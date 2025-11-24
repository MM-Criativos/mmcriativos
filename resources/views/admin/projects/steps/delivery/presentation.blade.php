@php
    $clients = $clients ?? collect();
    $services = $services ?? collect();
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Entrega e publicação</h3>
            <p class="text-sm text-gray-500">Atualize os dados finais e conclua o projeto para aparecer no site.</p>
        </div>
    </div>

    <form id="project-presentation-form" method="POST" action="{{ route('admin.projects.update', $project) }}"
        enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm p-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do projeto</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}"
                    class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $project->slug) }}"
                    class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                @error('slug')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <select name="client_id"
                    class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">Selecione...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id', $project->client_id) == $client->id)>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Serviço</label>
                <select name="service_id"
                    class="w-full bg-white dark:bg-black border-gray-300 rounded-md text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">Selecione...</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $project->service_id) == $service->id)>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm p-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>
                @php
                    $cover = $project->cover;
                    $isVideo =
                        $cover &&
                        \Illuminate\Support\Str::endsWith(\Illuminate\Support\Str::lower($cover), [
                            '.mp4',
                            '.webm',
                            '.ogg',
                            '.mov',
                        ]);
                @endphp
                <div class="w-40 h-40 mb-2">
                    @if ($cover)
                        @if ($isVideo)
                            <video src="{{ asset($cover) }}"
                                class="w-40 h-40 object-cover rounded border border-gray-200" controls muted></video>
                        @else
                            <img src="{{ asset($cover) }}"
                                class="w-40 h-40 object-cover rounded border border-gray-200" />
                        @endif
                    @else
                        <div
                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">
                            Sem cover</div>
                    @endif
                </div>
                <input type="file" name="cover" accept="image/*,video/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thumb</label>
                <div class="w-40 h-40 mb-2">
                    @if ($project->thumb)
                        <img src="{{ asset($project->thumb) }}"
                            class="w-40 h-40 object-cover rounded border border-gray-200" />
                    @else
                        <div
                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">
                            Sem thumb</div>
                    @endif
                </div>
                <input type="file" name="thumb" accept="image/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Skill cover</label>
                <div class="w-40 h-40 mb-2">
                    @if ($project->skill_cover)
                        <img src="{{ asset($project->skill_cover) }}"
                            class="w-40 h-40 object-cover rounded border border-gray-200" />
                    @else
                        <div
                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs">
                            Sem skill cover</div>
                    @endif
                </div>
                <input type="file" name="skill_cover" accept="image/*" class="block w-full text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Vídeo (URL)</label>
                <input type="text" name="video" value="{{ old('video', $project->video) }}"
                    placeholder="https://..."
                    class="mt-1 block w-full bg-white dark:!bg-black border-gray-300 rounded-md">
                @error('video')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </form>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" form="project-presentation-form"
            class="btn-mmcriativos inline-flex items-center px-6 py-3 rounded-md">
            Salvar apresentação
        </button>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.projects.finish', $project) }}"
                onsubmit="return confirm('Marcar este projeto como finalizado?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center px-6 py-3.5 rounded border text-sm font-medium transition-colors duration-200
                    {{ $project->finished_at ? 'bg-green-800 text-white border-green-800 cursor-not-allowed' : 'bg-green-50 text-green-800 border-green-800 hover:bg-green-800 hover:text-white' }}"
                    {{ $project->finished_at ? 'disabled' : '' }}>
                    {{ $project->finished_at ? 'Projeto finalizado' : 'Finalizar projeto' }}
                </button>
            </form>

            @if ($project->finished_at)
                <form method="POST" action="{{ route('admin.projects.resume', $project) }}"
                    onsubmit="return confirm('Deseja reabrir este projeto e voltar para produção?');">
                    @csrf
                    <button type="submit" class="btn-mmcriativos inline-flex items-center px-6 py-3.5 rounded">
                        Voltar para desenvolvimento
                    </button>
                </form>
            @endif
        </div>

        @if ($project->finished_at)
            <span class="text-xs text-gray-500">
                Finalizado em {{ $project->finished_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
            </span>
        @endif
    </div>
</div>
