<?php

namespace App\Http\Controllers\Vee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VeeTestRunnerDispatchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group_id' => ['nullable', 'string', 'max:10'],
            'target' => ['required', 'string', 'in:atendimento,agendamento,full'],
            'tenant_slug' => ['required', 'string', 'max:100'],
            'user_message' => ['required', 'string', 'max:2000'],
            'remoteJid' => ['nullable', 'string', 'max:64'],
            'pushName' => ['nullable', 'string', 'max:120'],
            'idMessage' => ['nullable', 'string', 'max:120'],
            'test_case' => ['nullable', 'string', 'max:120'],
            'current_flow' => ['nullable', 'string', 'max:120'],
            'current_step' => ['nullable', 'string', 'max:120'],
            'context_payload' => ['nullable', 'array'],
            'messageType' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant = $this->resolveTenantBySlug((string) $data['tenant_slug']);
        if (! $tenant) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Tenant nao encontrado para o slug informado.',
                'tenant_slug' => $data['tenant_slug'],
            ], 422);
        }

        $tenantId = (string) ($tenant['id'] ?? '');
        $tenantSlug = (string) ($tenant['slug'] ?? $data['tenant_slug']);
        $tenantApiToken = (string) ($tenant['api_token'] ?? '');

        if ($tenantId === '' || $tenantApiToken === '') {
            return response()->json([
                'status' => 'fail',
                'message' => 'Tenant sem id ou api_token valido.',
                'tenant_slug' => $tenantSlug,
            ], 422);
        }

        $remoteJid = (string) ($data['remoteJid'] ?? '5511958469546@s.whatsapp.net');
        $target = (string) $data['target'];

        $clientId = null;
        $contextFromDb = null;

        if ($target === 'agendamento') {
            $clientId = $this->resolveClientId($tenantSlug, $tenantId, $tenantApiToken, $remoteJid);
            if ($clientId === null) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Nenhum cliente encontrado dinamicamente para o tenant informado.',
                    'tenant_slug' => $tenantSlug,
                    'remoteJid' => $remoteJid,
                ], 422);
            }

            $contextFromDb = $this->resolveConversationContext($tenantSlug, $tenantId, $tenantApiToken, $clientId);
        }

        $payload = [
            'target' => $target,
            'test_case' => (string) ($data['test_case'] ?? ($target === 'agendamento' ? 'existing_user_agendamento' : 'existing_user_atendimento')),
            'tenant_slug' => $tenantSlug,
            'tenant_id' => $tenantId,
            'tenant_api_token' => $tenantApiToken,
            'user_message' => (string) $data['user_message'],
            'remoteJid' => $remoteJid,
            'pushName' => (string) ($data['pushName'] ?? 'Tester'),
            'idMessage' => (string) ($data['idMessage'] ?? ('TEST-' . strtoupper($target) . '-' . now()->timestamp)),
            'messageType' => (string) ($data['messageType'] ?? 'text'),
            'context_payload' => (array) ($data['context_payload'] ?? []),
        ];

        if ($target === 'agendamento') {
            $payload['client_id'] = (string) $clientId;
            $payload['current_intent'] = (string) ($contextFromDb['current_intent'] ?? 'booking');
            $payload['current_flow'] = (string) ($data['current_flow'] ?? ($contextFromDb['current_flow'] ?? 'schedule'));
            $payload['current_step'] = (string) ($data['current_step'] ?? '');
            $payload['action'] = 'req_scheduling';
        }

        $webhookUrl = $this->resolveTestRunnerWebhookUrl();

        $response = Http::timeout(20)
            ->retry(2, 300)
            ->acceptJson()
            ->asJson()
            ->post($webhookUrl, $payload);

        if (! $response->ok()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Falha ao disparar webhook do Test Runner.',
                'webhook_status' => $response->status(),
                'webhook_body' => $response->body(),
                'webhook_url' => $webhookUrl,
                'payload' => $payload,
            ], 502);
        }

        $json = $response->json();
        if (! is_array($json) || ($json['status'] ?? null) !== 'success') {
            Log::warning('Vee Test Runner dispatch invalid webhook response', [
                'webhook_url' => $webhookUrl,
                'http_status' => $response->status(),
                'body_preview' => Str::limit(trim((string) $response->body()), 500),
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Webhook respondeu sem formato esperado do Test Runner.',
                'webhook_url' => $webhookUrl,
                'webhook_status' => $response->status(),
                'webhook_body' => Str::limit(trim((string) $response->body()), 5000),
                'payload' => $payload,
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'dispatch' => $json,
            'payload' => $payload,
            'resolved' => [
                'tenant_id' => $tenantId,
                'tenant_slug' => $tenantSlug,
                'client_id' => $clientId,
            ],
        ]);
    }

    private function resolveTenantBySlug(string $tenantSlug): ?array
    {
        $response = $this->mmcloudExternalRequest()->get('/api/external/tenants/' . urlencode($tenantSlug));
        if (! $response->ok()) {
            return null;
        }

        $tenant = $response->json();
        return is_array($tenant) ? $tenant : null;
    }

    private function resolveClientId(string $tenantSlug, string $tenantId, string $tenantApiToken, string $remoteJid): ?int
    {
        $response = $this->mmcloudTenantRequest($tenantId, $tenantApiToken)
            ->get('/api/external/tenants/' . urlencode($tenantSlug) . '/customers', ['limit' => 100]);

        if (! $response->ok()) {
            return null;
        }

        $customers = $response->json('customers');
        if (! is_array($customers) || empty($customers)) {
            return null;
        }

        $remoteDigits = $this->onlyDigits(Str::before($remoteJid, '@'));
        $remoteLocal = strlen($remoteDigits) > 11 ? substr($remoteDigits, -11) : $remoteDigits;

        $best = null;

        foreach ($customers as $customer) {
            if (! is_array($customer) || ! isset($customer['id'])) {
                continue;
            }

            $phoneDigits = $this->onlyDigits((string) ($customer['phone'] ?? ''));
            $phoneLocal = strlen($phoneDigits) > 11 ? substr($phoneDigits, -11) : $phoneDigits;

            $score = 0;
            if ($phoneDigits !== '' && $phoneDigits === $remoteDigits) {
                $score = 100;
            } elseif ($phoneLocal !== '' && $phoneLocal === $remoteLocal) {
                $score = 90;
            } elseif ($phoneDigits !== '' && ($this->endsWith($phoneDigits, $remoteDigits) || $this->endsWith($remoteDigits, $phoneDigits))) {
                $score = 75;
            }

            if ($best === null || $score > $best['score'] || ($score === $best['score'] && $this->isNewer($customer, $best['customer']))) {
                $best = [
                    'score' => $score,
                    'customer' => $customer,
                ];
            }
        }

        if ($best && $best['score'] > 0) {
            return (int) $best['customer']['id'];
        }

        usort($customers, function (array $a, array $b) {
            $ua = (string) ($a['updated_at'] ?? '');
            $ub = (string) ($b['updated_at'] ?? '');
            if ($ua === $ub) {
                return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
            }
            return strcmp($ub, $ua);
        });

        return isset($customers[0]['id']) ? (int) $customers[0]['id'] : null;
    }

    private function resolveConversationContext(string $tenantSlug, string $tenantId, string $tenantApiToken, int $clientId): ?array
    {
        $response = $this->mmcloudTenantRequest($tenantId, $tenantApiToken)
            ->get('/api/external/tenants/' . urlencode($tenantSlug) . '/ai/conversation-context', [
                'client_id' => $clientId,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $ctx = $response->json('conversation_context');
        return is_array($ctx) ? $ctx : null;
    }

    private function mmcloudExternalRequest()
    {
        $base = $this->mmcloudBaseUrl();
        $secret = trim((string) config('services.mmcloud.secret', ''));

        return Http::baseUrl($base)
            ->timeout(15)
            ->retry(2, 300)
            ->acceptJson()
            ->withHeaders([
                'X-MMCloud-External-Secret' => $secret,
            ]);
    }

    private function mmcloudTenantRequest(string $tenantId, string $tenantApiToken)
    {
        $base = $this->mmcloudBaseUrl();

        return Http::baseUrl($base)
            ->timeout(15)
            ->retry(2, 300)
            ->acceptJson()
            ->withHeaders([
                'X-MMCloud-Header' => $tenantApiToken,
                'X-Tenant-Id' => $tenantId,
            ]);
    }

    private function mmcloudBaseUrl(): string
    {
        $configured = trim((string) config('services.mmcloud.url', ''));

        if ($configured === '') {
            return 'https://mmcc.mmcriativos.cloud';
        }

        if (Str::contains($configured, ['.test', 'localhost', '127.0.0.1'])) {
            return 'https://mmcc.mmcriativos.cloud';
        }

        if (Str::contains($configured, 'mmcriativos.cloud') && ! Str::contains($configured, 'mmcc.')) {
            return 'https://mmcc.mmcriativos.cloud';
        }

        return rtrim($configured, '/');
    }

    private function resolveTestRunnerWebhookUrl(): string
    {
        $default = 'https://n8n.mmcriativos.cloud/webhook/test-runner';
        $configured = trim((string) config('services.n8n.webhook_test_runner', ''));

        if ($configured === '') {
            return $default;
        }

        // Legacy URL format used before the webhook path stabilization.
        if (Str::contains($configured, '/webhook/WRPPK93a5dttEwzA/webhook/test-runner')) {
            return $default;
        }

        if (! Str::contains($configured, '/webhook/test-runner')) {
            return $default;
        }

        return rtrim($configured, '/');
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        return $needle !== '' && str_ends_with($haystack, $needle);
    }

    private function isNewer(array $a, array $b): bool
    {
        $ua = (string) ($a['updated_at'] ?? '');
        $ub = (string) ($b['updated_at'] ?? '');

        if ($ua === $ub) {
            return ((int) ($a['id'] ?? 0)) > ((int) ($b['id'] ?? 0));
        }

        return strcmp($ua, $ub) > 0;
    }
}
