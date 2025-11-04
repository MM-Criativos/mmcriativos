<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $classes = Classe::orderBy('hierarquia')->orderBy('id')->get();
        return view('admin.team.classes.index', compact('classes'));
    }

    public function edit(Classe $classe)
    {
        return view('admin.team.classes.edit', compact('classe'));
    }

    public function update(Request $request, Classe $classe)
    {
        $data = $request->validate([
            'hierarquia' => ['required', 'integer', 'in:1,2,3'],
            'classe' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // recebemos skills como string (vírgulas/linhas) ou array
            'skills' => ['nullable'],
        ]);

        $skillsInput = $request->input('skills');
        $skills = [];
        if (is_string($skillsInput)) {
            $parts = preg_split('/[\n,]+/', $skillsInput) ?: [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') $skills[] = $p;
            }
        } elseif (is_array($skillsInput)) {
            foreach ($skillsInput as $p) {
                $p = trim((string) $p);
                if ($p !== '') $skills[] = $p;
            }
        }

        $classe->update([
            'hierarquia' => (int) $data['hierarquia'],
            'classe' => $data['classe'],
            'description' => $data['description'] ?? null,
            'skills' => $skills,
        ]);

        return redirect()->route('admin.classes.edit', $classe)->with('status', 'Classe atualizada.');
    }
}
