<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.commercial.dashboard') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->routeIs('admin.commercial.dashboard') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        Dashboard
    </a>
    <a href="{{ route('admin.commercial.budgets.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/budgets*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        Orçamentos
    </a>
    <a href="{{ route('admin.commercial.plans.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/plans*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        Planos
    </a>
    <a href="{{ route('admin.commercial.extras.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/extras*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        Extras
    </a>
    <a href="{{ route('admin.commercial.email-templates.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/email-templates*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        E-mails
    </a>
    <a href="{{ route('admin.commercial.kpi.index') }}"
        class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/kpi*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
        KPI
    </a>
</div>
