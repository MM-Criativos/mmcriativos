<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\CloudLeadEtapa;
use App\Enums\CloudLeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProspeccaoRequest;
use App\Models\CloudLead;
use App\Models\User;
use Illuminate\Http\Request;

class ProspeccaoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved', 'can.prospeccao']);
    }

    // ─── Index ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = CloudLead::with('responsavel')
            ->filter($request)
            ->latest('updated_at');

        // Usuário 'comercial' vê apenas leads onde é responsável
        if ($user->role !== 'admin') {
            $query->where('responsavel_id', $user->id);
        }

        $leads       = $query->paginate(10)->withQueryString();
        $users       = User::where('is_approved', true)->orderBy('name')->get();
        $statusOpts  = CloudLeadStatus::options();
        $etapaOpts   = CloudLeadEtapa::options();

        return view('admin.commercial.prospeccao.index', compact(
            'leads', 'users', 'statusOpts', 'etapaOpts'
        ));
    }

    // ─── Store ───────────────────────────────────────────────────

    public function store(ProspeccaoRequest $request)
    {
        $data = $request->validated();
        $data['created_by_id'] = auth()->id();

        CloudLead::create($data);

        return redirect()
            ->route('admin.commercial.prospeccao.index')
            ->with('status', 'Lead adicionado com sucesso.');
    }

    // ─── Update ──────────────────────────────────────────────────

    public function update(ProspeccaoRequest $request, CloudLead $cloudLead)
    {
        $this->authorizeEdit($cloudLead);

        $cloudLead->update($request->validated());

        return redirect()
            ->route('admin.commercial.prospeccao.index')
            ->with('status', 'Lead atualizado.');
    }

    // ─── Inline patch (status ou etapa) ──────────────────────────

    public function updateField(Request $request, CloudLead $cloudLead)
    {
        $this->authorizeEdit($cloudLead);

        $data = $request->validate([
            'field' => ['required', 'in:status,etapa,responsavel_id'],
            'value' => ['required', 'string'],
        ]);

        $field = $data['field'];
        $value = $data['value'];

        // Validações específicas por campo
        if ($field === 'status' && ! CloudLeadStatus::tryFrom($value)) {
            return response()->json(['error' => 'Status inválido.'], 422);
        }
        if ($field === 'etapa' && ! CloudLeadEtapa::tryFrom($value)) {
            return response()->json(['error' => 'Etapa inválida.'], 422);
        }

        $cloudLead->update([$field => $value]);
        $cloudLead->refresh();

        return response()->json([
            'ok'             => true,
            'status_label'   => $cloudLead->status->label(),
            'status_classes' => $cloudLead->status->badgeClasses(),
            'etapa_label'    => $cloudLead->etapa->label(),
            'etapa_classes'  => $cloudLead->etapa->badgeClasses(),
        ]);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function destroy(CloudLead $cloudLead)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $cloudLead->delete();

        return redirect()
            ->route('admin.commercial.prospeccao.index')
            ->with('status', 'Lead removido.');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function authorizeEdit(CloudLead $lead): void
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && $lead->responsavel_id !== $user->id) {
            abort(403);
        }
    }
}
