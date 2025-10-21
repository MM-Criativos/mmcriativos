<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $skills = Skill::query()->orderBy('id')->get();
        return view('admin.skills.index', compact('skills'));
    }

    public function create()
    {
        $skill = new Skill();
        return view('admin.skills.create', compact('skill'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:skills,slug'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumb' => ['nullable', 'image'],
            'cover' => ['nullable', 'image'],
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('skills', 'public');
                $data[$field] = 'storage/' . $path;
            }
        }
        $skill = Skill::create($data);
        return redirect()->route('admin.skills.edit', $skill)->with('status', 'Skill criada com sucesso.');
    }

    public function edit(Skill $skill)
    {
        $skill->load(['competencies' => function ($q) {
            $q->orderBy('id');
        }]);
        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:skills,slug,' . $skill->id],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumb' => ['nullable', 'image'],
            'cover' => ['nullable', 'image'],
        ]);
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('skills', 'public');
                $data[$field] = 'storage/' . $path;
            }
        }
        $skill->update($data);
        return back()->with('status', 'Skill atualizada.');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return redirect()->route('admin.skills.index')->with('status', 'Skill removida.');
    }
}
