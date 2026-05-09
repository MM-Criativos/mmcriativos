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

    // ── POST /admin/commercial/sdr/call/start ──────────────────────────────
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

    // ── POST /admin/commercial/sdr/call/{id}/end ───────────────────────────
    public function end(Request $request, int $id)
    {
        $session = SdrCallSession::findOrFail($id);

        $data = $request->validate([
            'path'    => ['nullable', 'array'],
            'outcome' => ['nullable', 'string'],
        ]);

        $allowedOutcomes = ['reuniao_agendada','retorno_agendado','sem_interesse','encerrado_manualmente'];
        $outcome = in_array($data['outcome'] ?? null, $allowedOutcomes)
            ? $data['outcome']
            : 'encerrado_manualmente';

        $session->update([
            'ended_at' => now(),
            'path'     => $data['path'] ?? [],
            'outcome'  => $outcome,
        ]);

        return response()->json(['ok' => true]);
    }

    // ── GET /admin/commercial/sdr/call/history ─────────────────────────────
    public function history(Request $request)
    {
        $sessions = SdrCallSession::latest()
            ->paginate(30);

        return view('admin.commercial.sdr.history', compact('sessions'));
    }

    // ── Default 20-node decision tree ─────────────────────────────────────
    private function defaultFlow(): array
    {
        return [
            'startNode' => 'ab_01',
            'nodes' => [

                // ── ABERTURA ─────────────────────────────────────────────
                'ab_01' => [
                    'id'    => 'ab_01',
                    'stage' => 'Abertura',
                    'label' => 'Apresentação inicial',
                    'script'=> 'Olá, posso falar com {contact}? Aqui é {sdr_name} da MM Criativos — desenvolvemos o MM Cloud, um sistema de agendamento com IA para clínicas e salões de beleza. Tenho 2 minutinhos?',
                    'tip'   => 'Fale o nome do decisor com confiança. Tom descontraído, não robótico.',
                    'choices' => [
                        ['label' => 'Sim, pode falar', 'next' => 'ab_02'],
                        ['label' => 'Estou ocupado(a) agora', 'next' => 'ab_retorno'],
                        ['label' => 'Não tenho interesse', 'next' => 'enc_sem_interesse'],
                    ],
                ],
                'ab_02' => [
                    'id'    => 'ab_02',
                    'stage' => 'Abertura',
                    'label' => 'Ganchos iniciais',
                    'script'=> 'Vi o perfil da {company} — vocês têm uma presença incrível! Muitos estabelecimentos como o de vocês perdem clientes por dificuldade de agendamento. Isso acontece com vocês?',
                    'tip'   => 'Mencione algo específico do perfil deles se souber. Cria rapport.',
                    'choices' => [
                        ['label' => 'Sim, temos esse problema', 'next' => 'qual_01'],
                        ['label' => 'Não muito, mas pode falar', 'next' => 'qual_01'],
                        ['label' => 'Não temos esse problema', 'next' => 'qual_02'],
                    ],
                ],
                'ab_retorno' => [
                    'id'    => 'ab_retorno',
                    'stage' => 'Abertura',
                    'label' => 'Agendamento de retorno',
                    'script'=> 'Sem problema! Quando seria um bom momento para eu ligar de volta — amanhã de manhã ou à tarde?',
                    'tip'   => 'Dê duas opções concretas, nunca pergunte "quando posso ligar?"',
                    'choices' => [
                        ['label' => 'Agendou horário', 'next' => 'enc_retorno'],
                        ['label' => 'Pediu para não ligar mais', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                // ── QUALIFICAÇÃO ─────────────────────────────────────────
                'qual_01' => [
                    'id'    => 'qual_01',
                    'stage' => 'Qualificação',
                    'label' => 'Dor de agendamento confirmada',
                    'script'=> 'Entendo. Hoje vocês usam algum sistema para agendamento, ou ainda é tudo pelo WhatsApp e ligação?',
                    'tip'   => 'Escute mais do que fala. Anote o que ele disser.',
                    'choices' => [
                        ['label' => 'Só WhatsApp/ligação', 'next' => 'qual_03'],
                        ['label' => 'Tem algum sistema', 'next' => 'qual_04'],
                    ],
                ],
                'qual_02' => [
                    'id'    => 'qual_02',
                    'stage' => 'Qualificação',
                    'label' => 'Prospect não sente a dor',
                    'script'=> 'Que ótimo! Curiosidade: quantas horas por semana a equipe gasta respondendo mensagens de agendamento?',
                    'tip'   => 'Plante a semente do tempo perdido. Deixe o silêncio trabalhar.',
                    'choices' => [
                        ['label' => 'Falou em tempo / reconheceu gasto', 'next' => 'qual_03'],
                        ['label' => 'Realmente não vê problema', 'next' => 'pitch_01'],
                    ],
                ],
                'qual_03' => [
                    'id'    => 'qual_03',
                    'stage' => 'Qualificação',
                    'label' => 'Volume de atendimentos',
                    'script'=> 'Quantos atendimentos vocês fazem por semana, em média?',
                    'tip'   => 'Leads com +50 atendimentos/semana têm ROI mais fácil de demonstrar.',
                    'choices' => [
                        ['label' => 'Mais de 50/semana', 'next' => 'pitch_01'],
                        ['label' => 'Menos de 50/semana', 'next' => 'pitch_01'],
                    ],
                ],
                'qual_04' => [
                    'id'    => 'qual_04',
                    'stage' => 'Qualificação',
                    'label' => 'Já tem sistema',
                    'script'=> 'Que sistema vocês usam? Estão satisfeitos com ele?',
                    'tip'   => 'Não fale mal do concorrente. Pergunte o que falta.',
                    'choices' => [
                        ['label' => 'Satisfeitos com o atual', 'next' => 'obj_concorrente'],
                        ['label' => 'Insatisfeitos / algo falta', 'next' => 'pitch_01'],
                    ],
                ],

                // ── PITCH ────────────────────────────────────────────────
                'pitch_01' => [
                    'id'    => 'pitch_01',
                    'stage' => 'Pitch',
                    'label' => 'Apresentação do MM Cloud',
                    'script'=> 'O MM Cloud é um sistema de agendamento com IA que atende seus clientes automaticamente pelo WhatsApp 24h, sem precisar de recepcionista. Ele confirma, cancela e reagenda — tudo sozinho.',
                    'tip'   => 'Pause após "24h". Deixe o benefício principal pousar antes de continuar.',
                    'choices' => [
                        ['label' => 'Interessante, como funciona?', 'next' => 'pitch_02'],
                        ['label' => 'Quanto custa?', 'next' => 'pitch_preco'],
                        ['label' => 'Já temos solução', 'next' => 'obj_concorrente'],
                    ],
                ],
                'pitch_02' => [
                    'id'    => 'pitch_02',
                    'stage' => 'Pitch',
                    'label' => 'Como funciona na prática',
                    'script'=> 'O cliente manda mensagem no WhatsApp de vocês, a IA entende o pedido, verifica a agenda em tempo real e já confirma o horário. Vocês recebem uma notificação e pronto. Nenhuma mensagem fica sem resposta.',
                    'tip'   => 'Se possível envie um print ou vídeo no WhatsApp deles enquanto fala.',
                    'choices' => [
                        ['label' => 'Entendeu, quer ver demonstração', 'next' => 'fecha_01'],
                        ['label' => 'Quanto custa?', 'next' => 'pitch_preco'],
                        ['label' => 'Tenho dúvidas', 'next' => 'obj_generico'],
                    ],
                ],
                'pitch_preco' => [
                    'id'    => 'pitch_preco',
                    'stage' => 'Pitch',
                    'label' => 'Apresentar preço com ancoragem',
                    'script'=> 'O investimento começa em R$ 197/mês — menos que um dia de trabalho de uma recepcionista. E o sistema funciona 24h, 7 dias por semana, sem férias.',
                    'tip'   => 'Ancore sempre no custo de uma recepcionista. Nunca defenda o preço sozinho.',
                    'choices' => [
                        ['label' => 'Ficou interessado', 'next' => 'fecha_01'],
                        ['label' => 'Achou caro', 'next' => 'obj_preco'],
                        ['label' => 'Precisa pensar', 'next' => 'obj_pensar'],
                    ],
                ],

                // ── OBJEÇÃO ──────────────────────────────────────────────
                'obj_preco' => [
                    'id'    => 'obj_preco',
                    'stage' => 'Objeção',
                    'label' => 'Objeção: preço alto',
                    'script'=> 'Entendo a preocupação. Me diz: hoje quanto vocês perdem em clientes que não conseguem agendar fora do horário comercial? O sistema paga o próprio custo em menos de uma semana para a maioria dos clientes.',
                    'tip'   => 'Use ROI. Pergunte, não afirme. Deixe ele calcular.',
                    'choices' => [
                        ['label' => 'Reconheceu o valor', 'next' => 'fecha_01'],
                        ['label' => 'Ainda acha caro', 'next' => 'obj_pensar'],
                        ['label' => 'Quer ver demonstração mesmo assim', 'next' => 'fecha_01'],
                    ],
                ],
                'obj_pensar' => [
                    'id'    => 'obj_pensar',
                    'stage' => 'Objeção',
                    'label' => 'Objeção: precisa pensar',
                    'script'=> 'Com certeza, é uma decisão importante. O que especificamente ainda gera dúvida — é o valor, a tecnologia, ou precisa consultar alguém?',
                    'tip'   => 'Destrinche a objeção. "Precisa pensar" geralmente esconde outra coisa.',
                    'choices' => [
                        ['label' => 'É o preço', 'next' => 'obj_preco'],
                        ['label' => 'Precisa consultar sócio/marido', 'next' => 'fecha_01'],
                        ['label' => 'Não tem certeza da tecnologia', 'next' => 'pitch_02'],
                    ],
                ],
                'obj_concorrente' => [
                    'id'    => 'obj_concorrente',
                    'stage' => 'Objeção',
                    'label' => 'Objeção: já tem sistema',
                    'script'=> 'Legal! O que o sistema atual não faz que vocês gostariam que fizesse? Pergunto porque o MM Cloud tem pontos específicos que sistemas mais antigos não cobrem, como IA generativa no atendimento.',
                    'tip'   => 'Nunca critique o concorrente. Pergunte o que falta.',
                    'choices' => [
                        ['label' => 'Mencionou lacuna que temos', 'next' => 'pitch_02'],
                        ['label' => 'Está mesmo satisfeito', 'next' => 'enc_sem_interesse'],
                    ],
                ],
                'obj_generico' => [
                    'id'    => 'obj_generico',
                    'stage' => 'Objeção',
                    'label' => 'Dúvida genérica',
                    'script'=> 'Claro! Qual é a principal dúvida agora?',
                    'tip'   => 'Escute. Anote. Responda somente o que foi perguntado.',
                    'choices' => [
                        ['label' => 'Tirou a dúvida, quer seguir', 'next' => 'fecha_01'],
                        ['label' => 'Mais objeções de preço', 'next' => 'obj_preco'],
                        ['label' => 'Não quer mais', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                // ── FECHAMENTO ───────────────────────────────────────────
                'fecha_01' => [
                    'id'    => 'fecha_01',
                    'stage' => 'Fechamento',
                    'label' => 'Proposta de demo / reunião',
                    'script'=> 'Que tal marcarmos uma demonstração de 20 minutos? Eu mostro o sistema funcionando em tempo real e vocês veem se faz sentido para a {company}. Quando ficaria melhor — amanhã às 10h ou quinta às 14h?',
                    'tip'   => 'Feche sempre com duas opções de horário. Nunca pergunte "quando você pode?".',
                    'choices' => [
                        ['label' => 'Agendou reunião / demo', 'next' => 'enc_reuniao'],
                        ['label' => 'Quer pensar mais', 'next' => 'fecha_02'],
                        ['label' => 'Não quer agora', 'next' => 'enc_sem_interesse'],
                    ],
                ],
                'fecha_02' => [
                    'id'    => 'fecha_02',
                    'stage' => 'Fechamento',
                    'label' => 'Fechamento com urgência',
                    'script'=> 'Entendo! Só um detalhe: estamos com vagas limitadas para onboarding esse mês. Posso deixar seu nome na lista de espera e confirmar na semana que vem?',
                    'tip'   => 'Urgência real > falsa urgência. Use só se tiver base de verdade.',
                    'choices' => [
                        ['label' => 'Aceitou entrar na lista', 'next' => 'enc_retorno'],
                        ['label' => 'Decidiu agendar agora', 'next' => 'enc_reuniao'],
                        ['label' => 'Recusou', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                // ── ENCERRADO ────────────────────────────────────────────
                'enc_reuniao' => [
                    'id'      => 'enc_reuniao',
                    'stage'   => 'Encerrado',
                    'label'   => 'Reunião agendada',
                    'outcome' => 'reuniao_agendada',
                    'choices' => [],
                ],
                'enc_retorno' => [
                    'id'      => 'enc_retorno',
                    'stage'   => 'Encerrado',
                    'label'   => 'Retorno agendado',
                    'outcome' => 'retorno_agendado',
                    'choices' => [],
                ],
                'enc_sem_interesse' => [
                    'id'      => 'enc_sem_interesse',
                    'stage'   => 'Encerrado',
                    'label'   => 'Sem interesse',
                    'outcome' => 'sem_interesse',
                    'choices' => [],
                ],
            ],
        ];
    }
}
