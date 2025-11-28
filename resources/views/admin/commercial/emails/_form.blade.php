@props(['template'])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Chave (key)</label>
        <input name="key" value="{{ old('key', $template->key ?? '') }}"
            class="w-full appearance-none bg-white dark:bg-dark-800 border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-150"
            required />
    </div>

    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Nome</label>
        <input name="name" value="{{ old('name', $template->name ?? '') }}"
            class="w-full appearance-none bg-white dark:bg-dark-800 border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-150"
            required />
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Assunto</label>
        <input name="subject" value="{{ old('subject', $template->subject ?? '') }}"
            class="w-full appearance-none bg-white dark:bg-dark-800 border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-150"
            required />
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Corpo</label>
        <textarea name="body" rows="8"
            class="w-full appearance-none bg-white dark:bg-dark-800 border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-150"
            required>{{ old('body', $template->body ?? '') }}</textarea>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Use variáveis como {{ '{' }}{{ '{client_name}' }}{{ '}' }} e
            {{ '{' }}{{ '{budget_link}' }}{{ '}' }}.
        </div>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Rodapé</label>
        <textarea name="footer" rows="3"
            class="w-full appearance-none bg-white dark:bg-dark-800 border border-gray-300 dark:border-dark-600 text-gray-800 dark:text-gray-200 rounded px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-150">{{ old('footer', $template->footer ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))
            class="rounded border-gray-400 dark:border-dark-600 text-orange-600 focus:ring-orange-500" />
        <span class="text-sm text-gray-700 dark:text-gray-300">Ativo</span>
    </div>
</div>
