<?php

namespace App\Http\Controllers;

use App\Services\MmcloudTenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuperAdminTenantController extends Controller
{
    public function __construct(
        private MmcloudTenantProvisioningService $tenantProvisioning
    ) {}

    public function index(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,suspended,trial'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $response = $this->tenantProvisioning->listTenants($filters);
        } catch (ValidationException $e) {
            return view('mmcloud.index', [
                'tenants' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => (int) ($filters['per_page'] ?? 20),
                    'total' => 0,
                ],
                'filters' => $filters,
            ])->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);

            return view('mmcloud.index', [
                'tenants' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => (int) ($filters['per_page'] ?? 20),
                    'total' => 0,
                ],
                'filters' => $filters,
            ])->withErrors([
                'mmcloud' => 'Erro inesperado ao carregar tenants do MM Criativos Cloud.',
            ]);
        }

        return view('mmcloud.index', [
            'tenants' => $response['data'] ?? [],
            'meta' => $response['meta'] ?? [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => (int) ($filters['per_page'] ?? 20),
                'total' => count($response['data'] ?? []),
            ],
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensureSuperAdmin($request);

        return view('mmcloud.create');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        try {
            $tenant = $this->tenantProvisioning->createTenant(
                $data['name'],
                $data['domain']
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'domain' => 'Erro inesperado ao criar o tenant no MM Criativos Cloud.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('mmcloud.tenants.create')
            ->with('status', 'Tenant solicitado com sucesso.')
            ->with('tenant_token', $tenant['api_token'] ?? null)
            ->with('tenant_slug', $tenant['slug'] ?? null);
    }

    public function edit(Request $request, string $tenant)
    {
        $this->ensureSuperAdmin($request);

        try {
            $tenantData = $this->tenantProvisioning->showTenant($tenant);
        } catch (ValidationException $e) {
            return redirect()
                ->route('mmcloud.tenants.index')
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('mmcloud.tenants.index')
                ->withErrors([
                    'mmcloud' => 'Erro inesperado ao carregar os dados do tenant.',
                ]);
        }

        return view('mmcloud.edit', [
            'tenant' => $tenantData,
        ]);
    }

    public function update(Request $request, string $tenant)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,trial'],
        ]);

        try {
            $updated = $this->tenantProvisioning->updateTenant($tenant, $data);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'mmcloud' => 'Erro inesperado ao atualizar tenant no MM Criativos Cloud.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('mmcloud.tenants.edit', ['tenant' => $updated['tenant_id'] ?? $tenant])
            ->with('status', 'Tenant atualizado com sucesso.');
    }

    public function regenerateApiToken(Request $request, string $tenant)
    {
        $this->ensureSuperAdmin($request);

        try {
            $updated = $this->tenantProvisioning->regenerateApiToken($tenant);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'mmcloud' => 'Erro inesperado ao regenerar API key do tenant.',
            ]);
        }

        return redirect()
            ->route('mmcloud.tenants.edit', ['tenant' => $updated['tenant_id'] ?? $tenant])
            ->with('status', 'API key regenerada com sucesso.')
            ->with('tenant_token', $updated['api_token'] ?? null);
    }

    private function ensureSuperAdmin(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }
    }
}
