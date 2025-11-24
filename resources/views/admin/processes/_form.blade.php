@php
    /** @var \App\Models\Process $process */
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
        <input type="text" name="name" value="{{ old('name', $process->name) }}"
            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md" required>
        @error('name')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $process->slug) }}"
            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
            placeholder="gerado automaticamente se vazio">
        @error('slug')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ícone</label>
        <input type="text" name="icon" value="{{ old('icon', $process->icon) }}"
            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md"
            placeholder="Ex: fa-solid fa-gears ou &lt;i class='fa-solid fa-gears'&gt;&lt;/i&gt;">
        <p class="mt-1 text-xs text-gray-500">
            Informe as classes do Font Awesome ou cole a tag &lt;i&gt; completa.
        </p>
        @error('icon')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
        <input type="number" name="order" value="{{ old('order', $process->order) }}"
            class="mt-1 block w-full bg-[#f5f5f5] dark:!bg-[#262626] border-gray-300 rounded-md" min="0">
        @error('order')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>
