<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MmcloudTenantProvisioningService
{
    public function createTenant(string $name, string $domain): array
    {
        $url        = rtrim(config('services.mmcloud.url', ''), '/');
        $secret     = config('services.mmcloud.secret');
        $skipVerify = (bool) config('services.mmcloud.skip_verify', false);

        if (! $url || ! $secret) {
            throw ValidationException::withMessages([
                'domain' => 'Conexão com o MM Criativos Cloud não está configurada.',
            ]);
        }

        $payload = [
            'name'   => $name,
            'slug'   => $this->buildSlug($domain),
            'status' => 'trial', // 👈 recomendado iniciar como trial
        ];

        $client = Http::withOptions([
            'verify' => ! $skipVerify,
        ])->withHeaders([
            'X-MMCloud-External-Secret' => $secret,
            'Accept'                   => 'application/json', // 🔥 ESSENCIAL
        ]);

        try {
            $response = $client->post("{$url}/api/external/tenants", $payload);

            // força exception para qualquer 4xx / 5xx
            $response->throw();
        } catch (RequestException $exception) {

            // tenta pegar mensagem JSON primeiro
            $message = null;

            if ($exception->response) {
                $message = $exception->response->json('message');

                // fallback seguro (sem HTML gigante)
                if (! $message) {
                    $message = 'Erro ao comunicar com o MM Criativos Cloud.';
                }
            }

            throw ValidationException::withMessages([
                'domain' => $message ?? $exception->getMessage(),
            ]);
        }

        // garante retorno como array
        return $response->json() ?? [];
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
