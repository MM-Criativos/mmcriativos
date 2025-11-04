<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Upload\ImageUploadService;
use App\Services\Upload\VideoUploadService;
use App\Support\StorageHelper;

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
            // Accept image or video for cover
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg,mov'],
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $slug = $data['slug'];
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $mime = (string) $file->getMimeType();
                if (str_starts_with($mime, 'image/')) {
                    /** @var ImageUploadService $uploader */
                    $uploader = app(ImageUploadService::class);
                    $basename = "skill-{$slug}-{$field}";
                    $path = $uploader->store($file, 'skills', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $path;
                } elseif (str_starts_with($mime, 'video/')) {
                    /** @var VideoUploadService $vid */
                    $vid = app(VideoUploadService::class);
                    $basename = "skill-{$slug}-{$field}";
                    $out = $vid->transcode($file, 'skills', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $out['video'];
                    if (empty($data['thumb']) && !empty($out['poster'])) {
                        $data['thumb'] = 'storage/' . $out['poster'];
                    }
                } else {
                    $path = $file->store('skills', 'public');
                    $data[$field] = 'storage/' . $path;
                }
            }
        }
        $skill = Skill::create($data);
        return redirect()->route('admin.skills.edit', $skill)->with('status', 'Skill criada com sucesso.');
    }

    public function edit(Skill $skill)
    {
        $skill->load(['info', 'competencies' => function ($q) {
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
            // Accept image or video for cover
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg,mov'],
        ]);
        $slug = $skill->slug;
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $mime = (string) $file->getMimeType();
                if (str_starts_with($mime, 'image/')) {
                    if ($field === 'thumb') StorageHelper::deletePublic($skill->thumb);
                    if ($field === 'cover') StorageHelper::deletePublic($skill->cover);
                    /** @var ImageUploadService $uploader */
                    $uploader = app(ImageUploadService::class);
                    $basename = "skill-{$slug}-{$field}";
                    $path = $uploader->store($file, 'skills', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $path;
                } elseif (str_starts_with($mime, 'video/')) {
                    if ($field === 'cover') StorageHelper::deletePublic($skill->cover);
                    /** @var VideoUploadService $vid */
                    $vid = app(VideoUploadService::class);
                    $basename = "skill-{$slug}-{$field}";
                    $out = $vid->transcode($file, 'skills', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $out['video'];
                    if (empty($data['thumb']) && !empty($out['poster'])) {
                        StorageHelper::deletePublic($skill->thumb);
                        $data['thumb'] = 'storage/' . $out['poster'];
                    }
                } else {
                    if ($field === 'thumb') StorageHelper::deletePublic($skill->thumb);
                    if ($field === 'cover') StorageHelper::deletePublic($skill->cover);
                    $path = $file->store('skills', 'public');
                    $data[$field] = 'storage/' . $path;
                }
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
