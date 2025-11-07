<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetEvent;
use App\Models\BudgetItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\ExchangeRateService;

class KpiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
        $this->middleware(function ($request, $next) {
            if (auth()->user()?->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        /** @var ExchangeRateService $fx */
        $fx = app(ExchangeRateService::class);

        $total = Budget::count();
        $sent = Budget::where('status', 'sent')->count();
        $accepted = Budget::where('status', 'accepted')->count();
        $declined = Budget::where('status', 'declined')->count();
        $draft = Budget::where('status', 'draft')->count();
        $expired = Budget::where('status', 'expired')->count();
        $opened = Budget::where('status', 'opened')->count();

        $conversionRate = $total > 0 ? round(($accepted / $total) * 100, 1) : 0.0;

        $approvedRevenueTotal = (float) Budget::where('status', 'accepted')
            ->get(['currency','total_one_time'])
            ->sum(function($b) use ($fx){ return $fx->toBRL((float)$b->total_one_time, $b->currency); });

        // Pipeline (valor) por status
        $pipelineSentValue = (float) Budget::where('status', 'sent')->get(['currency','total_one_time'])->sum(fn($b)=>$fx->toBRL((float)$b->total_one_time,$b->currency));
        $pipelineOpenedValue = (float) Budget::where('status', 'opened')->get(['currency','total_one_time'])->sum(fn($b)=>$fx->toBRL((float)$b->total_one_time,$b->currency));

        // Receita média aprovada por cliente (considera apenas clientes distintos que aceitaram)
        $acceptedSum = (float) Budget::where('status', 'accepted')->get(['currency','total_one_time'])
            ->sum(fn($b)=>$fx->toBRL((float)$b->total_one_time,$b->currency));
        $acceptedClients = Budget::where('status', 'accepted')->whereNotNull('client_id')->distinct('client_id')->count('client_id');
        $avgRevenuePerClient = $acceptedClients > 0 ? round($acceptedSum / $acceptedClients, 2) : 0.0;

        // Ticket médio aprovado (por orçamento)
        $ticketApprovedAvg = $accepted > 0 ? round($acceptedSum / $accepted, 2) : 0.0;

        // Receita recorrente aprovada (itens monthly/yearly de budgets accepted)
        $acceptedBudgetsMap = Budget::where('status', 'accepted')->get(['id','currency'])->keyBy('id');
        $acceptedIds = $acceptedBudgetsMap->keys();
        $recurringMonthlyTotal = (float) BudgetItem::whereIn('budget_id', $acceptedIds)
            ->where('billing_period', 'monthly')->get(['budget_id','total'])
            ->sum(function($it) use ($acceptedBudgetsMap, $fx){
                $curr = optional($acceptedBudgetsMap->get($it->budget_id))->currency ?? 'BRL';
                return $fx->toBRL((float)$it->total, $curr);
            });
        $recurringYearlyTotal = (float) BudgetItem::whereIn('budget_id', $acceptedIds)
            ->where('billing_period', 'yearly')->get(['budget_id','total'])
            ->sum(function($it) use ($acceptedBudgetsMap, $fx){
                $curr = optional($acceptedBudgetsMap->get($it->budget_id))->currency ?? 'BRL';
                return $fx->toBRL((float)$it->total, $curr);
            });

        // Serviço mais vendido (por accepted). Usa service direto ou via plano
        $topService = null;
        $serviceCounter = [];
        $acceptedBudgets = Budget::with(['service:id,name', 'plan.service:id,name'])
            ->where('status', 'accepted')->get();
        foreach ($acceptedBudgets as $b) {
            $serviceName = $b->service->name ?? ($b->plan->service->name ?? null);
            if ($serviceName) {
                $serviceCounter[$serviceName] = ($serviceCounter[$serviceName] ?? 0) + 1;
            }
        }
        if (!empty($serviceCounter)) {
            arsort($serviceCounter);
            $topService = array_key_first($serviceCounter);
        }

        // Tempo médio de aprovação (horas) = média de (accepted_event.created_at - budgets.created_at)
        $acceptedEvents = BudgetEvent::with('budget:id,created_at')
            ->where('event', 'accepted')
            ->get();
        $totalHours = 0.0; $countEvents = 0;
        foreach ($acceptedEvents as $ev) {
            $b = $ev->budget;
            if ($b) {
                $diff = $b->created_at ? $b->created_at->diffInHours($ev->created_at) : null;
                if ($diff !== null) {
                    $totalHours += $diff; $countEvents++;
                }
            }
        }
        $avgApprovalHours = $countEvents > 0 ? round($totalHours / $countEvents, 1) : 0.0;

        $openNotAccepted = $opened; // já contado acima

        // Taxa de abertura (ever): distintos abertos / distintos enviados
        $sentEver = BudgetEvent::where('event', 'sent')->distinct('budget_id')->count('budget_id');
        $openedEver = BudgetEvent::where('event', 'opened')->distinct('budget_id')->count('budget_id');
        $openRate = $sentEver > 0 ? round(($openedEver / $sentEver) * 100, 1) : 0.0;

        // Tempo médio até primeira abertura (horas) baseado em eventos
        $sentFirst = BudgetEvent::select('budget_id', DB::raw('MIN(created_at) as sent_at'))
            ->where('event', 'sent')->groupBy('budget_id')->pluck('sent_at', 'budget_id');
        $openedFirst = BudgetEvent::select('budget_id', DB::raw('MIN(created_at) as opened_at'))
            ->where('event', 'opened')->groupBy('budget_id')->pluck('opened_at', 'budget_id');
        $sumOpenHours = 0.0; $countOpen = 0;
        foreach ($openedFirst as $bid => $openedAt) {
            if (isset($sentFirst[$bid])) {
                $sentAt = Carbon::parse($sentFirst[$bid]);
                $openAt = Carbon::parse($openedAt);
                $sumOpenHours += $sentAt->diffInHours($openAt);
                $countOpen++;
            }
        }
        $avgHoursToOpen = $countOpen > 0 ? round($sumOpenHours / $countOpen, 1) : 0.0;

        // Séries mensais (últimos 12 meses)
        $months = collect(range(0, 11))->reverse()->map(function ($i) {
            return now()->subMonths($i)->startOfMonth();
        });

        $labels = [];
        $seriesCreated = [];
        $seriesAccepted = [];
        $seriesRevenue = [];

        $seriesSent = [];
        $seriesOpened = [];
        foreach ($months as $start) {
            $end = (clone $start)->endOfMonth();
            $labels[] = $start->format('m/Y');
            $seriesCreated[] = Budget::whereBetween('created_at', [$start, $end])->count();
            // Contar orçamentos distintos por mês para evitar múltiplas aberturas/envios
            $seriesSent[] = BudgetEvent::where('event', 'sent')
                ->whereBetween('created_at', [$start, $end])
                ->distinct('budget_id')->count('budget_id');
            $seriesOpened[] = BudgetEvent::where('event', 'opened')
                ->whereBetween('created_at', [$start, $end])
                ->distinct('budget_id')->count('budget_id');
            $acceptedInMonth = BudgetEvent::where('event', 'accepted')
                ->whereBetween('created_at', [$start, $end])
                ->distinct('budget_id')->count('budget_id');
            $seriesAccepted[] = $acceptedInMonth;
            // Receita aprovada no mês (data do evento accepted)
            $acceptedEvents = BudgetEvent::with(['budget' => function($q){
                    $q->with('selectedPayment');
                }])
                ->where('event', 'accepted')
                ->whereBetween('created_at', [$start, $end])
                ->get();
            $monthlyRevenue = 0.0;
            foreach ($acceptedEvents as $ev) {
                $b = $ev->budget;
                if (!$b) { continue; }
                $amount = $b->selectedPayment?->total_with_interest ?? (float) $b->total_one_time;
                $monthlyRevenue += $fx->toBRL((float) $amount, $b->currency ?? 'BRL');
            }
            $seriesRevenue[] = $monthlyRevenue;
        }

        return view('admin.commercial.kpi.index', [
            'cards' => [
                'total' => $total,
                'sent' => $sent,
                'accepted' => $accepted,
                'declined' => $declined,
                'draft' => $draft,
                'expired' => $expired,
                'opened' => $opened,
                'conversion' => $conversionRate,
                'approved_revenue_total' => $approvedRevenueTotal,
                'avg_revenue_per_client' => $avgRevenuePerClient,
                'ticket_approved_avg' => $ticketApprovedAvg,
                'pipeline_sent_value' => $pipelineSentValue,
                'pipeline_opened_value' => $pipelineOpenedValue,
                'recurring_monthly_total' => $recurringMonthlyTotal,
                'recurring_yearly_total' => $recurringYearlyTotal,
                'open_rate' => $openRate,
                'avg_hours_to_open' => $avgHoursToOpen,
                'top_service' => $topService,
                'avg_approval_hours' => $avgApprovalHours,
                'open_not_accepted' => $openNotAccepted,
            ],
            'chart' => [
                'labels' => $labels,
                'created' => $seriesCreated,
                'sent' => $seriesSent,
                'opened' => $seriesOpened,
                'accepted' => $seriesAccepted,
                'revenue' => $seriesRevenue,
            ],
            'service_conversion' => $this->serviceConversionSnapshot(),
        ]);
    }

    protected function serviceConversionSnapshot(): array
    {
        $data = [];
        $totals = [];
        $acceptedTotals = [];
        $budgets = Budget::with(['service:id,name', 'plan.service:id,name'])->get();
        foreach ($budgets as $b) {
            $serviceName = $b->service->name ?? ($b->plan->service->name ?? null);
            if (!$serviceName) { continue; }
            $totals[$serviceName] = ($totals[$serviceName] ?? 0) + 1;
            if ($b->status === 'accepted') {
                $acceptedTotals[$serviceName] = ($acceptedTotals[$serviceName] ?? 0) + 1;
            }
        }
        foreach ($totals as $name => $total) {
            $acc = $acceptedTotals[$name] ?? 0;
            $rate = $total > 0 ? round(($acc / $total) * 100, 1) : 0.0;
            $data[] = ['service' => $name, 'accepted' => $acc, 'total' => $total, 'rate' => $rate];
        }
        // Top 5 por aceitos
        usort($data, function ($a, $b) {
            if ($a['accepted'] === $b['accepted']) { return $b['rate'] <=> $a['rate']; }
            return $b['accepted'] <=> $a['accepted'];
        });
        return array_slice($data, 0, 5);
    }
}
