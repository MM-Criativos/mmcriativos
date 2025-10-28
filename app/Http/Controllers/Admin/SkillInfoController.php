<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillInfoController extends Controller
{
    public function update(Request $request, Skill $skill)
    {
        $data = $request->validate([
            'image' => ['nullable', 'image'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('skills', 'public');
            $data['image'] = 'storage/' . $path;
        }

        $skill->info()->updateOrCreate([], $data);

        return back()->with('status', 'Informações da Skill salvas.');
    }
}

