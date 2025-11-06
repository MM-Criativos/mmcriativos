<div class="mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Resumo do Projeto</h3>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea name="summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md">{{ old('summary', $project->summary) }}</textarea>
        </div>
    </div>
</div>
