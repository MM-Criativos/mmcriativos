<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\SdrCallSession;
use App\Models\SdrScript;
use Illuminate\Http\Request;

class SdrCallController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved', 'can.prospeccao']);
    }

    // ── Editor de script (GET) ─────────────────────────────────────────────
    public function editor()
    {
        $script      = SdrScript::active();
        $defaultFlow = $script ? null : $this->defaultFlow();
        return view('admin.commercial.sdr.script-editor', compact('script', 'defaultFlow'));
    }

    // ── Salvar script (POST) ───────────────────────────────────────────────
    public function editorSave(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'flow'    => ['required', 'array'],
        ]);

        SdrScript::where('is_active', true)->update(['is_active' => false]);

        SdrScript::create([
            'name'      => $data['name'],
            'version'   => $data['version'],
            'is_active' => true,
            'flow'      => $data['flow'],
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Iniciar call (POST) ────────────────────────────────────────────────
    public function start(Request $request)
    {
        $data = $request->validate([
            'lead_id'  => ['nullable', 'integer'],
            'sdr_name' => ['required', 'string', 'max:255'],
            'company'  => ['required', 'string', 'max:255'],
            'contact'  => ['nullable', 'string', 'max:255'],
        ]);

        $session = SdrCallSession::create([
            'lead_id'    => $data['lead_id'] ?? null,
            'sdr_name'   => $data['sdr_name'],
            'company'    => $data['company'],
            'contact'    => $data['contact'] ?? null,
            'started_at' => now(),
        ]);

        $script = SdrScript::active();
        $flow   = $script ? $script->flow : $this->defaultFlow();

        return response()->json([
            'session_id' => $session->id,
            'flow'       => $flow,
        ]);
    }

    // ── Encerrar call (POST) ───────────────────────────────────────────────
    public function end(Request $request, int $id)
    {
        $session = SdrCallSession::findOrFail($id);

        $data = $request->validate([
            'path'    => ['nullable', 'array'],
            'outcome' => ['nullable', 'string'],
        ]);

        $allowed = ['reuniao_agendada', 'retorno_agendado', 'sem_interesse', 'encerrado_manualmente'];
        $outcome = in_array($data['outcome'] ?? null, $allowed)
            ? $data['outcome']
            : 'encerrado_manualmente';

        $session->update([
            'ended_at' => now(),
            'path'     => $data['path'] ?? [],
            'outcome'  => $outcome,
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Histórico de calls (GET) ───────────────────────────────────────────
    public function history()
    {
        $sessions = SdrCallSession::latest()->paginate(30);
        return view('admin.commercial.sdr.history', compact('sessions'));
    }

    // ── Dashboard SDR (GET) ────────────────────────────────────────────────
    public function dashboard(Request $request)
    {
        $period        = $request->get('period', 'month');
        $outcomeFilter = $request->get('outcome', '');

        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };

        $base = SdrCallSession::query();
        if ($startDate) {
            $base->where('started_at', '>=', $startDate);
        }

        $totalCalls   = (clone $base)->count();
        $reunioes     = (clone $base)->where('outcome', 'reuniao_agendada')->count();
        $retornos     = (clone $base)->where('outcome', 'retorno_agendado')->count();
        $semInteresse = (clone $base)->where('outcome', 'sem_interesse')->count();
        $conversao    = $totalCalls > 0 ? round(($reunioes / $totalCalls) * 100, 1) : 0;

        $avgSeconds = (clone $base)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at)) as avg_sec')
            ->value('avg_sec');
        $avgDuration = $avgSeconds ? (int) round($avgSeconds) : null;

        $bySdr = (clone $base)
            ->selectRaw("
                sdr_name,
                COUNT(*) as total,
                SUM(CASE WHEN outcome = 'reuniao_agendada' THEN 1 ELSE 0 END) as reunioes,
                SUM(CASE WHEN outcome = 'retorno_agendado' THEN 1 ELSE 0 END) as retornos,
                SUM(CASE WHEN outcome = 'sem_interesse'    THEN 1 ELSE 0 END) as sem_interesse,
                ROUND(AVG(CASE WHEN ended_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, started_at, ended_at) END)) as avg_sec
            ")
            ->groupBy('sdr_name')
            ->orderByDesc('reunioes')
            ->get();

        $sessionsQuery = SdrCallSession::latest('started_at');
        if ($startDate) {
            $sessionsQuery->where('started_at', '>=', $startDate);
        }
        if ($outcomeFilter) {
            $sessionsQuery->where('outcome', $outcomeFilter);
        }
        $sessions = $sessionsQuery->paginate(20)->withQueryString();

        return view('admin.commercial.sdr.dashboard', compact(
            'totalCalls', 'reunioes', 'retornos', 'semInteresse',
            'conversao', 'avgDuration', 'bySdr', 'sessions',
            'period', 'outcomeFilter'
        ));
    }

    // ── Fluxo padrão — MM Cloud v2 ────────────────────────────────────────
    // Posicionamento: resultado de negócio, não produto de tecnologia.
    // Produto = operação de atendimento funcionando no piloto automático.
    // Lead chega. Agendamento sai.
    //
    // Variáveis disponíveis nos scripts:
    //   {contact|fallback}  → nome do tomador de decisão, ou fallback se não informado
    //   {company|fallback}  → nome da empresa, ou fallback se não informado
    //   {sdr_name}          → nome do SDR logado
    private function defaultFlow(): array
    {
        return [
            'startNode' => 'ab_01',
            'nodes' => [

                // ═══════════════════════════════════════════════════════════
                // ABERTURA
                // ═══════════════════════════════════════════════════════════

                'ab_01' => [
                    'id'     => 'ab_01',
                    'stage'  => 'Abertura',
                    'label'  => 'Contato inicial — responsável pelos agendamentos',
                    'script' => 'Oi, tudo bem? Falo com {contact|a pessoa responsável pelos agendamentos}?',
                    'tip'    => 'Tom leve e descontraído. Não comece vendendo. O objetivo é apenas abrir a conversa.',
                    'choices' => [
                        ['label' => 'Confirmou — é a pessoa certa', 'next' => 'ab_02'],
                        ['label' => 'Perguntou do que se trata', 'next' => 'ab_o_que_e'],
                        ['label' => 'Responsável não está disponível', 'next' => 'ab_nao_disponivel'],
                        ['label' => 'Não quer passar o responsável', 'next' => 'ab_nao_passa'],
                    ],
                ],

                'ab_02' => [
                    'id'     => 'ab_02',
                    'stage'  => 'Abertura',
                    'label'  => 'Apresentação + pergunta de abertura',
                    'script' => 'Perfeito. Meu nome é {sdr_name}, sou da MM Cloud. Eu vi o trabalho da {company|clínica de vocês} pelo Instagram — presença bem ativa por lá. Por isso queria te fazer uma pergunta rápida, {contact|}: hoje, quando uma cliente manda mensagem no WhatsApp ou no direct querendo agendar, esse atendimento é feito por alguém da equipe ou vocês já têm algum processo automatizado?',
                    'tip'    => 'Tom de curiosidade genuína, não de vendedor. A pergunta é simples e abre espaço para a dor aparecer naturalmente.',
                    'choices' => [
                        ['label' => 'Fazem tudo manualmente', 'next' => 'qual_01'],
                        ['label' => 'Já usam alguma automação', 'next' => 'qual_sistema'],
                        ['label' => 'Estão ocupados / pressa', 'next' => 'ab_pressa'],
                    ],
                ],

                'ab_o_que_e' => [
                    'id'     => 'ab_o_que_e',
                    'stage'  => 'Abertura',
                    'label'  => 'Explicar brevemente do que se trata (porteira)',
                    'script' => 'Claro. É um contato da MM Cloud sobre uma solução que cuida do atendimento de clientes pelo WhatsApp da {company|empresa} — responde dúvidas, passa as informações certas e organiza os pedidos de agendamento, sem precisar de alguém disponível o tempo todo. Libera muito a equipe de atendimento. Como envolve o fluxo da {company|clínica}, o ideal é conversar com {contact|quem cuida dessa área}.',
                    'tip'    => 'Fale para a secretária como se fosse algo que ajuda ela, não substitui. "Libera a equipe" é o gatilho certo aqui. Objetivo: ser passado para o decisor.',
                    'choices' => [
                        ['label' => 'Passou para o responsável', 'next' => 'ab_02'],
                        ['label' => 'É o responsável mesmo', 'next' => 'qual_01'],
                        ['label' => 'Não tem interesse', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'ab_nao_disponivel' => [
                    'id'     => 'ab_nao_disponivel',
                    'stage'  => 'Abertura',
                    'label'  => 'Responsável não está — agendar retorno',
                    'script' => 'Tudo bem. Qual seria o melhor horário para falar com {contact|a responsável}? E posso confirmar o nome, por gentileza?',
                    'tip'    => 'Anote nome e horário. Confirme o retorno.',
                    'choices' => [
                        ['label' => 'Informou nome e horário', 'next' => 'enc_retorno'],
                        ['label' => 'Não quer informar', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'ab_nao_passa' => [
                    'id'     => 'ab_nao_passa',
                    'stage'  => 'Abertura',
                    'label'  => 'Não quer passar o responsável',
                    'script' => 'Entendo. Deixa eu te contar rapidinho: a solução não é pra substituir o atendimento de vocês — ela resolve aquelas mensagens repetitivas que chegam no WhatsApp o tempo todo, sabe? Preço, horário, o que inclui, disponibilidade. Isso tudo acontece automaticamente, inclusive fora do expediente. A equipe fica livre pra focar no que realmente precisa de atenção. Mas pra entender se faz sentido pra {company|clínica}, preciso conversar com {contact|quem cuida dessa área}. Tem um caminho melhor — WhatsApp, e-mail ou um horário para retornar?',
                    'tip'    => 'Primeiro mostre o benefício para ela, depois peça o decisor. Nunca insista diretamente — ofereça alternativa de canal.',
                    'choices' => [
                        ['label' => 'Sugeriu um canal ou horário', 'next' => 'enc_retorno'],
                        ['label' => 'Recusou completamente', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'ab_pressa' => [
                    'id'     => 'ab_pressa',
                    'stage'  => 'Abertura',
                    'label'  => 'Versão ultra curta — prospect com pressa',
                    'script' => 'Entendo, {contact|vou ser direto}. O MMCloud faz o atendimento da {company|clínica} funcionar sem precisar de ninguém disponível: a cliente manda mensagem, tira as dúvidas, e sai com horário marcado — a qualquer hora do dia. Posso te mostrar isso em 15 minutos? Amanhã de manhã ou à tarde?',
                    'tip'    => 'Ultra curto. Resultado primeiro, tecnologia nunca. Vá direto para o fechamento com duas opções de horário.',
                    'choices' => [
                        ['label' => 'Aceitou a reunião', 'next' => 'enc_reuniao'],
                        ['label' => 'Pediu para ligar depois', 'next' => 'enc_retorno'],
                        ['label' => 'Recusou', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                // ═══════════════════════════════════════════════════════════
                // QUALIFICAÇÃO
                // ═══════════════════════════════════════════════════════════

                'qual_01' => [
                    'id'     => 'qual_01',
                    'stage'  => 'Qualificação',
                    'label'  => 'Pergunta principal de qualificação',
                    'script' => 'Hoje a {company|clínica} consegue responder todos os contatos com rapidez e manter um padrão no atendimento, ou em alguns momentos acaba ficando corrido?',
                    'tip'    => 'Escute mais do que fala. Anote o que ele disser. Esta pergunta abre os cenários mais ricos.',
                    'choices' => [
                        ['label' => 'Às vezes demora / fica corrido', 'next' => 'qual_demora'],
                        ['label' => 'Perdem leads / mensagens sem resposta', 'next' => 'qual_perde_lead'],
                        ['label' => 'Diz que está tudo bem', 'next' => 'qual_tudo_bem'],
                        ['label' => 'Resposta vaga: "a gente responde normal"', 'next' => 'qual_vago'],
                    ],
                ],

                'qual_sistema' => [
                    'id'     => 'qual_sistema',
                    'stage'  => 'Qualificação',
                    'label'  => 'Já usa alguma automação — entender o cenário',
                    'script' => 'Que sistema a {company|clínica} usa? Estão satisfeitos com ele?',
                    'tip'    => 'Não fale mal do concorrente. Pergunte o que falta.',
                    'choices' => [
                        ['label' => 'Satisfeitos com o atual', 'next' => 'obj_ja_tem_sistema'],
                        ['label' => 'Insatisfeitos / algo falta', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_demora' => [
                    'id'     => 'qual_demora',
                    'stage'  => 'Qualificação',
                    'label'  => 'Admite demora ou correria',
                    'script' => 'Faz total sentido. Isso acontece muito — a equipe está atendendo presencialmente e, ao mesmo tempo, precisa responder WhatsApp, direct, confirmar horário, organizar agenda. O problema é que, nesse intervalo, algumas oportunidades simplesmente somem.',
                    'tip'    => 'Pausa após "somem". Deixe o ponto pousar. Não complete a frase por ele.',
                    'choices' => [
                        ['label' => 'Concordou — seguir para pitch', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_perde_lead' => [
                    'id'     => 'qual_perde_lead',
                    'stage'  => 'Qualificação',
                    'label'  => 'Confirmou que perde leads',
                    'script' => 'Entendi. Esse é exatamente o cenário em que nossa solução gera resultado mais rápido. Porque não é só responder mensagem — é conduzir a cliente do primeiro contato até o agendamento confirmado, com padrão e velocidade, sem depender de alguém disponível pra isso.',
                    'tip'    => 'Lead muito qualificado. Vá para o pitch com confiança e sem hesitar.',
                    'choices' => [
                        ['label' => 'Demonstrou interesse — seguir', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_tudo_bem' => [
                    'id'     => 'qual_tudo_bem',
                    'stage'  => 'Qualificação',
                    'label'  => 'Diz que está tudo bem',
                    'script' => 'Ótimo, isso é um bom sinal. Quem já tem o atendimento organizado costuma ganhar ainda mais escala quando automatiza o que é repetitivo. Deixa eu te perguntar uma coisa: hoje a {company|clínica} sabe quantos contatos chegam por semana e quantos efetivamente viram agendamento confirmado?',
                    'tip'    => 'Plante a semente da métrica. Quem não mede não sabe onde está perdendo.',
                    'choices' => [
                        ['label' => 'Não tem essa métrica', 'next' => 'pitch_01'],
                        ['label' => 'Tem e está bem', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_vago' => [
                    'id'     => 'qual_vago',
                    'stage'  => 'Qualificação',
                    'label'  => 'Resposta vaga — aprofundar',
                    'script' => 'Perfeito. E quando você diz "a gente responde normal" — a {company|clínica} consegue acompanhar todos os contatos até o agendamento confirmado, ou tem mensagem que acaba ficando pelo caminho?',
                    'tip'    => 'Não aceite "está tudo bem" sem aprofundar. A maioria não percebe que perde lead.',
                    'choices' => [
                        ['label' => 'Admitiu que às vezes perde', 'next' => 'qual_demora'],
                        ['label' => 'Insiste que está tudo bem', 'next' => 'qual_tudo_bem'],
                    ],
                ],

                // ═══════════════════════════════════════════════════════════
                // PITCH
                // ═══════════════════════════════════════════════════════════

                'pitch_01' => [
                    'id'     => 'pitch_01',
                    'stage'  => 'Pitch',
                    'label'  => 'Apresentação da solução — resultado de negócio',
                    'script' => 'A MM Cloud criou uma solução que coloca o atendimento da {company|clínica} no piloto automático. Na prática: a cliente manda mensagem, recebe atendimento humanizado, tira as dúvidas, e o que sai do outro lado é um agendamento confirmado. Isso acontece 24 horas por dia, no tom e nas regras da {company|empresa} — sem precisar de ninguém disponível pra isso.',
                    'tip'    => 'Pause após "sem precisar de ninguém disponível pra isso." Nunca diga "plataforma", "IA" ou "automação" neste momento.',
                    'choices' => [
                        ['label' => 'Interessante, como funciona?', 'next' => 'pitch_detalhe'],
                        ['label' => 'Mas vai ficar robótico / impessoal', 'next' => 'obj_robotico'],
                        ['label' => 'Já temos recepcionista', 'next' => 'obj_recepcionista'],
                        ['label' => 'Quanto custa?', 'next' => 'obj_preco'],
                        ['label' => 'Me manda mais informações', 'next' => 'obj_info'],
                    ],
                ],

                'pitch_detalhe' => [
                    'id'     => 'pitch_detalhe',
                    'stage'  => 'Pitch',
                    'label'  => 'Detalhar + gatilho de curiosidade',
                    'script' => 'O que surpreende quem vê funcionando é que não parece automação. A solução conhece os serviços da {company|empresa}, os profissionais, os horários, as regras — e usa tudo isso para conversar com a cliente como se fosse alguém da equipe. O que mudou para os nossos clientes não foi o volume de mensagens que chegam: foi o volume de agendamentos que saem confirmados. Porque antes, muita coisa ficava pelo caminho — uma dúvida sem resposta, uma mensagem tarde demais, uma conversa que esfriou.',
                    'tip'    => 'Não venda tecnologia. Venda o que o empresário deixou de perder. "Agendamentos que saem confirmados" é o frame certo.',
                    'choices' => [
                        ['label' => 'Ficou curioso — seguir', 'next' => 'pitch_impacto'],
                        ['label' => 'Ainda tem dúvida sobre robótico', 'next' => 'obj_robotico'],
                    ],
                ],

                'pitch_impacto' => [
                    'id'     => 'pitch_impacto',
                    'stage'  => 'Pitch',
                    'label'  => 'Pergunta de impacto — 21h',
                    'script' => 'Hoje, se uma cliente chama a {company|clínica} às 21h ou no domingo querendo agendar, o que acontece com esse contato?',
                    'tip'    => 'Essa pergunta faz o prospect visualizar a perda. Deixe o silêncio trabalhar — não preencha a resposta por ele.',
                    'choices' => [
                        ['label' => 'Só vê no próximo expediente', 'next' => 'pitch_reforco_perda'],
                        ['label' => 'Alguém responde mesmo fora do horário', 'next' => 'pitch_reforco_cansativo'],
                    ],
                ],

                'pitch_reforco_perda' => [
                    'id'     => 'pitch_reforco_perda',
                    'stage'  => 'Pitch',
                    'label'  => 'Reforço — perde cliente sem perceber',
                    'script' => 'Exatamente. E esse é um ponto em que a maioria perde receita sem perceber — porque a cliente interessada nem sempre espera. Ela chama às 22h, quer saber preço e disponibilidade da {company|clínica}, e se não recebe resposta, ela vai para o próximo. Com a nossa solução, ela é atendida na hora, tira as dúvidas, e o agendamento já sai confirmado.',
                    'tip'    => 'Não exagere. A lógica fala por si. Deixe ele conectar os pontos.',
                    'choices' => [
                        ['label' => 'Concordou — ir para fechamento', 'next' => 'fecha_01'],
                        ['label' => 'Ainda tem objeções', 'next' => 'obj_preco'],
                    ],
                ],

                'pitch_reforco_cansativo' => [
                    'id'     => 'pitch_reforco_cansativo',
                    'stage'  => 'Pitch',
                    'label'  => 'Reforço — equipe não precisa estar disponível 24h',
                    'script' => 'Ótimo, isso mostra cuidado com o atendimento. A questão é: isso depende de alguém da {company|equipe} estar disponível o tempo todo, certo? A nossa solução mantém esse padrão de resposta sem esse peso — a equipe descansa, o atendimento não para.',
                    'tip'    => 'Valorize o esforço deles antes de mostrar o ganho. Nunca diminua o que eles já fazem.',
                    'choices' => [
                        ['label' => 'Fez sentido — ir para fechamento', 'next' => 'fecha_01'],
                        ['label' => 'Ainda quer entender mais', 'next' => 'pitch_detalhe'],
                    ],
                ],

                // ═══════════════════════════════════════════════════════════
                // OBJEÇÃO
                // ═══════════════════════════════════════════════════════════

                'obj_robotico' => [
                    'id'     => 'obj_robotico',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: medo de ser robótico / impessoal',
                    'script' => 'Essa preocupação é muito legítima — e é exatamente por isso que a nossa solução não tem menu, não tem número pra digitar, não tem resposta engessada. Ela é configurada com as informações reais da {company|empresa}: os serviços, os preços, os profissionais, o tom de voz da marca. A cliente conversa normalmente — o que ela percebe é que foi atendida rápido e bem. Não que foi atendida por uma máquina.',
                    'tip'    => 'Valide a preocupação primeiro. "Essa preocupação é muito legítima" abre o ouvido antes de responder.',
                    'choices' => [
                        ['label' => 'Entendeu — quer ver funcionando', 'next' => 'fecha_01'],
                        ['label' => 'Ainda cético', 'next' => 'fecha_01'],
                    ],
                ],

                'obj_recepcionista' => [
                    'id'     => 'obj_recepcionista',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: já tem recepcionista',
                    'script' => 'Perfeito — e a proposta não é substituir quem já atende na {company|clínica}. É o oposto: tirar da equipe o que é repetitivo e liberar ela pra focar no que só ela pode fazer. Sabe aquelas mensagens de preço, horário, o que inclui o serviço, confirmação de agenda — que chegam repetindo o dia inteiro, inclusive fora do horário? A solução resolve isso automaticamente. A recepcionista ganha tempo, não perde espaço.',
                    'tip'    => 'Nunca posicione contra a recepcionista. Ela provavelmente está do outro lado da linha. "Ganha tempo, não perde espaço" é o frame exato.',
                    'choices' => [
                        ['label' => 'Entendeu — quer ver', 'next' => 'fecha_01'],
                        ['label' => 'Ainda resistente', 'next' => 'obj_sem_tempo'],
                    ],
                ],

                'obj_ja_tem_sistema' => [
                    'id'     => 'obj_ja_tem_sistema',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: já tem sistema de agenda',
                    'script' => 'Ótimo — e isso, na verdade, facilita. A diferença é que a maioria dos sistemas organiza a agenda depois que o agendamento já aconteceu. A nossa solução atua antes: no atendimento, na resposta rápida, na condução da cliente até o momento em que ela confirma o horário. É o que acontece entre o primeiro "oi" e o agendamento entrar na agenda da {company|clínica}.',
                    'tip'    => 'Antes do agendamento vs. depois do agendamento — essa é a diferença. Não concorra com o sistema deles, complemente.',
                    'choices' => [
                        ['label' => 'Fez sentido — quer ver', 'next' => 'fecha_01'],
                        ['label' => 'Não quer trocar o processo atual', 'next' => 'obj_nao_trocar'],
                    ],
                ],

                'obj_nao_trocar' => [
                    'id'     => 'obj_nao_trocar',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: não quer trocar sistema atual',
                    'script' => 'Faz sentido. E a ideia não é trocar o que a {company|clínica} já usa. Primeiro, a gente entende o fluxo atual e identifica onde a solução pode entrar para melhorar atendimento e conversão — sem mexer no que já funciona.',
                    'tip'    => 'Não force. Convide para olhar sem compromisso. "Sem mexer no que já funciona" reduz a resistência.',
                    'choices' => [
                        ['label' => 'Aceitou olhar sem compromisso', 'next' => 'fecha_01'],
                        ['label' => 'Recusou', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'obj_preco' => [
                    'id'     => 'obj_preco',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: quanto custa?',
                    'script' => 'O valor depende do volume de atendimento da {company|clínica}, por isso prefiro não passar algo genérico sem entender o cenário de vocês. Mas a lógica é simples: em muitos casos, o custo de alguns agendamentos perdidos por mês já supera o investimento na solução. Por isso prefiro te mostrar o que ela faz primeiro, e depois o número faz sentido dentro do contexto.',
                    'tip'    => 'Não dê preço sem contexto. Redirecione para a reunião onde o valor fica claro dentro do cenário deles.',
                    'choices' => [
                        ['label' => 'Aceitou marcar a reunião', 'next' => 'fecha_01'],
                        ['label' => 'Insistiu em saber o valor agora', 'next' => 'fecha_01'],
                        ['label' => 'Sem interesse pelo preço', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'obj_info' => [
                    'id'     => 'obj_info',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: me manda informações',
                    'script' => 'Claro, te mando. E junto com o material, prefiro te enviar uma demonstração prática — porque só texto não mostra o que acontece quando a cliente manda mensagem e sai com o horário confirmado. Mas pra fazer sentido para a realidade da {company|clínica}, o ideal é uma conversa rápida de 15 minutos. Podemos encaixar amanhã, {contact|}?',
                    'tip'    => 'Material é a ponte, não o destino. Use como argumento para a reunião, não como substituto.',
                    'choices' => [
                        ['label' => 'Aceitou a reunião junto com o material', 'next' => 'enc_reuniao'],
                        ['label' => 'Insistiu em só receber material', 'next' => 'enc_retorno'],
                        ['label' => 'Sem interesse', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'obj_sem_tempo' => [
                    'id'     => 'obj_sem_tempo',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: sem tempo para implementar',
                    'script' => 'Justamente por isso pode fazer sentido olhar agora. Quando a operação está corrida, normalmente é porque a equipe está absorvendo tarefas repetitivas demais. A solução é pensada pra entrar no fluxo da {company|clínica} sem criar mais trabalho — ela assume o que é repetitivo pra liberar quem precisa focar no que importa.',
                    'tip'    => '"Correria" é sinal de que precisam da solução, não motivo para recusar. Vire o argumento sem confrontar.',
                    'choices' => [
                        ['label' => 'Fez sentido — quer marcar', 'next' => 'fecha_01'],
                        ['label' => 'Realmente sem disponibilidade agora', 'next' => 'enc_retorno'],
                    ],
                ],

                // ═══════════════════════════════════════════════════════════
                // FECHAMENTO
                // ═══════════════════════════════════════════════════════════

                'fecha_01' => [
                    'id'     => 'fecha_01',
                    'stage'  => 'Fechamento',
                    'label'  => 'Convite para reunião de 15-20 min',
                    'script' => 'Para não tomar mais tempo agora, o ideal seria uma conversa rápida de 15 a 20 minutos — pra eu entender como funciona o atendimento da {company|clínica} e mostrar como a solução operaria no dia a dia de vocês. Tenho dois horários disponíveis: amanhã às [HORÁRIO] ou [HORÁRIO]. Qual fica melhor para {contact|você}?',
                    'tip'    => 'Sempre duas opções de horário. Nunca "quando você pode?" — isso muda a pergunta de sim/não para qual dos dois.',
                    'choices' => [
                        ['label' => 'Agendou a reunião', 'next' => 'enc_reuniao'],
                        ['label' => 'Quer pensar / precisa consultar sócio', 'next' => 'fecha_urgencia'],
                        ['label' => 'Não quer agora', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'fecha_urgencia' => [
                    'id'     => 'fecha_urgencia',
                    'stage'  => 'Fechamento',
                    'label'  => 'Gatilho de urgência para fechar',
                    'script' => 'Entendo, {contact|}. Só um detalhe: estamos abrindo poucas demonstrações por semana porque cada apresentação é montada para o tipo de negócio — não é uma demo genérica. Por isso queria reservar um horário para a {company|clínica} ainda essa semana. Posso deixar confirmado?',
                    'tip'    => 'Urgência real supera urgência artificial. Use só se tiver base verdadeira. O frame "não é demo genérica" reforça o valor da reunião.',
                    'choices' => [
                        ['label' => 'Aceitou o horário', 'next' => 'enc_reuniao'],
                        ['label' => 'Preferiu retorno na semana seguinte', 'next' => 'enc_retorno'],
                        ['label' => 'Recusou', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                // ═══════════════════════════════════════════════════════════
                // ENCERRADO
                // ═══════════════════════════════════════════════════════════

                'enc_reuniao' => [
                    'id'      => 'enc_reuniao',
                    'stage'   => 'Encerrado',
                    'label'   => 'Reunião agendada ✓',
                    'script'  => '',
                    'tip'     => 'Envie a confirmação via WhatsApp imediatamente após encerrar a call. Confirme data, horário e link da reunião.',
                    'outcome' => 'reuniao_agendada',
                    'choices' => [],
                ],

                'enc_retorno' => [
                    'id'      => 'enc_retorno',
                    'stage'   => 'Encerrado',
                    'label'   => 'Retorno agendado',
                    'script'  => '',
                    'tip'     => 'Envie a mensagem pós-call e marque o follow-up. Registre o motivo e o que foi dito.',
                    'outcome' => 'retorno_agendado',
                    'choices' => [],
                ],

                'enc_sem_interesse' => [
                    'id'      => 'enc_sem_interesse',
                    'stage'   => 'Encerrado',
                    'label'   => 'Sem interesse',
                    'script'  => '',
                    'tip'     => 'Registre o motivo nas notas. Pode reentrar na cadência em 30-60 dias com novo ângulo.',
                    'outcome' => 'sem_interesse',
                    'choices' => [],
                ],
            ],
        ];
    }
}
