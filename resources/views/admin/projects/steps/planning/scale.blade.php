<div class="mt-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Régua perceptiva</h3>
        <form method="POST" action="{{ route('admin.projects.planning.scale.email', $project, false) }}" class="flex items-center gap-2">
            @csrf
            <input type="email" name="email" value="{{ old('email', optional($project->client)->email) }}" placeholder="e-mail do cliente" class="border-gray-300 rounded-md text-sm py-1 px-2 w-56" />
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-white text-gray-700 rounded border border-gray-300 hover:bg-gray-50">
                <i class="fa-regular fa-envelope"></i>
                <span>Enviar por e-mail</span>
            </button>
        </form>
    </div>

    <p class="text-sm text-gray-600">Este formulário é enviado ao cliente. Use o botão acima para enviar o link seguro por e‑mail.</p>
</div>
