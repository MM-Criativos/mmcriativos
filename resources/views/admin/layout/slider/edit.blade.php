@php
    $title = 'Slider';
    $subTitle = 'Ajustes o slider do header hero';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            <div class="bg-white dark:bg-black overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.layout.slider.update') }}" enctype="multipart/form-data"
                        class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col items-center justify-center mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Vídeo de fundo
                            </label>

                            <div class="relative group cursor-pointer w-40 h-40 mx-auto">
                                <input type="file" name="video" accept="video/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewVideo(event)">

                                @php
                                    $video = $slider->video;
                                    $isVideo =
                                        $video &&
                                        \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($video), [
                                            '.mp4',
                                            '.webm',
                                            '.ogg',
                                            '.mov',
                                        ]);
                                @endphp

                                @if ($slider->video)
                                    @if ($isVideo)
                                        <video id="preview-video" src="{{ asset($slider->video) }}"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition"
                                            controls muted></video>
                                    @else
                                        <div id="preview-video"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-file-video text-base mr-1"></i> Arquivo inválido
                                        </div>
                                    @endif
                                @else
                                    <div id="preview-video"
                                        class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                        <i class="fa-regular fa-file-video text-base mr-1"></i> Vídeo
                                    </div>
                                @endif
                            </div>

                            @if ($slider->video)
                                <p class="text-xs text-gray-500 mt-2 text-center">
                                    Atual:
                                    <a class="underline" href="{{ asset($slider->video) }}" target="_blank">
                                        {{ basename($slider->video) }}
                                    </a>
                                </p>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Texto 1
                                </label>
                                <input type="text" name="text_1" value="{{ old('text_1', $slider->text_1) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Texto 2
                                </label>
                                <input type="text" name="text_2" value="{{ old('text_2', $slider->text_2) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Texto 3
                                </label>
                                <input type="text" name="text_3" value="{{ old('text_3', $slider->text_3) }}"
                                    class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-dark-800 border-gray-300 rounded-md">
                            </div>
                        </div>


                        <div class="flex justify-center">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 btn-mmcriativos rounded-md">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
