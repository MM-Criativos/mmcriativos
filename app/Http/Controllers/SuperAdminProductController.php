<?php

namespace App\Http\Controllers;

use App\Services\MmcloudProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SuperAdminProductController extends Controller
{
    public function __construct(
        private MmcloudProductService $products
    ) {}

    public function index(Request $request)
    {
        $this->ensureSuperAdmin($request);

        try {
            $response = $this->products->list();
        } catch (ValidationException $e) {
            return view('mmcloud.products.index', ['products' => []])->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);
            return view('mmcloud.products.index', ['products' => []])->withErrors([
                'mmcloud' => 'Erro ao carregar produtos do MMCC.',
            ]);
        }

        return view('mmcloud.products.index', [
            'products' => $response['data'] ?? [],
        ]);
    }

    public function create(Request $request)
    {
        $this->ensureSuperAdmin($request);

        try {
            $catalog = $this->products->featureCatalog();
        } catch (\Throwable $e) {
            report($e);
            $catalog = ['core' => [], 'features' => []];
        }

        return view('mmcloud.products.create', compact('catalog'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/'],
            'display_name'      => ['nullable', 'string', 'max:255'],
            'tagline'           => ['nullable', 'string', 'max:500'],
            'status'            => ['required', 'in:active,inactive'],
            'primary_color'     => ['nullable', 'string', 'max:20'],
            'secondary_color'   => ['nullable', 'string', 'max:20'],
            'accent_color'      => ['nullable', 'string', 'max:20'],
            'modality_mode'     => ['nullable', 'in:full,health'],
            'features'          => ['nullable', 'array'],
            'features.*'        => ['string'],
            'logo_primary'      => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_icon'         => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'           => ['nullable', 'file', 'mimes:png,jpg,jpeg,ico', 'max:1024'],
            'vocab_keys'        => ['nullable', 'array'],
            'vocab_keys.*'      => ['nullable', 'string', 'max:100'],
            'vocab_values'      => ['nullable', 'array'],
            'vocab_values.*'    => ['nullable', 'string', 'max:500'],
            'customer_fields'   => ['nullable', 'array'],
            'customer_fields.*' => ['nullable', 'boolean'],
        ]);

        // Montar vocabulary a partir dos arrays paralelos de chaves/valores
        $vocabulary = [];
        foreach ($data['vocab_keys'] ?? [] as $i => $key) {
            $key = trim((string) $key);
            if ($key !== '') {
                $vocabulary[$key] = $data['vocab_values'][$i] ?? '';
            }
        }
        $data['vocabulary'] = $vocabulary ?: null;
        unset($data['vocab_keys'], $data['vocab_values']);

        try {
            $payload = collect($data)->except(['logo_primary', 'logo_icon', 'favicon'])->toArray();
            $product = $this->products->create($payload);

            $files = array_filter([
                'logo_primary' => $request->file('logo_primary'),
                'logo_icon'    => $request->file('logo_icon'),
                'favicon'      => $request->file('favicon'),
            ]);

            if ($files) {
                $this->products->uploadAssets($product['id'], $files);
            }
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['mmcloud' => 'Erro ao criar produto.']);
        }

        return redirect()
            ->route('mmcloud.products.edit', $product['id'])
            ->with('status', "Produto \"{$product['name']}\" criado com sucesso.");
    }

    public function edit(Request $request, int|string $productId)
    {
        $this->ensureSuperAdmin($request);

        try {
            $product         = $this->products->find($productId);
            $catalog         = $this->products->featureCatalog();
            $policyCatalog   = $this->products->policyCatalog();
            $assignedTenants = $this->products->tenants($productId);
            $freeTenants     = $this->products->assignableTenants();
        } catch (ValidationException $e) {
            return redirect()->route('mmcloud.products.index')->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('mmcloud.products.index')->withErrors([
                'mmcloud' => 'Erro ao carregar produto.',
            ]);
        }

        return view('mmcloud.products.edit', [
            'product'         => $product,
            'catalog'         => $catalog,
            'policyCatalog'   => $policyCatalog,
            'assignedTenants' => $assignedTenants['data'] ?? [],
            'freeTenants'     => $freeTenants['data'] ?? [],
        ]);
    }

    public function update(Request $request, int|string $productId)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'slug'               => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/'],
            'display_name'       => ['nullable', 'string', 'max:255'],
            'tagline'            => ['nullable', 'string', 'max:500'],
            'status'             => ['required', 'in:active,inactive'],
            'primary_color'      => ['nullable', 'string', 'max:20'],
            'secondary_color'    => ['nullable', 'string', 'max:20'],
            'accent_color'       => ['nullable', 'string', 'max:20'],
            'modality_mode'      => ['nullable', 'in:full,health'],
            'features'           => ['nullable', 'array'],
            'features.*'         => ['string'],
            'logo_primary'       => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_icon'          => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'            => ['nullable', 'file', 'mimes:png,jpg,jpeg,ico', 'max:1024'],
            'vocab_keys'         => ['nullable', 'array'],
            'vocab_keys.*'       => ['nullable', 'string', 'max:100'],
            'vocab_values'       => ['nullable', 'array'],
            'vocab_values.*'     => ['nullable', 'string', 'max:500'],
            'customer_fields'    => ['nullable', 'array'],
            'customer_fields.*'  => ['nullable', 'boolean'],
            'visible_policies'   => ['nullable', 'array'],
            'visible_policies.*' => ['string'],
        ]);

        $vocabulary = [];
        foreach ($data['vocab_keys'] ?? [] as $i => $key) {
            $key = trim((string) $key);
            if ($key !== '') {
                $vocabulary[$key] = $data['vocab_values'][$i] ?? '';
            }
        }
        $data['vocabulary'] = $vocabulary ?: null;
        unset($data['vocab_keys'], $data['vocab_values']);

        // visible_policies_submitted='1' → tab foi enviada normalmente (salva o que está marcado).
        // visible_policies_submitted='0' → reset explícito para null (usa default global).
        // Ausente → tab não foi submetida, não altera o valor atual no MMCC.
        $policiesSubmitted = $request->input('visible_policies_submitted');
        if ($policiesSubmitted === '1') {
            $data['visible_policies'] = $data['visible_policies'] ?? null;
        } elseif ($policiesSubmitted === '0') {
            $data['visible_policies'] = null;
        } else {
            unset($data['visible_policies']);
        }

        try {
            $payload = collect($data)->except(['logo_primary', 'logo_icon', 'favicon'])->toArray();
            $this->products->update($productId, $payload);

            $files = array_filter([
                'logo_primary' => $request->file('logo_primary'),
                'logo_icon'    => $request->file('logo_icon'),
                'favicon'      => $request->file('favicon'),
            ]);

            if ($files) {
                $this->products->uploadAssets($productId, $files);
            }
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['mmcloud' => 'Erro ao atualizar produto.']);
        }

        return redirect()
            ->route('mmcloud.products.edit', $productId)
            ->with('status', 'Produto atualizado com sucesso.');
    }

    public function destroy(Request $request, int|string $productId)
    {
        $this->ensureSuperAdmin($request);

        try {
            $this->products->delete($productId);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['mmcloud' => 'Erro ao remover produto.']);
        }

        return redirect()
            ->route('mmcloud.products.index')
            ->with('status', 'Produto removido. Tenants associados voltaram para o fallback.');
    }

    public function assignTenant(Request $request, int|string $productId)
    {
        $this->ensureSuperAdmin($request);

        $data = $request->validate([
            'tenant_id' => ['required', 'integer'],
        ]);

        try {
            $this->products->assignTenant($productId, (int) $data['tenant_id']);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['mmcloud' => 'Erro ao associar tenant.']);
        }

        return back()->with('status', 'Tenant associado ao produto.');
    }

    public function unassignTenant(Request $request, int|string $productId, int|string $tenantId)
    {
        $this->ensureSuperAdmin($request);

        try {
            $this->products->unassignTenant($productId, $tenantId);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['mmcloud' => 'Erro ao desassociar tenant.']);
        }

        return back()->with('status', 'Tenant desassociado. Voltou para o fallback.');
    }

    private function ensureSuperAdmin(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }
    }
}
