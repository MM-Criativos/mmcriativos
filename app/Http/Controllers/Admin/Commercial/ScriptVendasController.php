<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\SdrScript;
use Illuminate\Http\Request;

class ScriptVendasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved', 'can.commercial']);
    }

    public function index()
    {
        $script = SdrScript::active();
        return view('admin.commercial.script-vendas.index', compact('script'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'flow'    => ['required', 'array'],
        ]);

        // Desativa todos os scripts anteriores
        SdrScript::where('is_active', true)->update(['is_active' => false]);

        SdrScript::create([
            'name'      => $data['name'],
            'version'   => $data['version'],
            'is_active' => true,
            'flow'      => $data['flow'],
        ]);

        return response()->json(['ok' => true]);
    }
}
