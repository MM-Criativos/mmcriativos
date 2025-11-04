<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use App\Services\Upload\VideoUploadService;
use App\Support\StorageHelper;

class SliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('approved');
    }

    public function edit()
    {
        $slider = Slider::first();
        if (!$slider) {
            $slider = Slider::create([
                'video' => null,
                'text_1' => null,
                'text_2' => null,
                'text_3' => null,
            ]);
        }
        return view('admin.layout.slider.edit', compact('slider'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_1' => ['nullable','string','max:255'],
            'text_2' => ['nullable','string','max:255'],
            'text_3' => ['nullable','string','max:255'],
            'video' => ['nullable','file','mimes:mp4,webm,ogg,mov'],
        ]);

        $slider = Slider::firstOrFail();

        if ($request->hasFile('video')) {
            // remove anterior
            StorageHelper::deletePublic($slider->video);
            /** @var VideoUploadService $vid */
            $vid = app(VideoUploadService::class);
            $out = $vid->transcode($request->file('video'), 'layout', [
                'basename' => 'layout-slider-video',
            ]);
            $data['video'] = 'storage/' . $out['video'];
        }

        $slider->update($data);
        return back()->with('status', 'Slider atualizado.');
    }
}

