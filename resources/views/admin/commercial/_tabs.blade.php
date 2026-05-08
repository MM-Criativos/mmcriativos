<div class="mb-4 flex flex-wrap gap-2">
    @if (Route::has('admin.commercial.dashboard'))
        <a href="{{ route('admin.commercial.dashboard') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->routeIs('admin.commercial.dashboard') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Dashboard
        </a>
    @endif
    @if (Route::has('admin.clients.index'))
        <a href="{{ route('admin.clients.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/clients*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Clientes
        </a>
    @endif
    @if (Route::has('admin.testimonials.index'))
        <a href="{{ route('admin.testimonials.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/testimonials*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Depoimentos
        </a>
    @endif
    @if (Route::has('admin.commercial.budgets.index'))
        <a href="{{ route('admin.commercial.budgets.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/budgets*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Orçamentos
        </a>
    @endif
    @if (Route::has('admin.commercial.plans.index'))
        <a href="{{ route('admin.commercial.plans.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/plans*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Planos
        </a>
    @endif
    @if (Route::has('admin.commercial.extras.index'))
        <a href="{{ route('admin.commercial.extras.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/extras*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            Extras
        </a>
    @endif
    @if (Route::has('admin.commercial.email-templates.index'))
        <a href="{{ route('admin.commercial.email-templates.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/email-templates*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            E-mails
        </a>
    @endif
    @if (Route::has('admin.commercial.kpi.index') && auth()->user()?->role === 'admin')
        <a href="{{ route('admin.commercial.kpi.index') }}"
            class="px-3 py-2 bg-orange-600 text-black dark:text-white rounded border border-transparent font-semibold text-xs uppercase tracking-widest hover:bg-white hover:text-orange-600 hover:border-orange-600 hover:border-solid {{ request()->is('admin/commercial/kpi*') ? '' : 'bg-white text-orange-600 border-orange-600 border-solid dark:bg-dark-800 dark:white' }}">
            KPI
        </a>
    @endif
</div>
