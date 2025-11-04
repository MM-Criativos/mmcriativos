<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceProcess;
use Illuminate\Http\Request;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;
use Illuminate\Support\Str;

class ServiceProcessController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
        ]);
        if ($request->hasFile('image')) {
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = 'service-' . ($service->slug ?? 'service') . '-process-' . \Illuminate\Support\Str::slug($request->input('title', 'process'), '-') . '-image';
            $path = $uploader->store($request->file('image'), 'services/processes', ['basename' => $basename]);
            $data['image'] = 'storage/' . $path;
        }
        $data['order'] = (int) $service->processes()->max('order') + 1;
        $service->processes()->create($data);
        return back()->with('status', 'Processo adicionado.');
    }

    public function update(Request $request, ServiceProcess $process)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
            'order' => ['nullable', 'integer'],
        ]);
        if ($request->hasFile('image')) {
            StorageHelper::deletePublic($process->image);
            /** @var ImageUploadService $uploader */
            $uploader = app(ImageUploadService::class);
            $basename = 'service-' . ($process->service->slug ?? 'service') . '-process-' . \Illuminate\Support\Str::slug($request->input('title', $process->title ?? 'process'), '-') . '-image';
            $path = $uploader->store($request->file('image'), 'services/processes', ['basename' => $basename]);
            $data['image'] = 'storage/' . $path;
        }
        $process->update($data);
        return back()->with('status', 'Processo atualizado.');
    }

    public function destroy(ServiceProcess $process)
    {
        $process->delete();
        return back()->with('status', 'Processo removido.');
    }

    /**
     * Atualiza todos os processos de um serviço em uma única requisição.
     * Mantém os não enviados; atualiza apenas os que vierem no payload.
     */
    public function bulk(Request $request, Service $service)
    {
        $validated = $request->validate([
            'processes' => ['required','array'],
            'processes.*.title' => ['nullable','string','max:255'],
            'processes.*.description' => ['nullable','string'],
            'processes.*.order' => ['nullable','integer'],
            'processes.*.image' => ['nullable','image'],
        ]);

        $items = $validated['processes'] ?? [];
        /** @var ImageUploadService $uploader */
        $uploader = app(ImageUploadService::class);

        foreach ($items as $id => $fields) {
            /** @var ServiceProcess|null $proc */
            $proc = $service->processes()->whereKey($id)->first();
            if (!$proc) { continue; }

            $update = [];
            if (array_key_exists('title', $fields)) { $update['title'] = $fields['title']; }
            if (array_key_exists('description', $fields)) { $update['description'] = $fields['description']; }
            if (array_key_exists('order', $fields)) { $update['order'] = (int) $fields['order']; }

            if ($request->hasFile("processes.$id.image")) {
                StorageHelper::deletePublic($proc->image);
                $file = $request->file("processes.$id.image");
                $basename = 'service-' . ($service->slug ?? 'service') . '-process-' . Str::slug($fields['title'] ?? $proc->title ?? 'process', '-') . '-image';
                $path = $uploader->store($file, 'services/processes', ['basename' => $basename]);
                $update['image'] = 'storage/' . $path;
            }

            if (!empty($update)) {
                $proc->update($update);
            }
        }

        return back()->with('status', 'Processos atualizados.');
    }
}
