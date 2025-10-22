<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Projeto</h2>
            <a href="{{ route('admin.projects.index') }}"
                class="inline-flex items-center gap-1.5 px-6 py-3.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 transition-colors duration-200">Voltar</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.projects.update', $project) }}"
                        enctype="multipart/form-data" class="space-y-6 js-project-update">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-2">Informações Gerais</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" name="name" value="{{ old('name', $project->name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug', $project->slug) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <select name="client_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="">Selecione...</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id', $project->client_id) == $client->id)>
                                            {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Serviço</label>
                                <select name="service_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="">Selecione...</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected(old('service_id', $project->service_id) == $service->id)>
                                            {{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('summary', $project->summary) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Cover --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cover</label>
                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="cover" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'cover')">

                                    @if ($project->cover)
                                        <img id="preview-cover" src="{{ asset($project->cover) }}" alt="Cover"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-cover"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Cover
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Thumb --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Thumb</label>
                                <div class="relative group cursor-pointer w-40 h-40">
                                    <input type="file" name="thumb" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                        onchange="previewImage(event, 'thumb')">

                                    @if ($project->thumb)
                                        <img id="preview-thumb" src="{{ asset($project->thumb) }}" alt="Thumb"
                                            class="w-40 h-40 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                    @else
                                        <div id="preview-thumb"
                                            class="flex items-center justify-center w-40 h-40 border border-dashed border-gray-300 rounded bg-gray-50 text-gray-400 text-xs text-center group-hover:bg-orange-50">
                                            <i class="fa-regular fa-image text-base mr-1"></i> Thumb
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Vídeo --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Vídeo (URL)</label>
                                <input type="text" name="video" value="{{ old('video', $project->video) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md" placeholder="https://...">
                            </div>
                        </div>


                        <div class="flex justify-end">
                            <button
                                class="inline-flex items-center px-5 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid text-sm">Salvar
                                Informações</button>
                        </div>
                    </form>

                    <script>
                        function previewImage(event, id) {
                            const reader = new FileReader();
                            const preview = document.getElementById(`preview-${id}`);
                            reader.onload = () => {
                                if (preview.tagName === 'IMG') {
                                    preview.src = reader.result;
                                } else {
                                    preview.outerHTML =
                                        `\n                    <img id="preview-${id}" src="${reader.result}" class=\"w-40 h-40 object-cover rounded border border-gray-200\" />`;
                                }
                            };
                            reader.readAsDataURL(event.target.files[0]);
                        }
                    </script>

                    <hr class="my-8">

                    <h3 class="text-lg font-semibold mb-2">Desafios</h3>
                    <div id="challenges-list" class="space-y-3">
                        @foreach ($project->challenges as $challenge)
                            <div id="challenge-{{ $challenge->id }}"
                                class="bg-white p-4 rounded shadow-sm border border-gray-100">
                                <form method="POST" action="{{ route('admin.challenges.update', $challenge) }}"
                                    class="grid grid-cols-12 gap-3 js-challenge-update" data-id="{{ $challenge->id }}">
                                    @csrf @method('PUT')
                                    <div class="col-span-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                        <input type="text" name="title" value="{{ $challenge->title }}"
                                            class="w-full border-gray-300 rounded-md text-sm" required>
                                    </div>
                                    <div class="col-span-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                        <input type="text" name="description" value="{{ $challenge->description }}"
                                            class="w-full border-gray-300 rounded-md text-sm">
                                    </div>
                                    <div class="col-span-1 flex items-end gap-2 justify-end">
                                        <button
                                            class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm"
                                            title="Atualizar">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                </form>
                                <form method="POST" action="{{ route('admin.challenges.destroy', $challenge) }}"
                                    class="js-challenge-destroy" data-id="{{ $challenge->id }}"
                                    onsubmit="return confirm('Remover desafio?');">
                                    @csrf @method('DELETE')
                                    <button
                                        class="inline-flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                        title="Apagar">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                    </div>
                    @endforeach

                    <form method="POST" action="{{ route('admin.projects.challenges.store', $project) }}"
                        class="bg-white p-4 rounded shadow-sm border border-gray-100 js-challenge-store">
                        @csrf
                        <h4 class="font-medium text-gray-800 mb-3">Adicionar novo desafio</h4>
                        <div class="grid grid-cols-12 gap-3 items-end">
                            <div class="col-span-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" name="title"
                                    class="w-full border-gray-300 rounded-md text-sm" placeholder="Novo desafio"
                                    required>
                            </div>
                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                <input type="text" name="description"
                                    class="w-full border-gray-300 rounded-md text-sm" placeholder="Descrição">
                            </div>
                            <div class="col-span-1 flex items-end justify-end">
                                <button
                                    class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <hr class="my-8">

                <h3 class="text-lg font-semibold mb-2">Soluções</h3>
                <div id="solutions-list" class="space-y-3">
                    @foreach ($project->solutions as $solution)
                        <div id="solution-{{ $solution->id }}"
                            class="bg-white p-4 rounded shadow-sm border border-gray-100">
                            <form method="POST" action="{{ route('admin.solutions.update', $solution) }}"
                                class="grid grid-cols-12 gap-3 js-solution-update" data-id="{{ $solution->id }}">
                                @csrf @method('PUT')
                                <div class="col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                    <input type="text" name="title" value="{{ $solution->title }}"
                                        class="w-full border-gray-300 rounded-md text-sm" required>
                                </div>
                                <div class="col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <input type="text" name="description" value="{{ $solution->description }}"
                                        class="w-full border-gray-300 rounded-md text-sm">
                                </div>
                                <div class="col-span-1 flex items-end gap-2 justify-end">
                                    <button
                                        class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm"
                                        title="Atualizar">
                                        <i class="fa-solid fa-rotate-right"></i>
                                    </button>
                            </form>
                            <form method="POST" action="{{ route('admin.solutions.destroy', $solution) }}"
                                class="js-solution-destroy" data-id="{{ $solution->id }}"
                                onsubmit="return confirm('Remover solução?');">
                                @csrf @method('DELETE')
                                <button
                                    class="inline-flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                    title="Apagar">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                </div>
                @endforeach

                <form method="POST" action="{{ route('admin.projects.solutions.store', $project) }}"
                    class="bg-white p-4 rounded shadow-sm border border-gray-100 js-solution-store">
                    @csrf
                    <h4 class="font-medium text-gray-800 mb-3">Adicionar nova solução</h4>
                    <div class="grid grid-cols-12 gap-3 items-end">
                        <div class="col-span-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="title" class="w-full border-gray-300 rounded-md text-sm"
                                placeholder="Nova solução" required>
                        </div>
                        <div class="col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <input type="text" name="description"
                                class="w-full border-gray-300 rounded-md text-sm" placeholder="Descrição">
                        </div>
                        <div class="col-span-1 flex items-end justify-end">
                            <button
                                class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <hr class="my-8">

            <h3 class="text-lg font-semibold mb-2">Processos</h3>
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

            <hr class="my-8">

            <h3 class="text-lg font-semibold mb-2">Skills e Competências</h3>
            <div class="bg-white p-4 rounded shadow-sm border border-gray-100 mb-4">
                <form id="attachSkillsForm" method="POST"
                    action="{{ route('admin.projects.skills.attach', $project) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-12 gap-3 items-end">
                        <div class="col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
                            <select id="skillSelect" name="skill_id"
                                class="w-full border-gray-300 rounded-md text-sm" required>
                                <option value="">Selecione...</option>
                                @foreach ($skills as $sk)
                                    <option value="{{ $sk->id }}">{{ $sk->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Competências</label>
                            <div id="competencyBadges" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                    <div id="competencyInputs"></div>
                    <div class="flex justify-end">
                        <button
                            class="inline-flex items-center gap-1 px-5 py-3 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                            <i class="fa-solid fa-plus"></i> Adicionar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 rounded shadow-sm border border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3">Competências vinculadas</h4>
                <div id="psc-container">
                    @include('admin.projects.partials.skill_links', ['project' => $project])
                </div>
            </div>

            <script>
                const skillsData = @json($skills);
                const skillSelect = document.getElementById('skillSelect');
                const badges = document.getElementById('competencyBadges');
                const inputs = document.getElementById('competencyInputs');
                const pscContainer = document.getElementById('psc-container');
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                function renderCompetencies(skillId) {
                    badges.innerHTML = '';
                    inputs.innerHTML = '';
                    if (!skillId) return;
                    const skill = skillsData.find(s => String(s.id) === String(skillId));
                    (skill?.competencies || []).forEach(c => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'px-3 py-1 rounded-full border text-xs bg-gray-100 hover:bg-gray-200';
                        btn.textContent = c.competency;
                        btn.dataset.id = c.id;
                        btn.dataset.selected = 'false';
                        btn.addEventListener('click', () => {
                            const selected = btn.dataset.selected === 'true';
                            btn.dataset.selected = String(!selected);
                            btn.className = 'px-3 py-1 rounded-full border text-xs ' + (!selected ?
                                'bg-orange-600 text-white border-orange-600' : 'bg-gray-100 hover:bg-gray-200');
                            syncInputs();
                        });
                        badges.appendChild(btn);
                    });
                }

                function syncInputs() {
                    inputs.innerHTML = '';
                    badges.querySelectorAll('button').forEach(btn => {
                        if (btn.dataset.selected === 'true') {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'competencies[]';
                            input.value = btn.dataset.id;
                            inputs.appendChild(input);
                        }
                    });
                }

                // Show selected skill name above badges
                const selectedSkillNameEl = document.createElement('div');
                selectedSkillNameEl.className = 'text-sm font-semibold text-gray-800 mb-1';
                badges.parentElement.prepend(selectedSkillNameEl);

                skillSelect?.addEventListener('change', e => {
                    const id = e.target.value;
                    const skill = skillsData.find(s => String(s.id) === String(id));
                    selectedSkillNameEl.textContent = skill ? skill.name : '';
                    renderCompetencies(id);
                });

                // AJAX: attach skills
                document.getElementById('attachSkillsForm')?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const form = e.currentTarget;
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.html) pscContainer.innerHTML = data.html;
                    }
                });

                // Delegate delete of PSC without reload
                pscContainer?.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.matches('.js-psc-delete')) {
                        e.preventDefault();
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.html) pscContainer.innerHTML = data.html;
                        }
                    }
                });

                // AJAX: update image metadata
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList?.contains('js-image-update')) {
                        e.preventDefault();
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const id = form.dataset.imgId;
                            const cap = document.getElementById(`img-cap-${id}`);
                            if (cap && data.image) {
                                cap.textContent = data.image.title || 'Sem título';
                            }
                            closeUploadModal(`edit-img-${id}`);
                        }
                    }
                });

                // AJAX: delete image
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList?.contains('js-image-destroy')) {
                        e.preventDefault();
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.removed && data.id) {
                                const card = document.getElementById(`img-card-${data.id}`);
                                if (card) card.remove();
                                closeUploadModal(`edit-img-${data.id}`);
                            }
                        }
                    }
                });

                // AJAX: add images to process
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList?.contains('js-images-store')) {
                        e.preventDefault();
                        const ppId = form.dataset.ppId;
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const grid = document.getElementById(`pp-${ppId}-images`);
                            if (grid && Array.isArray(data.images)) {
                                const solutions = @json($project->solutions->map(fn($s) => ['title' => $s->title]));
                                data.images.forEach(img => {
                                    const opts = solutions.map(s =>
                                        `<option value="${s.title}">${s.title}</option>`).join('');
                                    const actionBase = form.action.replace(`/project-processes/${ppId}/images`,
                                        '');
                                    const updateAction = `${actionBase}/project-images/${img.id}`;
                                    const html = `
                                            <div id="img-card-${img.id}" class="border rounded overflow-hidden">
                                                <img src="/${img.image}" class="w-full h-28 object-cover cursor-pointer" onclick=\"openUploadModal('edit-img-${img.id}')\">
                                                <div id="img-cap-${img.id}" class="p-2 text-xs text-gray-600 truncate">${img.title || 'Sem título'}</div>
                                            </div>
                                            <div id="edit-img-${img.id}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                                                <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
                                                    <div class="flex items-center justify-between mb-4">
                                                        <h4 class="font-semibold">Editar Imagem</h4>
                                                        <button type="button" onclick=\"closeUploadModal('edit-img-${img.id}')\"><i class=\"fa-solid fa-xmark\"></i></button>
                                                    </div>
                                                    <form id="img-form-${img.id}" method="POST" action="${updateAction}" class="space-y-3 js-image-update" data-img-id="${img.id}">
                                                        <input type="hidden" name="_token" value="${csrf || ''}">
                                                        <input type="hidden" name="_method" value="PUT">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                                            <input type="text" name="title" value="" class="w-full border-gray-300 rounded-md text-sm">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                                            <textarea name="description" rows="3" class="w-full border-gray-300 rounded-md text-sm"></textarea>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Solução Relacionada</label>
                                                            <select name="solution" class="w-full border-gray-300 rounded-md text-sm">
                                                                <option value="">Selecione...</option>
                                                                ${opts}
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                                            <input type="number" name="order" value="0" class="w-full border-gray-300 rounded-md text-sm">
                                                        </div>
                                                    </form>
                                                    <div class="flex items-center justify-between mt-4">
                                                        <button type="button" onclick=\"closeUploadModal('edit-img-${img.id}')\" class="px-4 py-2 text-sm rounded border">Cancelar</button>
                                                        <div class="flex items-center gap-2">
                                                            <button form="img-form-${img.id}" class="px-4 py-2 text-sm bg-orange-600 text-white rounded">Salvar</button>
                                                            <form class="js-image-destroy" method="POST" action="${updateAction}" onsubmit="return confirm('Excluir esta imagem?');">
                                                                <input type="hidden" name="_token" value="${csrf || ''}">
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <button class="px-4 py-2 text-sm bg-red-600 text-white rounded">Excluir</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>`;
                                    grid.insertAdjacentHTML('beforeend', html);
                                });
                            }
                            closeUploadModal(`upload-pp-${ppId}`);
                            form.reset();
                        }
                    }
                });

                // AJAX: project basic info (no reload)
                document.querySelector('.js-project-update')?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const form = e.currentTarget;
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data?.project?.cover) {
                            const img = document.getElementById('preview-cover');
                            if (img && img.tagName === 'IMG') img.src = data.project.cover;
                        }
                    }
                });

                // AJAX: challenges
                const chList = document.getElementById('challenges-list');
                chList?.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList.contains('js-challenge-update')) {
                        e.preventDefault();
                        await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                    }
                    if (form.classList.contains('js-challenge-destroy')) {
                        e.preventDefault();
                        const id = form.dataset.id;
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.removed) document.getElementById(`challenge-${id}`)?.remove();
                        }
                    }
                    if (form.classList.contains('js-challenge-store')) {
                        e.preventDefault();
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const c = data.challenge;
                            const html = `
                                    <div id="challenge-${c.id}" class="bg-white p-4 rounded shadow-sm border border-gray-100">
                                        <form method="POST" action="/admin/challenges/${c.id}" class="grid grid-cols-12 gap-3 js-challenge-update" data-id="${c.id}">
                                            <input type="hidden" name="_token" value="${csrf || ''}">
                                            <input type="hidden" name="_method" value="PUT">
                                            <div class="col-span-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                                <input type="text" name="title" value="${c.title || ''}" class="w-full border-gray-300 rounded-md text-sm" required>
                                            </div>
                                            <div class="col-span-6">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                                <input type="text" name="description" value="${c.description || ''}" class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                            <div class="col-span-1 flex items-end gap-2 justify-end">
                                                <button class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm" title="Atualizar">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </button>
                                        </form>
                                                <form method="POST" action="/admin/challenges/${c.id}" class="js-challenge-destroy" data-id="${c.id}" onsubmit="return confirm('Remover desafio?');">
                                                    <input type="hidden" name="_token" value="${csrf || ''}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button class="inline-flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm" title="Apagar">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                    </div>`;
                            chList.insertAdjacentHTML('afterbegin', html);
                            form.reset();
                        }
                    }
                });

                // AJAX: solutions
                const solList = document.getElementById('solutions-list');
                solList?.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList.contains('js-solution-update')) {
                        e.preventDefault();
                        await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                    }
                    if (form.classList.contains('js-solution-destroy')) {
                        e.preventDefault();
                        const id = form.dataset.id;
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.removed) document.getElementById(`solution-${id}`)?.remove();
                        }
                    }
                    if (form.classList.contains('js-solution-store')) {
                        e.preventDefault();
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            const s = data.solution;
                            const html = `
                                    <div id="solution-${s.id}" class="bg-white p-4 rounded shadow-sm border border-gray-100">
                                        <form method="POST" action="/admin/solutions/${s.id}" class="grid grid-cols-12 gap-3 js-solution-update" data-id="${s.id}">
                                            <input type="hidden" name="_token" value="${csrf || ''}">
                                            <input type="hidden" name="_method" value="PUT">
                                            <div class="col-span-5">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                                <input type="text" name="title" value="${s.title || ''}" class="w-full border-gray-300 rounded-md text-sm" required>
                                            </div>
                                            <div class="col-span-6">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                                <input type="text" name="description" value="${s.description || ''}" class="w-full border-gray-300 rounded-md text-sm">
                                            </div>
                                            <div class="col-span-1 flex items-end gap-2 justify-end">
                                                <button class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm" title="Atualizar">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </button>
                                        </form>
                                                <form method="POST" action="/admin/solutions/${s.id}" class="js-solution-destroy" data-id="${s.id}" onsubmit="return confirm('Remover solução?');">
                                                    <input type="hidden" name="_token" value="${csrf || ''}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button class="inline-flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 text-sm" title="Apagar">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                    </div>`;
                            solList.insertAdjacentHTML('afterbegin', html);
                            form.reset();
                        }
                    }
                });

                // AJAX: processes add/update/destroy
                const procStore = document.querySelector('.js-process-store');
                const procList = document.getElementById('processes-list');
                procStore?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const form = e.currentTarget;
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const pp = data.project_process;
                        const updateUrl = form.dataset.updateBase.replace('__ID__', pp.id);
                        const destroyUrl = form.dataset.destroyBase.replace('__ID__', pp.id);
                        const imagesStoreUrl = form.dataset.imagesStoreBase.replace('__ID__', pp.id);
                        const cardHtml = `
                                <div id="process-card-${pp.id}" class="bg-white p-4 rounded shadow-sm border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="font-semibold text-gray-800">${pp.process.name}</div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="openUploadModal('upload-pp-${pp.id}')" class="inline-flex items-center gap-1 px-4 py-2 bg-orange-600 text-white rounded text-sm hover:bg-orange-700"><i class="fa-regular fa-image"></i> Adicionar Imagens</button>
                                            <form method="POST" action="${destroyUrl}" class="js-process-destroy" data-id="${pp.id}" onsubmit="return confirm('Remover processo deste projeto?');">
                                                <input type="hidden" name="_token" value="${csrf || ''}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button class="inline-flex items-center gap-1 px-4 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700"><i class="fa-regular fa-trash"></i> Remover</button>
                                            </form>
                                        </div>
                                    </div>
                                    <form method="POST" action="${updateUrl}" class="grid grid-cols-12 gap-3 items-start mb-3 js-process-update" data-id="${pp.id}">
                                        <input type="hidden" name="_token" value="${csrf || ''}">
                                        <input type="hidden" name="_method" value="PUT">
                                        <div class="col-span-10">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                            <textarea name="description" rows="2" class="w-full border-gray-300 rounded-md text-sm">${pp.description || ''}</textarea>
                                        </div>
                                        <div class="col-span-1">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                                            <input type="number" name="order" value="${pp.order || 0}" class="w-full border-gray-300 rounded-md text-sm">
                                        </div>
                                        <div class="col-span-1 flex items-end">
                                            <button class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm" title="Salvar"><i class="fa-solid fa-rotate-right"></i></button>
                                        </div>
                                    </form>
                                    <div id="pp-${pp.id}-images" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
                                    <div id="upload-pp-${pp.id}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                                        <div class="bg-white rounded shadow-lg w-full max-w-lg p-6">
                                            <div class="flex items-center justify-between mb-4"><h4 class="font-semibold">Adicionar Imagens</h4>
                                                <button type="button" onclick="closeUploadModal('upload-pp-${pp.id}')"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <form method="POST" action="${imagesStoreUrl}" enctype="multipart/form-data" class="space-y-4 js-images-store" data-pp-id="${pp.id}">
                                                <input type="hidden" name="_token" value="${csrf || ''}">
                                                <input type="file" name="images[]" multiple accept="image/*" class="block w-full border border-dashed border-gray-300 rounded p-4 cursor-pointer">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" onclick="closeUploadModal('upload-pp-${pp.id}')" class="px-4 py-2 text-sm rounded border">Cancelar</button>
                                                    <button class="px-4 py-2 text-sm bg-orange-600 text-white rounded">Salvar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>`;
                        procList.insertAdjacentHTML('beforeend', cardHtml);
                        form.reset();
                    }
                });

                procList?.addEventListener('submit', async (e) => {
                    const form = e.target;
                    if (form.classList.contains('js-process-update')) {
                        e.preventDefault();
                        await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                    }
                    if (form.classList.contains('js-process-destroy')) {
                        e.preventDefault();
                        const id = form.dataset.id;
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.removed) document.getElementById(`process-card-${id}`)?.remove();
                        }
                    }
                });
            </script>
        </div>
    </div>
    </div>
    </div>
</x-app-layout>
