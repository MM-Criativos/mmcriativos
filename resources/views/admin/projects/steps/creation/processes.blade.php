<div class="mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Processos do Projeto</h3>
    </div>

    <form method="POST" action="{{ route('admin.projects.processes.store', $project) }}"
        class="bg-white p-4 rounded shadow-sm border border-gray-100 mb-4 grid grid-cols-12 gap-3 items-end js-process-store"
        data-update-base="/admin/project-processes/__ID__" data-destroy-base="/admin/project-processes/__ID__"
        data-images-store-base="/admin/project-processes/__ID__/images"
        data-image-update-base="/admin/project-images/__ID__">
        @csrf
        <div class="col-span-11">
            <label class="block text-sm font-medium text-gray-700 mb-1">Selecionar Processo</label>
            <select name="process_id" class="w-full border-gray-300 rounded-md text-sm" required>
                <option value="">Selecione...</option>
                @foreach ($processes as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-1 flex items-end justify-end">
            <button
                class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>

    <div id="processes-list" class="space-y-4">
        @foreach ($project->projectProcesses as $pp)
            <div id="process-card-{{ $pp->id }}"
                class="bg-white p-4 rounded shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold text-gray-800">{{ $pp->process->name }}</div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openUploadModal('upload-pp-{{ $pp->id }}')"
                            class="inline-flex items-center gap-1 px-4 py-2 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                            <i class="fa-regular fa-image"></i> Adicionar Imagens
                        </button>
                        <form method="POST" action="{{ route('admin.project-processes.destroy', $pp) }}"
                            class="js-process-destroy" data-id="{{ $pp->id }}"
                            onsubmit="return confirm('Remover processo deste projeto?');">
                            @csrf @method('DELETE')
                            <button
                                class="inline-flex items-center gap-1 px-4 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                <i class="fa-regular fa-trash"></i> Remover
                            </button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.project-processes.update', $pp) }}"
                    class="grid grid-cols-12 gap-3 items-start mb-3 js-process-update"
                    data-id="{{ $pp->id }}">
                    @csrf @method('PUT')
                    <div class="col-span-10">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="description" rows="2" class="w-full border-gray-300 rounded-md text-sm">{{ $pp->description }}</textarea>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                        <input type="number" name="order" value="{{ $pp->order }}"
                            class="w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div class="col-span-1 flex items-end">
                        <button
                            class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm"
                            title="Salvar">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                </form>

                <div id="pp-{{ $pp->id }}-images"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach ($pp->images as $img)
                        <div id="img-card-{{ $img->id }}" class="border rounded overflow-hidden">
                            <img src="{{ asset($img->image) }}"
                                class="w-full h-28 object-cover cursor-pointer"
                                onclick="openUploadModal('edit-img-{{ $img->id }}')">
                            <div id="img-cap-{{ $img->id }}" class="p-2 text-xs text-gray-600 truncate">
                                {{ $img->title ?: 'Sem título' }}</div>
                        </div>

                        <!-- Modal editar imagem -->
                        <div id="edit-img-{{ $img->id }}"
                            class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold">Editar Imagem</h4>
                                    <button type="button"
                                        onclick="closeUploadModal('edit-img-{{ $img->id }}')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <form id="img-form-{{ $img->id }}" method="POST"
                                    action="{{ route('admin.project-images.update', $img) }}"
                                    class="space-y-3 js-image-update" data-img-id="{{ $img->id }}">
                                    @csrf @method('PUT')
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title" value="{{ $img->title }}"
                                            class="w-full border-gray-300 rounded-md text-sm">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <textarea name="description" rows="3" class="w-full border-gray-300 rounded-md text-sm">{{ $img->description }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Solução
                                            Relacionada</label>
                                        <select name="solution"
                                            class="w-full border-gray-300 rounded-md text-sm">
                                            <option value="">Selecione...</option>
                                            @foreach ($project->solutions as $sol)
                                                <option value="{{ $sol->title }}"
                                                    @selected($img->solution === $sol->title)>{{ $sol->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                        <input type="number" name="order" value="{{ $img->order }}"
                                            class="w-full border-gray-300 rounded-md text-sm">
                                    </div>
                                </form>
                                <div class="flex items-center justify-between mt-4">
                                    <button type="button"
                                        onclick="closeUploadModal('edit-img-{{ $img->id }}')"
                                        class="px-4 py-2 text-sm rounded border">Cancelar</button>
                                    <div class="flex items-center gap-2">
                                        <button form="img-form-{{ $img->id }}"
                                            class="px-4 py-2 text-sm bg-orange-600 text-white rounded">Salvar</button>
                                        <form class="js-image-destroy" method="POST"
                                            action="{{ route('admin.project-images.destroy', $img) }}"
                                            onsubmit="return confirm('Excluir esta imagem?');">
                                            @csrf @method('DELETE')
                                            <button
                                                class="px-4 py-2 text-sm bg-red-600 text-white rounded">Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Modal upload imagens -->
                <div id="upload-pp-{{ $pp->id }}"
                    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold">Adicionar Imagens</h4>
                            <button type="button"
                                onclick="closeUploadModal('upload-pp-{{ $pp->id }}')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <form method="POST"
                            action="{{ route('admin.project-processes.images.store', $pp) }}"
                            enctype="multipart/form-data" class="space-y-4 js-images-store"
                            data-pp-id="{{ $pp->id }}">
                            @csrf
                            <input type="file" name="images[]" multiple accept="image/*"
                                class="block w-full border border-dashed border-gray-300 rounded p-4 cursor-pointer">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    onclick="closeUploadModal('upload-pp-{{ $pp->id }}')"
                                    class="px-4 py-2 text-sm rounded border">Cancelar</button>
                                <button
                                    class="px-4 py-2 text-sm bg-orange-600 text-white rounded">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function openUploadModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
        }

        function closeUploadModal(id) {
            document.getElementById(id)?.classList.add('hidden');
        }
    </script>
</div>
