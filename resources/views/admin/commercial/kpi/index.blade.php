@php
    $title = 'KPIs';
    $subTitle = 'Indicadores de desempenho comercial';
@endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="grid grid-cols-1">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Total</div>
                    <div class="text-2xl font-bold">{{ $cards['total'] }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Enviados</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $cards['sent'] }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Aprovados</div>
                    <div class="text-2xl font-bold text-green-600">{{ $cards['accepted'] }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Recusados</div>
                    <div class="text-2xl font-bold text-red-600">{{ $cards['declined'] }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Pendentes</div>
                    <div class="text-2xl font-bold text-gray-600">{{ $cards['draft'] }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Expirados</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $cards['expired'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Taxa de conversão</div>
                    <div class="text-3xl font-bold">{{ $cards['conversion'] }}%</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Receita total aprovada</div>
                    <div class="text-3xl font-bold">R$
                        {{ number_format($cards['approved_revenue_total'], 2, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Receita média por cliente</div>
                    <div class="text-3xl font-bold">R$
                        {{ number_format($cards['avg_revenue_per_client'], 2, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Serviço mais vendido</div>
                    <div class="text-2xl font-bold">{{ $cards['top_service'] ?? '—' }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Tempo médio de aprovação</div>
                    <div class="text-2xl font-bold">{{ $cards['avg_approval_hours'] }} h</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Abertos não aceitos</div>
                    <div class="text-2xl font-bold">{{ $cards['open_not_accepted'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Ticket médio aprovado</div>
                    <div class="text-3xl font-bold">R$ {{ number_format($cards['ticket_approved_avg'], 2, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Pipeline (enviados)</div>
                    <div class="text-3xl font-bold">R$ {{ number_format($cards['pipeline_sent_value'], 2, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Pipeline (abertos)</div>
                    <div class="text-3xl font-bold">R$
                        {{ number_format($cards['pipeline_opened_value'], 2, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Recorrente aprovado (mês)</div>
                    <div class="text-3xl font-bold">R$
                        {{ number_format($cards['recurring_monthly_total'], 2, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Recorrente aprovado (ano)</div>
                    <div class="text-3xl font-bold">R$
                        {{ number_format($cards['recurring_yearly_total'], 2, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Taxa de abertura</div>
                    <div class="text-3xl font-bold">{{ $cards['open_rate'] }}%</div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-500 text-sm">Média até primeira abertura</div>
                    <div class="text-3xl font-bold">{{ $cards['avg_hours_to_open'] }} h</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-700 font-semibold mb-2">Evolução mensal (quantidades)</div>
                    <div id="kpiCountsChart" class="w-full"></div>
                </div>
                <div class="bg-white dark:bg-black p-4 rounded shadow">
                    <div class="text-gray-700 font-semibold mb-2">Receita mensal (R$ mil)</div>
                    <div id="kpiRevenueChart" class="w-full"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const labels = @json($chart['labels']);
        const created = @json($chart['created']);
        const sent = @json($chart['sent']);
        const opened = @json($chart['opened']);
        const accepted = @json($chart['accepted']);
        const revenue = @json($chart['revenue']); // valores em BRL

        function renderCharts() {
            // Detecta dark mode
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? '#4b5563' : '#e5e7eb';
            const themeMode = isDark ? 'dark' : 'light';

            // Remove instâncias anteriores
            document.querySelectorAll(".apexcharts-canvas").forEach(e => e.remove());

            // Chart 1: Quantidades
            const countsOptions = {
                chart: {
                    type: 'line',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    foreColor: textColor,
                    background: 'transparent',
                },
                theme: {
                    mode: themeMode
                },
                stroke: {
                    width: [2, 2, 2, 3],
                    curve: 'smooth'
                },
                series: [{
                        name: 'Criados',
                        data: created
                    },
                    {
                        name: 'Enviados',
                        data: sent
                    },
                    {
                        name: 'Abertos',
                        data: opened
                    },
                    {
                        name: 'Aprovados',
                        data: accepted
                    },
                ],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            colors: textColor
                        }
                    },
                    axisBorder: {
                        color: gridColor
                    },
                    axisTicks: {
                        color: gridColor
                    },
                },
                yaxis: {
                    title: {
                        text: 'Qtd',
                        style: {
                            color: textColor
                        }
                    },
                    labels: {
                        style: {
                            colors: textColor
                        }
                    },
                    min: 0,
                },
                legend: {
                    labels: {
                        colors: textColor
                    }
                },
                grid: {
                    borderColor: gridColor
                },
                colors: ['#64748b', '#3b82f6', '#f97316', '#16a34a'],
            };
            new ApexCharts(document.querySelector('#kpiCountsChart'), countsOptions).render();

            // Chart 2: Receita (R$ mil)
            const revenueK = revenue.map(v => v / 1000);
            const maxK = Math.max(0, ...revenueK);
            const thresholds = [10, 20, 50, 100, 200];
            const yMaxK = thresholds.find(t => maxK <= t) || 200;

            const revenueOptions = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    foreColor: textColor,
                    background: 'transparent',
                },
                theme: {
                    mode: themeMode
                },
                series: [{
                    name: 'Receita (R$ mil)',
                    data: revenueK.map(v => Number(v.toFixed(2)))
                }],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            colors: textColor
                        }
                    },
                    axisBorder: {
                        color: gridColor
                    },
                    axisTicks: {
                        color: gridColor
                    },
                },
                yaxis: {
                    title: {
                        text: 'R$ mil',
                        style: {
                            color: textColor
                        }
                    },
                    labels: {
                        style: {
                            colors: textColor
                        }
                    },
                    min: 0,
                    max: yMaxK,
                    labels: {
                        formatter: val => `${val}`
                    },
                },
                legend: {
                    labels: {
                        colors: textColor
                    }
                },
                grid: {
                    borderColor: gridColor
                },
                colors: ['#f59e0b'],
            };
            new ApexCharts(document.querySelector('#kpiRevenueChart'), revenueOptions).render();
        }

        // Render inicial
        renderCharts();

        // 🔁 Detecta mudança de tema (Tailwind)
        const observer = new MutationObserver(() => {
            renderCharts();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    </script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-black p-4 rounded shadow">
                <div class="text-gray-700 font-semibold mb-2">Conversão por serviço (Top 5)</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-left text-gray-600 text-sm border-b">
                                <th class="py-2 pr-4">Serviço</th>
                                <th class="py-2 pr-4">Aprovados</th>
                                <th class="py-2 pr-4">Total</th>
                                <th class="py-2 pr-4">Conversão</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($service_conversion as $row)
                                <tr class="border-b">
                                    <td class="py-3 pr-4">{{ $row['service'] }}</td>
                                    <td class="py-3 pr-4">{{ $row['accepted'] }}</td>
                                    <td class="py-3 pr-4">{{ $row['total'] }}</td>
                                    <td class="py-3 pr-4">{{ $row['rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-3 pr-4 text-gray-600" colspan="4">Sem dados suficientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
