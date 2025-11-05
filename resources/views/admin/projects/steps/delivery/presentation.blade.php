<div class="space-y-6">
    <h3 class="text-lg font-semibold text-gray-800">Apresentação</h3>

    <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Campos obrigatórios do update para não quebrar validação -->
        <input type="hidden" name="name" value="{{ $project->name }}">
        <input type="hidden" name="slug" value="{{ $project->slug }}">

        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea name="summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('summary', $project->summary) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>
                @php
                    $cover = $project->cover;
                    $isVideo = $cover && \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), ['.mp4','.webm','.ogg','.mov']);
                @endphp
                <div class="w-40 h-40 mb-2">
                    @if ($cover)
                        @if ($isVideo)
                            <video src="{{ asset($cover) }}" class="w-40 h-40 object-cover rounded border border-gray-200" controls muted></video>
                        @else
                            <img src="{{ asset($cover) }}" class="w-40 h-40 object-cover rounded border border-gray-200" />
                        @endif
                    @else
                        <div class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">Sem cover</div>
                    @endif
                </div>
                <input type="file" name="cover" accept="image/*,video/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thumb</label>
                <div class="w-40 h-40 mb-2">
                    @if ($project->thumb)
                        <img src="{{ asset($project->thumb) }}" class="w-40 h-40 object-cover rounded border border-gray-200" />
                    @else
                        <div class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">Sem thumb</div>
                    @endif
                </div>
                <input type="file" name="thumb" accept="image/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Skill Cover</label>
                <div class="w-40 h-40 mb-2">
                    @if ($project->skill_cover)
                        <img src="{{ asset($project->skill_cover) }}" class="w-40 h-40 object-cover rounded border border-gray-200" />
                    @else
                        <div class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">Sem skill cover</div>
                    @endif
                </div>
                <input type="file" name="skill_cover" accept="image/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Vídeo (URL)</label>
                <input type="text" name="video" value="{{ old('video', $project->video) }}" placeholder="https://..."
                       class="mt-1 block w-full border-gray-300 rounded-md">
            </div>
        </div>

        <div>
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm transition-colors duration-200">Salvar apresentação</button>
        </div>
    </form>
</div>
