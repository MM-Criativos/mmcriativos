@php
    $title    = 'Dashboard SDR';
    $subTitle = 'Performance de prospecção MM Cloud';

    $periodLabels = [
        'today' => 'Hoje',
        'week'  => 'Esta semana',
        'month' => 'Este mês',
        'all'   => 'Tudo',
    ];

    $outcomeConfig = [
        'reuniao_agendada'      => ['label' => 'Reunião agendada', 'badge' => 'badge-sdr-reuniao',   'dot' => 'dot-sdr-green'],
        'retorno_agendado'      => ['label' => 'Retorno agendado', 'badge' => 'badge-sdr-retorno',   'dot' => 'dot-sdr-blue'],
        'sem_interesse'         => ['label' => 'Sem interesse',    'badge' => 'badge-sdr-interesse', 'dot' => 'dot-sdr-red'],
        'encerrado_manualmente' => ['label' => 'Encerrado',        'badge' => 'badge-sdr-encerrado', 'dot' => 'dot-sdr-gray'],
    ];

    function fmtDuration(?int $seconds): string {
        if ($seconds === null) return '—';
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        return $m . 'min ' . str_pad($s, 2, '0', STR_PAD_LEFT) . 's';
    }
@endphp

@extends('layouts.app')

@section('content')

{{-- ── Badge / Dot styles — classes explícitas para não serem purgadas ──── --}}
<style>
    /* ── Outcome badges ── */
    .badge-sdr-reuniao   { background: #dcfce7; color: #15803d; }
    .badge-sdr-retorno   { background: #dbeafe; color: #1d4ed8; }
    .badge-sdr-interesse { background: #fee2e2; color: #b91c1c; }
    .badge-sdr-encerrado { background: #f3f4f6; color: #6b7280; }

    .dark .badge-sdr-reuniao   { background: rgba(21,128,61,.25);  color: #86efac; }
    .dark .badge-sdr-retorno   { background: rgba(29,78,216,.25);  color: #93c5fd; }
    .dark .badge-sdr-interesse { background: rgba(185,28,28,.25);  color: #fca5a5; }
    .dark .badge-sdr-encerrado { background: rgba(75,85,99,.35);   color: #d1d5db; }

    /* ── Dot colors ── */
    .dot-sdr-green { background: #22c55e; }
    .dot-sdr-blue  { background: #3b82f6; }
    .dot-sdr-red   { background: #f87171; }
    .dot-sdr-gray  { background: #9ca3af; }

    /* ── Conversion rate badges ── */
    .badge-conv-high { background: #dcfce7; color: #15803d; }
    .badge-conv-mid  { background: #fef9c3; color: #a16207; }
    .badge-conv-low  { background: #f3f4f6; color: #6b7280; }

    .dark .badge-conv-high { background: rgba(21,128,61,.25);  color: #86efac; }
    .dark .badge-conv-mid  { background: rgba(161,98,7,.30);   color: #fde68a; }
    .dark .badge-conv-low  { background: rgba(75,85,99,.35);   color: #d1d5db; }

    /* ── Bar fills ── */
    .bar-sdr-green { background: #22c55e; }
    .bar-sdr-blue  { background: #3b82f6; }
    .bar-sdr-red   { background: #f87171; }
    .bar-sdr-gray  { background: #9ca3af; }
    .bar-sdr-orange{ background: #f97316; }
</style>

<div class="p-6 space-y-6">

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📊 Dashboard SDR</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Performance de prospecção MM Cloud</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.commercial.sdr.editor') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700
                      text-sm text-gray-600 dark:text-gray-300 hover:border-orange-400 hover:text-orange-500 transition">
                <i data-lucide="phone-call" class="w-4 h-4"></i>
                Guia SDR
            </a>
            <a href="{{ route('admin.commercial.prospeccao.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700
                      text-sm text-gray-600 dark:text-gray-300 hover:border-orange-400 hover:text-orange-500 transition">
                <i data-lucide="crosshair" class="w-4 h-4"></i>
                Prospecção
            </a>
        </div>
    </div>

    {{-- ── Filtro de período ───────────────────────────────────────────────── --}}
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl w-fit">
        @foreach ($periodLabels as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['period' => $key, 'outcome' => $outcomeFilter]) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition
                      {{ $period === $key
                           ? 'bg-white dark:bg-gray-700 text-orange-500 shadow-sm'
                           : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── KPI Cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total de calls</span>
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <i data-lucide="phone" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                </span>
            </div>
            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $totalCalls }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $periodLabels[$period] }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Reuniões</span>
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-green-600 dark:text-green-400"></i>
                </span>
            </div>
            <p class="text-4xl font-bold text-green-600 dark:text-green-400">{{ $reunioes }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">agendadas {{ $periodLabels[$period] }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Conversão</span>
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/40">
                    <i data-lucide="percent" class="w-4 h-4 text-orange-500 dark:text-orange-400"></i>
                </span>
            </div>
            <p class="text-4xl font-bold text-orange-500 dark:text-orange-400">{{ $conversao }}<span class="text-2xl">%</span></p>
            <div class="mt-2 h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bar-sdr-orange rounded-full transition-all" style="width: {{ min($conversao, 100) }}%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tempo médio</span>
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
                    <i data-lucide="timer" class="w-4 h-4 text-blue-500 dark:text-blue-400"></i>
                </span>
            </div>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ fmtDuration($avgDuration) }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">por call encerrada</p>
        </div>

    </div>

    {{-- ── Linha 2: outcome bars + ranking SDR ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Mini outcome bars --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Resultado das calls</h3>
            @php
                $outcomeCounts = [
                    'reuniao_agendada'      => $reunioes,
                    'retorno_agendado'      => $retornos,
                    'sem_interesse'         => $semInteresse,
                    'encerrado_manualmente' => max(0, $totalCalls - $reunioes - $retornos - $semInteresse),
                ];
                $barClasses = [
                    'reuniao_agendada'      => 'bar-sdr-green',
                    'retorno_agendado'      => 'bar-sdr-blue',
                    'sem_interesse'         => 'bar-sdr-red',
                    'encerrado_manualmente' => 'bar-sdr-gray',
                ];
            @endphp
            <div class="space-y-3">
                @foreach ($outcomeCounts as $key => $count)
                    @php
                        $pct = $totalCalls > 0 ? round(($count / $totalCalls) * 100) : 0;
                        $cfg = $outcomeConfig[$key];
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $cfg['dot'] }}"></span>
                                <span class="text-xs text-gray-600 dark:text-gray-300">{{ $cfg['label'] }}</span>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                                {{ $count }} <span class="font-normal text-gray-400">({{ $pct }}%)</span>
                            </span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $barClasses[$key] }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ranking por SDR --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Ranking por SDR</h3>
            @if ($bySdr->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
                    <i data-lucide="users" class="w-8 h-8 mb-2 opacity-40"></i>
                    <p class="text-sm">Nenhuma call registrada no período</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left pb-2 font-semibold">SDR</th>
                                <th class="text-center pb-2 font-semibold">Calls</th>
                                <th class="text-center pb-2 font-semibold">Reuniões</th>
                                <th class="text-center pb-2 font-semibold">Retornos</th>
                                <th class="text-center pb-2 font-semibold">Conversão</th>
                                <th class="text-center pb-2 font-semibold">Tempo médio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach ($bySdr as $i => $sdr)
                                @php
                                    $taxa = $sdr->total > 0 ? round(($sdr->reunioes / $sdr->total) * 100, 1) : 0;
                                    $convBadge = $taxa >= 30 ? 'badge-conv-high' : ($taxa >= 15 ? 'badge-conv-mid' : 'badge-conv-low');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="py-2.5 pr-4">
                                        <div class="flex items-center gap-2">
                                            @if ($i === 0) <span class="text-base">🥇</span>
                                            @elseif ($i === 1) <span class="text-base">🥈</span>
                                            @elseif ($i === 2) <span class="text-base">🥉</span>
                                            @else <span class="w-5 text-center text-xs text-gray-400">{{ $i + 1 }}</span>
                                            @endif
                                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $sdr->sdr_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-center text-gray-600 dark:text-gray-300">{{ $sdr->total }}</td>
                                    <td class="py-2.5 text-center font-semibold text-green-600 dark:text-green-400">{{ $sdr->reunioes }}</td>
                                    <td class="py-2.5 text-center text-blue-500 dark:text-blue-400">{{ $sdr->retornos }}</td>
                                    <td class="py-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $convBadge }}">
                                            {{ $taxa }}%
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center text-gray-500 dark:text-gray-400 text-xs">
                                        {{ fmtDuration($sdr->avg_sec ? (int) $sdr->avg_sec : null) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- ── Tabela de calls recentes ────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Calls recentes</h3>
            <div class="flex flex-wrap gap-1.5">
                <a href="{{ request()->fullUrlWithQuery(['outcome' => '']) }}"
                   class="px-3 py-1 rounded-full text-xs font-medium transition
                          {{ $outcomeFilter === '' ? 'bg-gray-800 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Todos
                </a>
                @foreach ($outcomeConfig as $key => $cfg)
                    <a href="{{ request()->fullUrlWithQuery(['outcome' => $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-medium transition
                              {{ $outcomeFilter === $key ? 'bg-gray-800 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $cfg['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($sessions->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400 dark:text-gray-500">
                <i data-lucide="phone-missed" class="w-10 h-10 mb-3 opacity-30"></i>
                <p class="text-sm font-medium">Nenhuma call encontrada</p>
                <p class="text-xs mt-1">Ajuste o período ou o filtro de resultado</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-gray-700/40">
                            <th class="text-left px-5 py-3 font-semibold">Empresa</th>
                            <th class="text-left px-4 py-3 font-semibold">Contato</th>
                            <th class="text-left px-4 py-3 font-semibold">SDR</th>
                            <th class="text-center px-4 py-3 font-semibold">Duração</th>
                            <th class="text-center px-4 py-3 font-semibold">Resultado</th>
                            <th class="text-right px-5 py-3 font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                        @foreach ($sessions as $session)
                            @php
                                $dur = ($session->started_at && $session->ended_at)
                                    ? $session->started_at->diffInSeconds($session->ended_at)
                                    : null;
                                $outKey = $session->outcome ?? 'encerrado_manualmente';
                                $cfg    = $outcomeConfig[$outKey] ?? $outcomeConfig['encerrado_manualmente'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition">
                                <td class="px-5 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    {{ $session->company }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                    {{ $session->contact ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $session->sdr_name }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 text-xs tabular-nums">
                                    {{ fmtDuration($dur ? (int) $dur : null) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cfg['badge'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                        {{ $cfg['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-400 dark:text-gray-500 tabular-nums whitespace-nowrap">
                                    {{ $session->started_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($sessions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $sessions->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
