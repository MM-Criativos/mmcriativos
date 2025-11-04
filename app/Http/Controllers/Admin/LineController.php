<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Line;
use Illuminate\Http\Request;

class LineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('approved');
    }

    public function edit()
    {
        $lines = Line::orderBy('id')->get(['id','text']);
        return view('admin.layout.lines.edit', compact('lines'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'lines' => ['required','array'],
            'lines.*' => ['nullable','string','max:255'],
        ]);

        $texts = collect($data['lines'])
            ->map(fn($t) => trim((string)$t))
            ->filter();

        // Estratégia simples: substituir o conjunto inteiro
        \DB::transaction(function () use ($texts) {
            Line::query()->delete();
            foreach ($texts as $t) {
                Line::create(['text' => $t]);
            }
        });

        return back()->with('status', 'Linhas atualizadas.');
    }
}

