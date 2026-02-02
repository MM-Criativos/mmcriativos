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
        $url = rtrim(config('services.mmcloud.url', ''), '/');
        $secret = config('services.mmcloud.secret');
        $skipVerify = (bool) config('services.mmcloud.skip_verify', false);

        if (! $url || ! $secret) {
            throw ValidationException::withMessages([
                'domain' => 'Conexão com o MM Criativos Cloud não está configurada.',
            ]);
        }

        $payload = [
            'name' => $name,
            'slug' => $this->buildSlug($domain),
            'status' => 'active',
        ];

        $client = Http::withOptions([
            'verify' => ! $skipVerify,
        ])->withHeaders([
            'X-MMCloud-External-Secret' => $secret,
        ]);

        try {
            $response = $client->post("{$url}/api/external/tenants", $payload);

            $response->throw();
        } catch (RequestException $exception) {
            $message = $exception->response
                ? $exception->response->json('message') ?? $exception->response->body()
                : $exception->getMessage();

            throw ValidationException::withMessages([
                'domain' => $message,
            ]);
        }

        return $response->json();
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
