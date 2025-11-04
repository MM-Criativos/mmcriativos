<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use App\Services\Upload\ImageUploadService;
use App\Support\StorageHelper;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('approved');
    }

    public function edit()
    {
        $about = AboutUs::first();
        if (!$about) {
            $about = AboutUs::create([
                'photo' => null,
                'title' => null,
                'subtitle' => null,
                'description' => null,
            ]);
        }
        return view('admin.layout.aboutus.edit', compact('about'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable','string','max:255'],
            'subtitle' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'photo' => ['nullable','image'],
            'cover' => ['nullable','image'],
        ]);

        $about = AboutUs::firstOrFail();

        if ($request->hasFile('photo')) {
            StorageHelper::deletePublic($about->photo);
            /** @var ImageUploadService $img */
            $img = app(ImageUploadService::class);
            $path = $img->store($request->file('photo'), 'layout', [
                'basename' => 'aboutus-photo',
            ]);
            $data['photo'] = 'storage/' . $path;
        } else {
            unset($data['photo']);
        }

        if ($request->hasFile('cover')) {
            StorageHelper::deletePublic($about->cover);
            /** @var ImageUploadService $img */
            $img = app(ImageUploadService::class);
            $path = $img->store($request->file('cover'), 'layout', [
                'basename' => 'aboutus-cover',
            ]);
            $data['cover'] = 'storage/' . $path;
        } else {
            unset($data['cover']);
        }

        $about->update($data);

        return back()->with('status', 'Sobre nós atualizado.');
    }
}
