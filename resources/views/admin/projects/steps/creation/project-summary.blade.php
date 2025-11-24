<div class="mt-6">
    <div class="mb-5">
        <h3 class="text-lg font-semibold text-gray-800">Resumo do Projeto</h3>
    </div>

    <form method="POST" action="{{ route('admin.projects.summary.update', $project) }}"
        class="bg-[#f5f5f5] dark:bg-[#262626] rounded-lg shadow-sm p-4">
        @csrf
        <div>
            <label class="block  text-sm font-medium text-gray-700">Descrição</label>
            <textarea name="summary" rows="4" class="mt-1 block w-full bg-white dark:bg-black border-gray-300 rounded-md">{{ old('summary', $project->summary) }}</textarea>
        </div>

        <div class="flex justify-center mt-4">
            <button class="btn-mmcriativos inline-flex items-center gap-2 px-4 py-2 rounded-xl">
                <i class="fa-duotone fa-solid fa-arrow-down-to-arc icon-project"></i>
                <span>Salvar resumo</span>
            </button>
        </div>
    </form>
</div>
