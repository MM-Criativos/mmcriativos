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

    public function create(Request $request)
    {
        $this->ensureSuperAdmin($request);

        return view('mmcloud.create');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
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

    private function ensureSuperAdmin(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }
    }
}
