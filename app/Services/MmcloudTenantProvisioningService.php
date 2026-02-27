<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
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

    public function createTenant(string $name, string $domain): array
    {
        $payload = [
            'name' => $name,
            'slug' => $this->buildSlug($domain),
            'status' => 'trial',
        ];

        try {
            $response = $this->client()->post('/api/external/tenants', $payload);
            $response->throw();
        } catch (RequestException $exception) {
            $this->throwValidationError($exception, 'domain');
        }

        return $response->json() ?? [];
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
            ])
            ->withHeaders([
                'X-MMCloud-External-Secret' => $secret,
                'Accept' => 'application/json',
            ]);
    }

    private function throwValidationError(RequestException $exception, string $field): never
    {
        $message = $exception->response?->json('message');

        if (! $message) {
            $message = 'Erro ao comunicar com o MM Criativos Cloud.';
        }

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
