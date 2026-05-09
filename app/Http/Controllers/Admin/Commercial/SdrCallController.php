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

    // ── Fluxo padrão baseado no script real da MM Cloud ───────────────────
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
                    'script' => 'Oi, tudo bem? Falo com a pessoa responsável pelos agendamentos da clínica/salão?',
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
                    'script' => 'Perfeito. Meu nome é {sdr_name}, sou da MM Cloud. Eu conheci o trabalho de vocês pelo Instagram e vi que vocês têm uma presença bem ativa por lá. Por isso, queria te fazer uma pergunta rápida: hoje, quando uma cliente chama vocês pelo WhatsApp ou pelo direct querendo agendar, esse atendimento é feito por alguém da equipe ou vocês já usam alguma automação?',
                    'tip'    => 'Mencione algo específico do perfil deles se souber. A pergunta é simples e reduz resistência.',
                    'choices' => [
                        ['label' => 'Fazem tudo manualmente', 'next' => 'qual_01'],
                        ['label' => 'Já usam alguma automação', 'next' => 'qual_sistema'],
                        ['label' => 'Estão ocupados / pressa', 'next' => 'ab_pressa'],
                    ],
                ],

                'ab_o_que_e' => [
                    'id'     => 'ab_o_que_e',
                    'stage'  => 'Abertura',
                    'label'  => 'Explicar brevemente do que se trata',
                    'script' => 'Claro. É um contato rápido da MM Cloud sobre uma solução de atendimento e agendamento com Inteligência Artificial, que ajuda clínicas e salões a responder clientes pelo WhatsApp, organizar horários e evitar perda de oportunidades vindas das redes sociais. Como envolve atendimento, agenda e conversão de clientes, o ideal é falar com quem decide sobre essa área.',
                    'tip'    => 'Breve e objetivo. O objetivo é chegar no decisor.',
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
                    'script' => 'Tudo bem. Qual seria o melhor horário para falar com ela? E posso confirmar o nome, por gentileza?',
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
                    'script' => 'Entendo. Então qual seria o melhor caminho para apresentar essa solução à pessoa responsável: WhatsApp, e-mail ou retorno em algum horário específico?',
                    'tip'    => 'Ofereça alternativas. Não insista em falar com quem não está disposto.',
                    'choices' => [
                        ['label' => 'Sugeriu um canal (WhatsApp/e-mail)', 'next' => 'enc_retorno'],
                        ['label' => 'Recusou completamente', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'ab_pressa' => [
                    'id'     => 'ab_pressa',
                    'stage'  => 'Abertura',
                    'label'  => 'Versão ultra curta — prospect com pressa',
                    'script' => 'Entendo que você esteja na correria, vou ser bem objetivo. A MM Cloud criou uma IA de atendimento e agendamento para clínicas e salões, que responde clientes pelo WhatsApp de forma humanizada e conduz até o agendamento, inclusive fora do horário comercial. Podemos marcar 15 minutos para eu te mostrar funcionando? Amanhã de manhã ou à tarde?',
                    'tip'    => 'Ultra curto. Vá direto ao fechamento. Duas opções de horário.',
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
                    'script' => 'Hoje vocês conseguem responder todos os contatos com rapidez e manter um padrão no atendimento, ou em alguns momentos acaba ficando corrido?',
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
                    'script' => 'Que sistema vocês usam? Estão satisfeitos com ele?',
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
                    'script' => 'Faz total sentido. Isso acontece muito em salões e clínicas, principalmente quando a equipe está atendendo presencialmente e, ao mesmo tempo, precisa responder WhatsApp, direct, confirmar horário e organizar agenda. O problema é que, nesse intervalo, algumas oportunidades acabam esfriando.',
                    'tip'    => 'Pausa após "esfriando". Deixe o ponto pousar antes de continuar.',
                    'choices' => [
                        ['label' => 'Concordou — seguir para pitch', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_perde_lead' => [
                    'id'     => 'qual_perde_lead',
                    'stage'  => 'Qualificação',
                    'label'  => 'Confirmou que perde leads',
                    'script' => 'Entendi. Esse é exatamente o tipo de cenário em que a solução costuma gerar resultado mais rápido. Porque não se trata apenas de responder mensagem: trata-se de conduzir a cliente interessada até o agendamento, com padrão, velocidade e sem depender de alguém parar tudo para atender.',
                    'tip'    => 'Lead muito qualificado. Vá para o pitch com confiança.',
                    'choices' => [
                        ['label' => 'Demonstrou interesse — seguir', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_tudo_bem' => [
                    'id'     => 'qual_tudo_bem',
                    'stage'  => 'Qualificação',
                    'label'  => 'Diz que está tudo bem',
                    'script' => 'Ótimo, isso é um excelente sinal. Normalmente, quem já tem um atendimento minimamente organizado é quem mais consegue ganhar escala com a IA, porque a tecnologia entra para melhorar padrão, velocidade e conversão, não para bagunçar o processo. Hoje vocês conseguem saber quantos contatos chegam por semana e quantos efetivamente viram agendamento?',
                    'tip'    => 'Plante a semente da métrica. Quem não mede, não sabe onde está perdendo.',
                    'choices' => [
                        ['label' => 'Não tem essa métrica', 'next' => 'pitch_01'],
                        ['label' => 'Tem e está bem', 'next' => 'pitch_01'],
                    ],
                ],

                'qual_vago' => [
                    'id'     => 'qual_vago',
                    'stage'  => 'Qualificação',
                    'label'  => 'Resposta vaga — aprofundar',
                    'script' => 'Perfeito. E quando você diz "a gente responde normal", vocês conseguem acompanhar todos os contatos até o agendamento ou existe contato que acaba ficando pelo caminho?',
                    'tip'    => 'Não aceite "está tudo bem" sem aprofundar. A maioria não sabe que perde lead.',
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
                    'label'  => 'Apresentação da solução',
                    'script' => 'A MM Cloud criou uma solução exatamente para esse ponto. É uma plataforma de agendamento integrada com Inteligência Artificial, treinada de acordo com a forma como a sua clínica/salão atende. Na prática, ela conversa com a cliente pelo WhatsApp de forma humanizada, entende o que ela precisa, tira dúvidas, conduz o atendimento e ajuda a transformar esse contato em agendamento, inclusive fora do horário comercial.',
                    'tip'    => 'Pause após "horário comercial". Deixe o benefício principal pousar antes de continuar.',
                    'choices' => [
                        ['label' => 'Interessante, como funciona?', 'next' => 'pitch_detalhe'],
                        ['label' => 'Mas atendimento por IA fica muito robótico', 'next' => 'obj_robotico'],
                        ['label' => 'Já temos recepcionista', 'next' => 'obj_recepcionista'],
                        ['label' => 'Quanto custa?', 'next' => 'obj_preco'],
                        ['label' => 'Me manda mais informações', 'next' => 'obj_info'],
                    ],
                ],

                'pitch_detalhe' => [
                    'id'     => 'pitch_detalhe',
                    'stage'  => 'Pitch',
                    'label'  => 'Detalhar + gatilho de curiosidade',
                    'script' => 'O ponto mais interessante é que não é uma automação fria, daquelas respostas engessadas. A IA é adaptada ao padrão de atendimento da empresa, com o tom, as preferências, os serviços, horários, regras e forma de abordagem que vocês escolherem. O que tem chamado atenção dos nossos clientes é que muitos achavam que o problema era "falta de procura", mas, na prática, estavam perdendo oportunidades por demora na resposta, falta de acompanhamento ou falha na condução até o agendamento.',
                    'tip'    => 'Não venda a tecnologia. Venda o resultado de não perder cliente.',
                    'choices' => [
                        ['label' => 'Ficou curioso — seguir', 'next' => 'pitch_impacto'],
                        ['label' => 'Ainda tem dúvida sobre robótico', 'next' => 'obj_robotico'],
                    ],
                ],

                'pitch_impacto' => [
                    'id'     => 'pitch_impacto',
                    'stage'  => 'Pitch',
                    'label'  => 'Pergunta de impacto — 21h',
                    'script' => 'Hoje, se uma cliente chamar vocês às 21h ou no domingo querendo agendar, o que acontece com esse contato?',
                    'tip'    => 'Essa pergunta faz o prospect visualizar a perda. Deixe o silêncio trabalhar.',
                    'choices' => [
                        ['label' => 'Só vê no próximo expediente', 'next' => 'pitch_reforco_perda'],
                        ['label' => 'Alguém responde mesmo fora do horário', 'next' => 'pitch_reforco_cansativo'],
                    ],
                ],

                'pitch_reforco_perda' => [
                    'id'     => 'pitch_reforco_perda',
                    'stage'  => 'Pitch',
                    'label'  => 'Reforço — perde cliente sem perceber',
                    'script' => 'Exatamente. E esse é um ponto em que muitos negócios perdem receita sem perceber, porque a cliente interessada nem sempre espera. Se ela chama às 22h querendo saber preço, horário ou disponibilidade, ela não fica esperando o dia seguinte. Ela já é atendida, orientada na hora e conduzida para o agendamento.',
                    'tip'    => 'Não exagere. Deixe a lógica falar por si.',
                    'choices' => [
                        ['label' => 'Concordou — ir para fechamento', 'next' => 'fecha_01'],
                        ['label' => 'Ainda tem objeções', 'next' => 'obj_preco'],
                    ],
                ],

                'pitch_reforco_cansativo' => [
                    'id'     => 'pitch_reforco_cansativo',
                    'stage'  => 'Pitch',
                    'label'  => 'Reforço — equipe não precisa estar disponível 24h',
                    'script' => 'Ótimo, isso mostra cuidado com o atendimento. A questão é: isso depende de alguém estar disponível o tempo todo, certo? A nossa solução tira esse peso da equipe e mantém o padrão de resposta sem depender de disponibilidade humana permanente.',
                    'tip'    => 'Não diminua o esforço deles. Valorize e mostre o ganho.',
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
                    'script' => 'Essa é uma preocupação super legítima. Inclusive, é justamente por isso que a nossa solução não trabalha com respostas prontas e engessadas. A IA é treinada para seguir o tom da empresa, a forma como vocês querem tratar a cliente, o nível de formalidade, os serviços oferecidos e até o jeito de conduzir a conversa. A ideia não é parecer um robô. A ideia é manter um atendimento ágil, educado, personalizado e coerente com o padrão da marca.',
                    'tip'    => 'Valide a preocupação antes de responder. "Essa preocupação é legítima" funciona muito bem.',
                    'choices' => [
                        ['label' => 'Entendeu — quer ver funcionando', 'next' => 'fecha_01'],
                        ['label' => 'Ainda cético', 'next' => 'fecha_01'],
                    ],
                ],

                'obj_recepcionista' => [
                    'id'     => 'obj_recepcionista',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: já tem recepcionista',
                    'script' => 'Perfeito. A proposta não é substituir o atendimento humano que vocês já têm. Pelo contrário: é tirar da equipe o peso do atendimento repetitivo e permitir que ela foque no que exige decisão, cuidado e relacionamento. A IA pode cuidar da primeira triagem, responder dúvidas frequentes, organizar solicitações e conduzir agendamentos. Assim, a equipe ganha tempo e o atendimento fica mais rápido e padronizado.',
                    'tip'    => 'Nunca posicione como concorrente da recepcionista. É complemento, não substituição.',
                    'choices' => [
                        ['label' => 'Entendeu — quer ver', 'next' => 'fecha_01'],
                        ['label' => 'Ainda resistente', 'next' => 'obj_sem_tempo'],
                    ],
                ],

                'obj_ja_tem_sistema' => [
                    'id'     => 'obj_ja_tem_sistema',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: já tem sistema de agenda',
                    'script' => 'Ótimo. Isso, na verdade, ajuda bastante. A diferença é que muitos sistemas organizam a agenda depois que o agendamento acontece. A nossa solução atua antes: no atendimento, na resposta rápida, na condução da cliente e na conversão do contato em horário marcado. Ou seja, não é só agenda. É atendimento inteligente integrado à jornada da cliente.',
                    'tip'    => 'Posicione como complemento, não concorrente. A diferença é: antes do agendamento vs. depois.',
                    'choices' => [
                        ['label' => 'Fez sentido — quer ver', 'next' => 'fecha_01'],
                        ['label' => 'Não quer trocar o processo atual', 'next' => 'obj_nao_trocar'],
                    ],
                ],

                'obj_nao_trocar' => [
                    'id'     => 'obj_nao_trocar',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: não quer trocar sistema atual',
                    'script' => 'Perfeito. E a ideia não é necessariamente trocar tudo o que vocês já usam. Primeiro, a gente entende o fluxo atual e identifica onde a IA pode entrar para melhorar atendimento, resposta e conversão. Muitas vezes, ela complementa o processo que a empresa já tem.',
                    'tip'    => 'Não force. Convide para apenas olhar. Sem compromisso.',
                    'choices' => [
                        ['label' => 'Aceitou olhar sem compromisso', 'next' => 'fecha_01'],
                        ['label' => 'Recusou', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'obj_preco' => [
                    'id'     => 'obj_preco',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: quanto custa?',
                    'script' => 'O valor depende um pouco do volume de atendimento e do formato de uso, por isso prefiro não te passar algo genérico sem entender o cenário de vocês. Mas a lógica da solução é justamente reduzir custo operacional, evitar perda de agendamento e melhorar a eficiência do atendimento. Em muitos casos, o custo de alguns agendamentos perdidos no mês já supera o investimento na plataforma.',
                    'tip'    => 'Não dê preço sem contexto. Redirecione para a reunião onde você vai entender o volume deles.',
                    'choices' => [
                        ['label' => 'Aceitou marcar a reunião para saber o valor', 'next' => 'fecha_01'],
                        ['label' => 'Insistiu em saber o valor agora', 'next' => 'fecha_01'],
                        ['label' => 'Sem interesse pelo preço', 'next' => 'enc_sem_interesse'],
                    ],
                ],

                'obj_info' => [
                    'id'     => 'obj_info',
                    'stage'  => 'Objeção',
                    'label'  => 'Objeção: me manda informações',
                    'script' => 'Claro, te mando sim. Inclusive, prefiro te mandar uma demonstração prática, porque só texto não mostra bem o potencial da solução. Vou te enviar um exemplo para você testar como se fosse uma cliente chamando para agendar. Mas para fazer sentido para o seu negócio, o ideal é uma conversa rápida, porque a IA é adaptada à realidade de cada empresa. Podemos agendar 15 minutos amanhã para eu te mostrar como isso funcionaria com o fluxo de vocês?',
                    'tip'    => 'Ponto importante: não vire apenas "envio de material". Use o material como ponte para a reunião.',
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
                    'script' => 'Justamente por isso pode fazer sentido olhar agora. Quando a operação está corrida, normalmente é porque a equipe está absorvendo tarefas repetitivas demais. A proposta da IA é reduzir esse peso, não criar mais trabalho. A implantação é pensada para ser adaptada ao funcionamento de vocês, com treinamento da IA conforme as regras, serviços e preferências da empresa.',
                    'tip'    => 'Virar o argumento: "correria" = sinal de que precisam da solução, não motivo para recusar.',
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
                    'script' => 'Para eu não tomar seu tempo agora, o ideal seria fazermos uma reunião rápida, de 15 a 20 minutos, para entender como funciona o fluxo de atendimento de vocês e mostrar como a IA poderia operar aplicada ao seu negócio. Tenho dois horários disponíveis: amanhã às [HORÁRIO] ou [HORÁRIO]. Qual fica melhor para você?',
                    'tip'    => 'Sempre dê duas opções de horário. Nunca pergunte "quando você pode?" — muda de sim/não para escolha de horário.',
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
                    'script' => 'Entendo. Só um detalhe: estamos abrindo poucas demonstrações por semana justamente porque cada apresentação é personalizada para o tipo de negócio. Por isso, queria reservar um horário para vocês ainda essa semana. Posso deixar seu nome na lista e confirmar?',
                    'tip'    => 'Urgência real > urgência artificial. Use só se tiver base. Alternativa: vantagem competitiva ou perda silenciosa.',
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
                    'tip'     => 'Envie a mensagem de confirmação via WhatsApp imediatamente após a call.',
                    'outcome' => 'reuniao_agendada',
                    'choices' => [],
                ],

                'enc_retorno' => [
                    'id'      => 'enc_retorno',
                    'stage'   => 'Encerrado',
                    'label'   => 'Retorno agendado',
                    'script'  => '',
                    'tip'     => 'Envie a mensagem "após ligação sem reunião agendada" e marque o follow-up no CRM.',
                    'outcome' => 'retorno_agendado',
                    'choices' => [],
                ],

                'enc_sem_interesse' => [
                    'id'      => 'enc_sem_interesse',
                    'stage'   => 'Encerrado',
                    'label'   => 'Sem interesse',
                    'script'  => '',
                    'tip'     => 'Registre o motivo nas notas. Pode reentrar na cadência em 30-60 dias.',
                    'outcome' => 'sem_interesse',
                    'choices' => [],
                ],
            ],
        ];
    }
}
