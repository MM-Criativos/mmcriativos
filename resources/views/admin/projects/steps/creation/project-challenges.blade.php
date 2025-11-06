<div class="mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Desafios do Projeto</h3>
    </div>

    <div class="space-y-6">
        <div id="challenges-list" class="space-y-3">
            @foreach ($project->challenges as $challenge)
                <div id="challenge-{{ $challenge->id }}"
                    class="bg-white p-4 rounded shadow-sm border border-gray-100">
                    <form method="POST" action="{{ route('admin.challenges.update', $challenge) }}"
                        class="grid grid-cols-12 gap-3 js-challenge-update"
                        data-id="{{ $challenge->id }}">
                        @csrf @method('PUT')
                        <div class="col-span-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="title" value="{{ $challenge->title }}"
                                class="w-full border-gray-300 rounded-md text-sm" required>
                        </div>
                        <div class="col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <input type="text" name="description"
                                value="{{ $challenge->description }}"
                                class="w-full border-gray-300 rounded-md text-sm">
                        </div>
                        <div class="col-span-1 flex items-end gap-2 justify-end">
                            <button
                                class="inline-flex items-center justify-center px-4 py-3 bg-orange-600 text-white rounded border border-transparent hover:bg-white hover:text-orange-600 hover:border-orange-600 text-sm"
                                title="Atualizar">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                        </div>
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
            @endforeach
        </div>

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
</div>
