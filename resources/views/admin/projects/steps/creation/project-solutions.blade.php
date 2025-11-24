<div class="mt-6">

    {{-- 🔶 FORM PRINCIPAL: engloba todas as soluções --}}
    <form id="form-update-all-solutions" method="POST"
        action="{{ route('admin.projects.solutions.updateAll', $project) }}">
        @csrf
        @method('PUT')

        {{-- 🔹 Cabeçalho --}}
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Soluções do Projeto</h3>
            <button class="btn-mmcriativos inline-flex items-center px-4 py-2 rounded-xl">
                <i class="fa-duotone fa-solid fa-arrow-rotate-right icon-project mr-2"></i>
                Atualizar todas
            </button>
        </div>

        {{-- 🔹 Lista de soluções --}}
        <div class="space-y-4" id="solutions-list">
            @foreach ($project->solutions as $solution)
                <div
                    class="bg-[#f5f5f5] dark:bg-[#262626]  p-4 rounded shadow-sm border border-gray-100 flex flex-col gap-3 hover:shadow-md transition">
                    <input type="hidden" name="solutions[{{ $solution->id }}][id]" value="{{ $solution->id }}">

                    <div class="grid grid-cols-12 gap-3 items-end">
                        {{-- 🔹 Título --}}
                        <div class="col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <input type="text" name="solutions[{{ $solution->id }}][title]"
                                value="{{ $solution->title }}"
                                class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm" required>
                        </div>

                        {{-- 🔹 Descrição (expandida) --}}
                        <div class="col-span-8">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <input type="text" name="solutions[{{ $solution->id }}][description]"
                                value="{{ $solution->description }}"
                                class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm">
                        </div>

                        {{-- 🔹 Excluir --}}
                        <div class="col-span-1 flex items-end justify-end">
                            <button type="button"
                                class="js-delete-solution inline-flex items-center justify-center w-full px-4 py-3 bg-red-500 text-white border-red-500 hover:bg-white dark:hover:bg-black hover:text-red-500 rounded-md"
                                data-url="{{ route('admin.solutions.destroy', $solution) }}" title="Excluir solução">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </form>

    {{-- 🔶 Form oculto para exclusão --}}
    <form id="delete-solution-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- 🔶 Nova Solução --}}
    <form method="POST" action="{{ route('admin.projects.solutions.store', $project) }}"
        class="bg-[#f5f5f5] dark:bg-[#262626] p-4 rounded shadow-sm border border-gray-100 mt-4">
        @csrf
        <h4 class="font-medium text-gray-800 mb-3">Adicionar nova solução</h4>
        <div class="grid grid-cols-12 gap-3 items-end">
            <div class="col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="title"
                    class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm" placeholder="Nova solução"
                    required>
            </div>
            <div class="col-span-8">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <input type="text" name="description"
                    class="w-full bg-white dark:!bg-black border-gray-300 rounded-md text-sm" placeholder="Descrição">
            </div>
            <div class="col-span-1 flex items-end justify-end">
                <button class="btn-mmcriativos inline-flex items-center justify-center w-full px-4 py-3 rounded-md">
                    <i class="fa-duotone fa-solid fa-circle-plus icon-project"></i>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- 🔸 JS para exclusão --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-delete-solution').forEach(button => {
            button.addEventListener('click', () => {
                const url = button.dataset.url;
                if (!confirm('Remover solução?')) return;

                const form = document.getElementById('delete-solution-form');
                form.action = url;
                form.submit();
            });
        });
    });
</script>
