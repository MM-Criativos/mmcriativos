<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectImage;
use App\Models\ProjectProcess;
use Illuminate\Http\Request;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;

class ProjectImageController extends Controller
{
    public function store(Request $request, ProjectProcess $projectProcess)
    {
        $request->validate([
            'images' => ['required','array'],
            'images.*' => ['image'],
        ]);

        $created = [];
        /** @var ImageUploadService $uploader */
        $uploader = app(ImageUploadService::class);
        $projectProcess->loadMissing(['project', 'process']);
        $projSlug = optional($projectProcess->project)->slug ?: 'project';
        $procSlug = \Illuminate\Support\Str::slug(optional($projectProcess->process)->name ?: 'process', '-');
        foreach ($request->file('images', []) as $i => $file) {
            $basename = "project-{$projSlug}-process-{$procSlug}-img";
            $path = $uploader->store($file, 'projects/processes', ['basename' => $basename]);
            $img = $projectProcess->images()->create([
                'image' => 'storage/' . $path,
                'order' => 0,
            ]);
            $created[] = [
                'id' => $img->id,
                'image' => $img->image,
                'title' => $img->title,
                'description' => $img->description,
                'solution' => $img->solution,
                'order' => $img->order,
                'project_process_id' => $img->project_process_id,
            ];
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'images' => $created]);
        }
        return back()->with('status', 'Imagens adicionadas.');
    }

    public function update(Request $request, ProjectImage $projectImage)
    {
        $data = $request->validate([
            'title' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'solution' => ['nullable','string'],
            'order' => ['nullable','integer'],
        ]);
        $projectImage->update($data);
        if ($request->ajax()) {
            return response()->json(['status' => 'ok', 'image' => [
                'id' => $projectImage->id,
                'title' => $projectImage->title,
                'description' => $projectImage->description,
                'solution' => $projectImage->solution,
                'order' => $projectImage->order,
            ]]);
        }
        return back()->with('status', 'Imagem atualizada.');
    }

    public function destroy(ProjectImage $projectImage)
    {
        $id = $projectImage->id;
        // remove arquivo do storage
        StorageHelper::deletePublic($projectImage->image);
        $projectImage->delete();
        if (request()->ajax()) {
            return response()->json(['status' => 'ok', 'removed' => true, 'id' => $id]);
        }
        return back()->with('status', 'Imagem removida.');
    }
}
