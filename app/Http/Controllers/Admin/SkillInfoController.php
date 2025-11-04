<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;

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
            // apaga anterior, se existir
            if ($skill->info) {
                StorageHelper::deletePublic($skill->info->image);
            }
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $path = $uploader->store($request->file('image'), 'skills');
            $data['image'] = 'storage/' . $path;
        }

        $skill->info()->updateOrCreate([], $data);

        return back()->with('status', 'Informações da Skill salvas.');
    }
}
