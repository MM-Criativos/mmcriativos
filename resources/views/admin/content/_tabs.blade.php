<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.content.dashboard') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->routeIs('admin.content.dashboard') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:text-orange-500' }}">
        Dashboard
    </a>

    <a href="{{ route('admin.services.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/services*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:text-orange-500' }}">
        Serviços
    </a>

    <a href="{{ route('admin.skills.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/skills*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:text-orange-500' }}">
        Habilidades
    </a>

    <a href="{{ route('admin.processes.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/processes*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:text-orange-500' }}">
        Processos
    </a>
</div>
