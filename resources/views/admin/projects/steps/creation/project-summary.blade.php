<div class="mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Resumo do Projeto</h3>
    </div>

    <form method="POST" action="{{ route('admin.projects.summary.update', $project) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea name="summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('summary', $project->summary) }}</textarea>
        </div>

        <div class="flex justify-end">
            <button
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded border border-transparent text-sm hover:bg-gray-900">
                <i class="fa-solid fa-rotate-right"></i>
                <span>Salvar resumo</span>
            </button>
        </div>
    </form>
</div>
