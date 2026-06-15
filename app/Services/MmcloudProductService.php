<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MmcloudProductService
{
    /** Catálogo de features do MMCC (single-source). Usado para renderizar a árvore. */
    public function featureCatalog(): array
    {
        try {
            $response = $this->client()->get('/api/external/feature-catalog');
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? ['core' => [], 'features' => []];
    }

    /** Catálogo de políticas (single-source). Usado para renderizar checkboxes da tab Políticas. */
    public function policyCatalog(): array
    {
        try {
            $response = $this->client()->get('/api/external/policy-catalog');
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? ['policies' => [], 'default_visible' => []];
    }

    public function list(): array
    {
        try {
            $response = $this->client()->get('/api/external/products');
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? ['data' => []];
    }

    public function find(int|string $id): array
    {
        try {
            $response = $this->client()->get("/api/external/products/{$id}");
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? [];
    }

    public function create(array $payload): array
    {
        try {
            $response = $this->client()->post('/api/external/products', $payload);
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'name');
        }

        return $response->json() ?? [];
    }

    public function update(int|string $id, array $payload): array
    {
        try {
            $response = $this->client()->put("/api/external/products/{$id}", $payload);
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'name');
        }

        return $response->json() ?? [];
    }

    public function delete(int|string $id): void
    {
        try {
            $response = $this->client()->delete("/api/external/products/{$id}");
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }
    }

    /**
     * Upload de logo/ícone/favicon (multipart). Os assets ficam no storage do MMCC;
     * a resposta traz as URLs finais (*_url).
     *
     * @param array<string, UploadedFile> $files  ex: ['logo_primary' => $file]
     */
    public function uploadAssets(int|string $id, array $files): array
    {
        $pending = $this->client()->asMultipart();

        foreach ($files as $field => $file) {
            $pending = $pending->attach($field, fopen($file->getRealPath(), 'r'), $file->getClientOriginalName());
        }

        try {
            $response = $pending->post("/api/external/products/{$id}/assets");
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? [];
    }

    public function tenants(int|string $productId): array
    {
        try {
            $response = $this->client()->get("/api/external/products/{$productId}/tenants");
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? ['data' => []];
    }

    public function assignableTenants(): array
    {
        try {
            $response = $this->client()->get('/api/external/products/assignable-tenants');
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }

        return $response->json() ?? ['data' => []];
    }

    public function assignTenant(int|string $productId, int $tenantId): void
    {
        try {
            $response = $this->client()->post("/api/external/products/{$productId}/tenants", [
                'tenant_id' => $tenantId,
            ]);
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'tenant_id');
        }
    }

    public function unassignTenant(int|string $productId, int|string $tenantId): void
    {
        try {
            $response = $this->client()->delete("/api/external/products/{$productId}/tenants/{$tenantId}");
            $response->throw();
        } catch (RequestException $e) {
            $this->throwValidationError($e, 'mmcloud');
        }
    }

    private function client(): PendingRequest
    {
        $url    = rtrim(config('services.mmcloud.url', ''), '/');
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
                    'max'             => 5,
                    'strict'          => true,
                    'referer'         => true,
                    'protocols'       => ['http', 'https'],
                    'track_redirects' => true,
                ],
            ])
            ->withHeaders([
                'X-MMCloud-External-Secret' => $secret,
                'Accept'                    => 'application/json',
            ]);
    }

    private function throwValidationError(RequestException $e, string $field): never
    {
        $body    = $e->response?->json() ?? [];
        $message = $body['message'] ?? $e->getMessage();

        throw ValidationException::withMessages([$field => $message]);
    }
}
