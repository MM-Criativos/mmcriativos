<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MmcloudTenantProvisioningService
{
    public function listTenants(array $filters = []): array
    {
        $query = array_filter([
            'search' => $filters['search'] ?? null,
            'status' => $filters['status'] ?? null,
            'per_page' => $filters['per_page'] ?? 20,
            'page' => $filters['page'] ?? 1,
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $response = $this->client()->get('/api/external/tenants', $query);
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'mmcloud');
        }

        return $response->json() ?? ['data' => [], 'meta' => []];
    }

    public function createTenant(string $name, string $domain, ?int $productId = null, ?string $plan = null): array
    {
        $payload = [
            'name' => $name,
            'slug' => $this->buildSlug($domain),
            'status' => 'trial',
        ];

        if ($productId !== null) {
            $payload['product_id'] = $productId;
        }

        if ($plan !== null) {
            $payload['plan'] = $plan;
        }

        try {
            $response = $this->client()->post('/api/external/tenants', $payload);
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'domain');
        }

        $result = $response->json() ?? [];

        if (! is_array($result) || ! isset($result['slug']) || ! isset($result['api_token'])) {
            throw ValidationException::withMessages([
                'domain' => 'Resposta invalida ao criar tenant no MM Criativos Cloud. Verifique MMCC_API_URL e redirecionamentos.',
            ]);
        }

        return $result;
    }

    public function showTenant(string|int $tenant): array
    {
        try {
            $response = $this->client()->get("/api/external/tenants/{$tenant}");
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'mmcloud');
        }

        return $response->json() ?? [];
    }

    public function updateTenant(string|int $tenant, array $payload): array
    {
        try {
            $response = $this->client()->patch("/api/external/tenants/{$tenant}", $payload);
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'name');
        }

        return $response->json() ?? [];
    }

    public function regenerateApiToken(string|int $tenant): array
    {
        try {
            $response = $this->client()->post("/api/external/tenants/{$tenant}/regenerate-api-token");
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'mmcloud');
        }

        return $response->json() ?? [];
    }

    /**
     * `/plans` e rota escopada a um tenant, e o MMCC vai passar a exigir o token
     * do tenant nela (SDD-fechamento-da-api-externa, F3). O segredo de plataforma
     * que o `client()` manda nao serve ali — e as irmas createTenantInstance() e
     * listTenantInstances() ja usavam `tenantClient()` por isso.
     */
    public function listTenantPlans(string $tenantSlug, string $tenantToken): array
    {
        try {
            $response = $this->tenantClient($tenantToken)->get("/api/external/tenants/{$tenantSlug}/plans");
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'mmcloud');
        }

        return $response->json() ?? ['plans' => []];
    }

    public function createTenantInstance(string|int $tenant, array $payload): array
    {
        $tenantData = $this->showTenant($tenant);
        $tenantSlug = trim((string) ($tenantData['slug'] ?? ''));
        $tenantToken = trim((string) ($tenantData['api_token'] ?? ''));

        if ($tenantSlug === '' || $tenantToken === '') {
            throw ValidationException::withMessages([
                'mmcloud' => 'Tenant sem slug ou api_token para criar instancia.',
            ]);
        }

        try {
            $response = $this->tenantClient($tenantToken)->post(
                "/api/external/tenants/{$tenantSlug}/ai/instances",
                $payload
            );
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'instance_name');
        }

        return $response->json() ?? [];
    }

    public function listTenantInstances(string|int $tenant): array
    {
        $tenantData = $this->showTenant($tenant);
        $tenantSlug = trim((string) ($tenantData['slug'] ?? ''));
        $tenantToken = trim((string) ($tenantData['api_token'] ?? ''));

        if ($tenantSlug === '' || $tenantToken === '') {
            throw ValidationException::withMessages([
                'mmcloud' => 'Tenant sem slug ou api_token para consultar instancias.',
            ]);
        }

        try {
            $response = $this->tenantClient($tenantToken)->get(
                "/api/external/tenants/{$tenantSlug}/ai/instances"
            );
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'mmcloud');
        }

        return $response->json() ?? ['instances' => []];
    }

    private function client(): PendingRequest
    {
        $url = rtrim(config('services.mmcloud.url', ''), '/');
        $secret = config('services.mmcloud.secret');
        $skipVerify = (bool) config('services.mmcloud.skip_verify', false);

        if (! $url || ! $secret) {
            throw ValidationException::withMessages([
                'mmcloud' => 'Conexao com o MM Criativos Cloud nao esta configurada.',
            ]);
        }

        return Http::baseUrl($url)
            ->withOptions([
                'verify' => ! $skipVerify,
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['http', 'https'],
                    'track_redirects' => true,
                ],
            ])
            ->withHeaders([
                'X-MMCloud-External-Secret' => $secret,
                'Accept' => 'application/json',
            ]);
    }

    private function tenantClient(string $tenantToken): PendingRequest
    {
        return $this->client()->withHeaders([
            'X-MMCloud-Token' => $tenantToken,
        ]);
    }

    private function throwValidationError(RequestException $exception, string $field): never
    {
        $status = $exception->response?->status();
        $message = $exception->response?->json('message');

        if (! $message) {
            $rawBody = trim((string) $exception->response?->body());
            $message = $rawBody !== '' ? Str::limit($rawBody, 220) : 'Erro ao comunicar com o MM Criativos Cloud.';
        }

        if ($status) {
            $message = "[MMCC {$status}] {$message}";
        }

        Log::warning('MMCloud API request failed', [
            'status' => $status,
            'field' => $field,
            'url' => $exception->request?->url(),
            'method' => $exception->request?->method(),
            'redirect_history' => $exception->response?->header('X-Guzzle-Redirect-History'),
            'redirect_status_history' => $exception->response?->header('X-Guzzle-Redirect-Status-History'),
        ]);

        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }

    private function buildSlug(string $domain): string
    {
        $slug = Str::slug($domain);

        if (! $slug) {
            $slug = Str::random(6);
        }

        return $slug;
    }
}
