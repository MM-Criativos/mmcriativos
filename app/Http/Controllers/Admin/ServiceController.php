<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Upload\ImageUploadService;
use App\Services\Upload\VideoUploadService;
use App\Support\StorageHelper;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $services = Service::query()->orderBy('order')->orderBy('name')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $service = new Service();
        return view('admin.services.create', compact('service'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumb' => ['nullable', 'image'],
            'cover' => ['nullable', 'file', 'mimes:mp4,webm,ogg,mov'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $slug = $data['slug'];
        $nowBase = date('Ymd-His');
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                if ($field === 'thumb') {
                    /** @var ImageUploadService $img */
                    $img = app(ImageUploadService::class);
                    $basename = "service-{$slug}-thumb";
                    $path = $img->store($request->file($field), 'services', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $path;
                } else {
                    $file = $request->file($field);
                    $mime = (string) $file->getMimeType();
                    if (str_starts_with($mime, 'video/')) {
                        /** @var VideoUploadService $vid */
                        $vid = app(VideoUploadService::class);
                        $basename = "service-{$slug}-cover";
                        $out = $vid->transcode($file, 'services', ['basename' => $basename]);
                        $data[$field] = 'storage/' . $out['video'];
                        // Se não veio thumb e gerou poster, usa como thumb
                        if (empty($data['thumb']) && !empty($out['poster'])) {
                            $data['thumb'] = 'storage/' . $out['poster'];
                        }
                    } else {
                        /** @var ImageUploadService $img */
                        $img = app(ImageUploadService::class);
                        $basename = "service-{$slug}-cover";
                        $path = $img->store($file, 'services', ['basename' => $basename]);
                        $data[$field] = 'storage/' . $path;
                    }
                }
            }
        }

        $data['order'] = (int) Service::max('order') + 1;

        $service = Service::create($data);

        return redirect()->route('admin.services.edit', $service)->with('status', 'Servico criado com sucesso.');
    }

    public function edit(Service $service)
    {
        $service->load([
            'info',
            'benefits' => function ($q) {
                $q->orderBy('order');
            },
            'features' => function ($q) {
                $q->orderBy('order');
            },
            'processes' => function ($q) {
                $q->orderBy('order');
            },
            'ctas',
        ]);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:services,slug,' . $service->id],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumb' => ['nullable', 'image'],
            'cover' => ['nullable', 'file', 'mimes:mp4,webm,ogg,mov'],
        ]);

        $slug = $service->slug;
        foreach (['thumb', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                if ($field === 'thumb') {
                    // apaga anterior
                    StorageHelper::deletePublic($service->thumb);
                    /** @var ImageUploadService $img */
                    $img = app(ImageUploadService::class);
                    $basename = "service-{$slug}-thumb";
                    $path = $img->store($request->file($field), 'services', ['basename' => $basename]);
                    $data[$field] = 'storage/' . $path;
                } else {
                    $file = $request->file($field);
                    $mime = (string) $file->getMimeType();
                    if (str_starts_with($mime, 'video/')) {
                        StorageHelper::deletePublic($service->cover);
                        /** @var VideoUploadService $vid */
                        $vid = app(VideoUploadService::class);
                        $basename = "service-{$slug}-cover";
                        $out = $vid->transcode($file, 'services', ['basename' => $basename]);
                        $data[$field] = 'storage/' . $out['video'];
                        if (empty($data['thumb']) && !empty($out['poster'])) {
                            // se vai substituir a thumb com o poster, apaga a atual
                            StorageHelper::deletePublic($service->thumb);
                            $data['thumb'] = 'storage/' . $out['poster'];
                        }
                    } else {
                        StorageHelper::deletePublic($service->cover);
                        /** @var ImageUploadService $img */
                        $img = app(ImageUploadService::class);
                        $basename = "service-{$slug}-cover";
                        $path = $img->store($file, 'services', ['basename' => $basename]);
                        $data[$field] = 'storage/' . $path;
                    }
                }
            }
        }

        $service->update($data);

        return back()->with('status', 'Servico atualizado.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('status', 'Servico removido.');
    }
}
