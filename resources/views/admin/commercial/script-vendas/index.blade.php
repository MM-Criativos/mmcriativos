@php
    $title = 'Script de Vendas';
    $subTitle = 'Árvore de decisão';
@endphp

@extends('layouts.app')

@section('content')
<div x-data="scriptVendas()" x-init="init()">

    <div class="bg-white dark:bg-black shadow-sm sm:rounded-lg" style="overflow:hidden;">

        {{-- ─── Cabeçalho ─────────────────────────────────────────── --}}
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h6 class="text-lg font-semibold text-neutral-800 dark:text-white">📞 Script de Vendas</h6>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Árvore de decisão — 1º contato</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                {{-- Legenda --}}
                <div class="hidden xl:flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-700 inline-block"></span> Fluxo</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-600 inline-block"></span> Positivo</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-700 inline-block"></span> Objeção</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-purple-700 inline-block"></span> Instagram</span>
                </div>
                {{-- Dica mouse --}}
                <span class="hidden lg:inline-flex items-center gap-1 text-xs text-neutral-400 dark:text-neutral-600 border border-neutral-200 dark:border-neutral-700 rounded px-2 py-1">
                    <i data-lucide="mouse" class="w-3 h-3"></i>
                    Meio=arrastar · Scroll=zoom · Dir=menu
                </span>
                {{-- Zoom --}}
                <div class="flex items-center gap-1">
                    <button @click="zoomOut()" title="Zoom Out"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-md btn-mmcriativos">
                        <i data-lucide="zoom-out" class="w-4 h-4"></i>
                    </button>
                    <button @click="zoomReset()" title="Reset zoom"
                        class="inline-flex items-center justify-center px-2 h-8 text-xs rounded-md btn-mmcriativos"
                        x-text="Math.round((zoomLevel||1)*100)+'%'">100%</button>
                    <button @click="zoomIn()" title="Zoom In"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-md btn-mmcriativos">
                        <i data-lucide="zoom-in" class="w-4 h-4"></i>
                    </button>
                </div>
                {{-- Centralizar --}}
                <button @click="center()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-md btn-mmcriativos">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Centralizar</span>
                </button>
            </div>
        </div>

        {{-- ─── Área principal (layout absoluto) ──────────────────── --}}
        <div style="position:relative; height:calc(100vh - 210px); min-height:460px;">

            {{-- Mapa --}}
            <div id="me-container"
                 style="position:absolute; top:0; left:0; bottom:0; overflow:hidden;"
                 :style="{ right: selectedNode ? '360px' : '0px' }">
            </div>

            {{-- Painel lateral --}}
            <div x-show="selectedNode"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 style="position:absolute; top:0; right:0; width:360px; bottom:0; display:flex; flex-direction:column; overflow:hidden;"
                 class="border-l border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">

                {{-- Header do painel --}}
                <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-start gap-3" style="flex-shrink:0;">
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-orange-500 mb-1"
                           x-text="selectedNode ? selectedNode.category : ''"></p>
                        <h3 class="font-semibold text-sm text-neutral-800 dark:text-white leading-snug"
                            x-text="selectedNode ? selectedNode.topic.replace(/\n/g,' ') : ''"></h3>
                    </div>
                    <button @click="selectedNode = null"
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition mt-0.5">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Conteúdo --}}
                <div class="px-5 py-4" style="flex:1; overflow-y:auto;">
                    <template x-if="selectedNode && selectedNode.content && selectedNode.content.length > 0">
                        <div>
                            <template x-for="(block, bi) in selectedNode.content" :key="bi">
                                <div style="margin-bottom:1.25rem;">
                                    <template x-if="block.label">
                                        <p class="text-[11px] font-bold uppercase tracking-wider mb-2"
                                           :class="block.labelColor || 'text-neutral-400'"
                                           x-text="block.label"></p>
                                    </template>
                                    <template x-for="(line, li) in (block.lines || [])" :key="li">
                                        <p class="text-sm text-neutral-700 dark:text-neutral-300"
                                           style="line-height:1.65; margin-bottom:0.15rem;"
                                           x-text="line || ' '"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!selectedNode || !selectedNode.content || selectedNode.content.length === 0">
                        <p class="text-sm text-neutral-400 italic">Nenhum conteúdo para este nó.</p>
                    </template>
                </div>

                {{-- Tags --}}
                <div x-show="selectedNode && selectedNode.tags && selectedNode.tags.length"
                     class="px-5 py-3 border-t border-neutral-100 dark:border-neutral-800 flex flex-wrap gap-1.5"
                     style="flex-shrink:0;">
                    <template x-if="selectedNode && selectedNode.tags">
                        <template x-for="tag in selectedNode.tags" :key="tag">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300"
                                  x-text="tag"></span>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>

    @push('scripts')
        {{-- mind-elixir IIFE local: inicia o CSS automaticamente, sem CDN --}}
        <script src="{{ asset('admin/js/lib/mind-elixir.iife.js') }}"></script>
        <script>
            // ────────────────────────────────────────────────────────────────────────────
            //  DADOS DO SCRIPT DE VENDAS
            // ────────────────────────────────────────────────────────────────────────────
            const SCRIPT_DATA = {
                nodeData: {
                    id: 'root',
                    topic: '📞 Script 1º Contato\nMM Cloud',
                    root: true,
                    style: {
                        fontSize: '15',
                        fontWeight: 'bold'
                    },
                    children: [

                        // ══════════════════════════════════════════════════════
                        //  DIREITA — FLUXO PRINCIPAL
                        // ══════════════════════════════════════════════════════

                        {
                            id: 'abertura',
                            topic: '1. Abertura',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Início da Ligação',
                                content: [{
                                        label: '🎯 Objetivo',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            'Não começar vendendo. Reduzir resistência com uma pergunta simples.']
                                    },
                                    {
                                        label: '💬 Frase de Abertura',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            '"Oi, tudo bem? Falo com a pessoa responsável pelos agendamentos da clínica/salão?"']
                                    }
                                ],
                                tags: ['Etapa 1', 'Abertura']
                            },
                            children: [{
                                    id: 'ab_confirma',
                                    topic: '✅ Confirma ser responsável',
                                    style: {
                                        background: '#15803d',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Abertura → Confirmou',
                                        content: [{
                                                label: '💬 Dizer',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Perfeito. Meu nome é [NOME], sou da MM Cloud.',
                                                    '',
                                                    'Eu conheci o trabalho de vocês pelo Instagram e vi que vocês têm uma presença bem ativa por lá. Por isso, queria te fazer uma pergunta rápida: hoje, quando uma cliente chama vocês pelo WhatsApp ou pelo direct querendo agendar, esse atendimento é feito por alguém da equipe ou vocês já usam alguma automação?"'
                                                ]
                                            },
                                            {
                                                label: '💡 Dica',
                                                labelColor: 'text-yellow-400',
                                                lines: ['Personalizar com o Instagram real do prospect.']
                                            }
                                        ],
                                        tags: ['Confirmou', 'Avançar para Qualificação']
                                    }
                                },
                                {
                                    id: 'ab_nao_resp',
                                    topic: '🔄 Não é o responsável',
                                    style: {
                                        background: '#6d28d9',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Abertura → Não é responsável',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-purple-400',
                                            lines: [
                                                '"Sem problema. Como envolve atendimento, agenda e conversão de clientes, o ideal é falar com quem decide sobre essa área. Quem seria a melhor pessoa para esse assunto?"'
                                            ]
                                        }],
                                        tags: ['Redirecionamento']
                                    }
                                },
                                {
                                    id: 'ab_pergunta',
                                    topic: '❓ "Do que se trata?"',
                                    style: {
                                        background: '#b45309',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Abertura → Pediu contexto',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                '"Claro. É um contato rápido da MM Cloud sobre uma solução de atendimento e agendamento com Inteligência Artificial, que ajuda clínicas e salões a responder clientes pelo WhatsApp, organizar horários e evitar perda de oportunidades vindas das redes sociais. Como envolve atendimento, agenda e conversão de clientes, o ideal é falar com quem decide sobre essa área."'
                                            ]
                                        }],
                                        tags: ['Briefing Rápido']
                                    }
                                },
                                {
                                    id: 'ab_ausente',
                                    topic: '🚫 Responsável ausente',
                                    style: {
                                        background: '#4b5563',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Abertura → Não disponível',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-gray-400',
                                            lines: [
                                                '"Tudo bem. Qual seria o melhor horário para falar com ela? E posso confirmar o nome, por gentileza?"'
                                            ]
                                        }],
                                        tags: ['Retorno', 'Agendar ligação']
                                    }
                                },
                                {
                                    id: 'ab_nao_passa',
                                    topic: '🚫 Não quer passar',
                                    style: {
                                        background: '#4b5563',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Abertura → Resistência no filtro',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-gray-400',
                                            lines: [
                                                '"Entendo. Então qual seria o melhor caminho para apresentar essa solução à pessoa responsável: WhatsApp, e-mail ou retorno em algum horário específico?"'
                                            ]
                                        }],
                                        tags: ['Canal Alternativo']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'qualificacao',
                            topic: '2. Qualificação',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Identificar Cenário Atual',
                                content: [{
                                        label: '🎯 Objetivo',
                                        labelColor: 'text-blue-400',
                                        lines: ['Entender o atendimento atual e identificar a dor invisível.']
                                    },
                                    {
                                        label: '💬 Pergunta Principal',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            '"Hoje vocês conseguem responder todos os contatos com rapidez e manter um padrão no atendimento, ou em alguns momentos acaba ficando corrido?"'
                                        ]
                                    }
                                ],
                                tags: ['Etapa 2', 'Qualificação']
                            },
                            children: [{
                                    id: 'qual_alt1',
                                    topic: '💬 Alt: Conversão',
                                    data: {
                                        category: 'Qualificação — Pergunta Alternativa',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                '"Vocês conseguem medir quantas pessoas chamam no WhatsApp ou Instagram e realmente viram agendamento?"'
                                            ]
                                        }],
                                        tags: ['Conversão']
                                    }
                                },
                                {
                                    id: 'qual_alt2',
                                    topic: '💬 Alt: Fora do horário',
                                    data: {
                                        category: 'Qualificação — Pergunta Alternativa',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                '"Quando a cliente chama fora do horário comercial, ela recebe algum retorno automático ou só é respondida depois?"'
                                            ]
                                        }],
                                        tags: ['Fora do horário']
                                    }
                                },
                                {
                                    id: 'qual_alt3',
                                    topic: '💬 Alt: Sistema de agenda',
                                    data: {
                                        category: 'Qualificação — Pergunta Alternativa',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                '"Hoje a agenda de vocês é organizada manualmente ou existe algum sistema ajudando nesse controle?"'
                                            ]
                                        }],
                                        tags: ['Sistema']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'apresentacao',
                            topic: '3. Apresentação da Solução',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Pitch da Solução',
                                content: [{
                                    label: '💬 Dizer',
                                    labelColor: 'text-blue-400',
                                    lines: [
                                        '"Entendi, [FULANO]. A MM Cloud criou uma solução exatamente para esse ponto.',
                                        '',
                                        'É uma plataforma de agendamento integrada com Inteligência Artificial, treinada de acordo com a forma como a sua clínica/salão atende.',
                                        '',
                                        'Na prática, ela conversa com a cliente pelo WhatsApp de forma humanizada, entende o que ela precisa, tira dúvidas, conduz o atendimento e ajuda a transformar esse contato em agendamento, inclusive fora do horário comercial."',
                                        '',
                                        '[Pausa curta]',
                                        '',
                                        '"O ponto mais interessante é que não é uma automação fria, daquelas respostas engessadas. A IA é adaptada ao padrão de atendimento da empresa, com o tom, as preferências, os serviços, horários, regras e forma de abordagem que vocês escolherem."'
                                    ]
                                }],
                                tags: ['Etapa 3', 'Pitch', 'IA humanizada']
                            }
                        },

                        {
                            id: 'gatilho',
                            topic: '4. Gatilho de Curiosidade',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Gerar Interesse — Escolha um dos 3',
                                content: [],
                                tags: ['Etapa 4']
                            },
                            children: [{
                                    id: 'gat1',
                                    topic: '💡 "Falta de procura"',
                                    data: {
                                        category: 'Gatilho 1 — Dor invisível',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                '"O que tem chamado atenção dos nossos clientes é que muitos achavam que o problema era \'falta de procura\', mas, na prática, estavam perdendo oportunidades por demora na resposta, falta de acompanhamento ou falha na condução até o agendamento."'
                                            ]
                                        }],
                                        tags: ['Dor invisível']
                                    }
                                },
                                {
                                    id: 'gat2',
                                    topic: '💡 "Cliente vai embora"',
                                    data: {
                                        category: 'Gatilho 2 — Perda silenciosa',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                '"Às vezes a cliente até tem intenção de marcar, mas se ela demora para receber retorno, chama outro salão, outra clínica ou simplesmente desiste. O sistema atua justamente nesse intervalo crítico."'
                                            ]
                                        }],
                                        tags: ['Intervalo crítico']
                                    }
                                },
                                {
                                    id: 'gat3',
                                    topic: '💡 "22h querendo agendar"',
                                    data: {
                                        category: 'Gatilho 3 — Urgência de resposta',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                '"A proposta é simples: se a cliente chama às 22h querendo saber preço, horário ou disponibilidade, ela não fica esperando o dia seguinte. Ela já é atendida, orientada na hora e conduzida para o agendamento."'
                                            ]
                                        }],
                                        tags: ['Fora do horário', 'Urgência']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'impacto',
                            topic: '5. Pergunta de Impacto',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Fazer o Prospect Visualizar a Perda',
                                content: [{
                                        label: '💬 Perguntar',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            '"Hoje, se uma cliente chamar vocês às 21h ou no domingo querendo agendar, o que acontece com esse contato?"'
                                        ]
                                    },
                                    {
                                        label: '💡 Por que funciona',
                                        labelColor: 'text-yellow-400',
                                        lines: ['Faz o prospect visualizar concretamente a perda. Muito eficaz.']
                                    }
                                ],
                                tags: ['Etapa 5', 'Visualização da perda']
                            },
                            children: [{
                                    id: 'imp_depois',
                                    topic: '😟 "Só vejo depois"',
                                    style: {
                                        background: '#b91c1c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Impacto → Confirmou a dor',
                                        content: [{
                                            label: '💬 Resposta',
                                            labelColor: 'text-red-400',
                                            lines: [
                                                '"Exatamente. E esse é um ponto em que muitos negócios perdem receita sem perceber, porque a cliente interessada nem sempre espera."'
                                            ]
                                        }],
                                        tags: ['Dor confirmada', 'Avançar para reunião']
                                    }
                                },
                                {
                                    id: 'imp_responde',
                                    topic: '🤔 "Respondo fora do horário"',
                                    style: {
                                        background: '#b45309',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Impacto → Tem cuidado com atendimento',
                                        content: [{
                                            label: '💬 Resposta',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                '"Ótimo, isso mostra cuidado com o atendimento. A questão é: isso depende de alguém estar disponível o tempo todo, certo? A nossa solução tira esse peso da equipe e mantém o padrão de resposta sem depender de disponibilidade humana permanente."'
                                            ]
                                        }],
                                        tags: ['Dependência humana', 'Escalabilidade']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'reuniao',
                            topic: '6. Convite para Reunião',
                            direction: 1,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Fechamento da Ligação',
                                content: [{
                                        label: '💬 Padrão',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            '"Para eu não tomar seu tempo agora, o ideal seria fazermos uma reunião rápida, de 15 a 20 minutos, para entender como funciona o fluxo de atendimento de vocês e mostrar como a IA poderia operar aplicada ao seu negócio. Você teria disponibilidade amanhã ou depois para olharmos isso juntos?"'
                                        ]
                                    },
                                    {
                                        label: '⭐ Melhor: ofereça 2 horários',
                                        labelColor: 'text-green-400',
                                        lines: [
                                            '"Tenho dois horários disponíveis: amanhã às [HORÁRIO] ou [HORÁRIO]. Qual fica melhor para você?"'
                                        ]
                                    },
                                    {
                                        label: '💡 Por que funciona',
                                        labelColor: 'text-yellow-400',
                                        lines: [
                                            'Muda de "sim ou não" para "qual horário". Aumenta muito a conversão.']
                                    }
                                ],
                                tags: ['Etapa 6', 'Fechamento', 'Agendamento']
                            },
                            children: [{
                                    id: 'reun_int',
                                    topic: '🙂 "Interessante"',
                                    style: {
                                        background: '#15803d',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Reunião → Interesse demonstrado',
                                        content: [{
                                            label: '💬 Resposta',
                                            labelColor: 'text-green-400',
                                            lines: [
                                                '"Perfeito. E eu acredito que faça bastante sentido para vocês justamente porque o Instagram já parece ser um canal importante de captação. Na reunião, conseguimos mostrar um exemplo prático de como a IA receberia uma cliente, responderia dúvidas e conduziria até o agendamento. Fica melhor amanhã de manhã ou à tarde?"'
                                            ]
                                        }],
                                        tags: ['Interesse confirmado']
                                    }
                                },
                                {
                                    id: 'reun_ok',
                                    topic: '👍 "Pode ser"',
                                    style: {
                                        background: '#15803d',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Reunião → Aceitou',
                                        content: [{
                                            label: '💬 Resposta',
                                            labelColor: 'text-green-400',
                                            lines: [
                                                '"Ótimo. Vou deixar reservado então. Antes da reunião, vou te enviar uma demonstração rápida para você testar como se fosse uma cliente. Assim, quando conversarmos, você já consegue visualizar a aplicação no seu próprio atendimento."'
                                            ]
                                        }],
                                        tags: ['Agendado!', 'Enviar demo']
                                    }
                                }
                            ]
                        },

                        // ── 12 Cenários de Objeção ────────────────────────────────────
                        {
                            id: 'cenarios',
                            topic: '7. Cenários de Objeção',
                            direction: 1,
                            style: {
                                background: '#7c2d12',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: '12 Cenários + Respostas Prontas',
                                content: [{
                                    label: '💡 Como usar',
                                    labelColor: 'text-orange-400',
                                    lines: [
                                        'Consulte o cenário conforme a resposta do prospect. Cada um tem resposta e fechamento.'
                                    ]
                                }],
                                tags: ['Etapa 7', '12 Cenários']
                            },
                            children: [{
                                    id: 'c1',
                                    topic: '1 — "A gente responde normal"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 1 — Resposta vaga',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: ['"Ah, a gente responde normal."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Perfeito. E quando você diz \'normal\', vocês conseguem acompanhar todos os contatos até o agendamento ou existe contato que acaba ficando pelo caminho?"']
                                            },
                                            {
                                                label: '↳ Se não souber',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Esse é justamente um dos pontos mais importantes. Muitas empresas não perdem cliente por falta de qualidade no serviço, mas porque não conseguem medir e conduzir bem o primeiro contato."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Numa reunião rápida, conseguimos te mostrar como isso pode ser organizado de forma automática e personalizada. Amanhã às [HORÁRIO] seria bom para você?"']
                                            }
                                        ],
                                        tags: ['Resposta vaga']
                                    }
                                },
                                {
                                    id: 'c2',
                                    topic: '2 — "Às vezes demora"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 2 — Admite demora ou correria',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: [
                                                    '"Às vezes demora." / "Fica corrido." / "Tem dia que não dá conta."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Faz total sentido. Isso acontece muito em salões e clínicas, principalmente quando a equipe está atendendo presencialmente e, ao mesmo tempo, precisa responder WhatsApp, direct, confirmar horário e organizar agenda. O problema é que, nesse intervalo, algumas oportunidades acabam esfriando."']
                                            },
                                            {
                                                label: '↳ Continuar',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"A nossa solução entra exatamente aí: ela mantém o atendimento ativo, rápido e padronizado, sem tirar a humanização da conversa."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Acho que vale muito te mostrar isso funcionando. Podemos marcar uma reunião rápida amanhã ou depois?"']
                                            }
                                        ],
                                        tags: ['Correria', 'Dor confirmada']
                                    }
                                },
                                {
                                    id: 'c3',
                                    topic: '3 — "Aqui a gente dá conta"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 3 — Diz que está tudo bem',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: ['"Aqui a gente dá conta." / "Hoje está tranquilo."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Ótimo, isso é um excelente sinal. Normalmente, quem já tem um atendimento minimamente organizado é quem mais consegue ganhar escala com a IA, porque a tecnologia entra para melhorar padrão, velocidade e conversão, não para bagunçar o processo."']
                                            },
                                            {
                                                label: '↳ Aprofundamento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Hoje vocês conseguem saber quantos contatos chegam por semana e quantos efetivamente viram agendamento?"']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Posso te mostrar em 15 minutos como isso funcionaria para vocês. Melhor amanhã às [HORÁRIO] ou [HORÁRIO]?"']
                                            }
                                        ],
                                        tags: ['Resistência suave']
                                    }
                                },
                                {
                                    id: 'c4',
                                    topic: '4 — "WhatsApp cheio / perde lead"',
                                    style: {
                                        background: '#991b1b',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 4 — Fala que perde lead',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-red-400',
                                                lines: [
                                                    '"A gente perde contato." / "Tem mensagem sem resposta." / "O WhatsApp fica cheio."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Entendi. Esse é exatamente o tipo de cenário em que a solução costuma gerar resultado mais rápido. Porque não se trata apenas de responder mensagem: trata-se de conduzir a cliente interessada até o agendamento, com padrão, velocidade e sem depender de alguém parar tudo para atender."']
                                            },
                                            {
                                                label: '↳ Continuar',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"E o mais importante: o sistema pode ser treinado com a linguagem da sua marca, os serviços de vocês, horários, regras de agenda, formas de pagamento, orientações e preferências de atendimento."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Acho que faz muito sentido te mostrar isso aplicado ao seu negócio. Podemos marcar uma conversa rápida amanhã?"']
                                            }
                                        ],
                                        tags: ['Dor forte', 'Alta conversão']
                                    }
                                },
                                {
                                    id: 'c5',
                                    topic: '5 — "Já temos recepcionista"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 5 — Já usa secretária/recepcionista',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: [
                                                    '"Nós já temos recepcionista." / "Já temos alguém que cuida disso."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Perfeito. A proposta não é substituir o atendimento humano que vocês já têm. Pelo contrário: é tirar da equipe o peso do atendimento repetitivo e permitir que ela foque no que exige decisão, cuidado e relacionamento."']
                                            },
                                            {
                                                label: '↳ Complemento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"A IA pode cuidar da primeira triagem, responder dúvidas frequentes, organizar solicitações e conduzir agendamentos. Assim, a equipe ganha tempo e o atendimento fica mais rápido e padronizado."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Em 15 minutos eu consigo te mostrar onde a IA entra sem descaracterizar o atendimento de vocês. Qual horário fica melhor amanhã?"']
                                            }
                                        ],
                                        tags: ['Recepcionista', 'Complementa']
                                    }
                                },
                                {
                                    id: 'c6',
                                    topic: '6 — "IA fica robótico"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 6 — Medo de atendimento robotizado',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: [
                                                    '"Mas atendimento por IA fica muito robótico." / "Tenho medo de ficar impessoal."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Essa é uma preocupação super legítima. Inclusive, é justamente por isso que a nossa solução não trabalha com respostas prontas e engessadas. A IA é treinada para seguir o tom da empresa, a forma como vocês querem tratar a cliente, o nível de formalidade, os serviços oferecidos e até o jeito de conduzir a conversa."']
                                            },
                                            {
                                                label: '↳ Complemento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"A ideia não é parecer um robô. A ideia é manter um atendimento ágil, educado, personalizado e coerente com o padrão da marca."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Na reunião, eu consigo te mostrar uma simulação real para você sentir essa diferença. Podemos marcar amanhã?"']
                                            }
                                        ],
                                        tags: ['Humanização', 'IA personalizada']
                                    }
                                },
                                {
                                    id: 'c7',
                                    topic: '7 — "Não tenho interesse"',
                                    style: {
                                        background: '#991b1b',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 7 — Rejeição direta',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-red-400',
                                                lines: ['"Não tenho interesse."']
                                            },
                                            {
                                                label: '💬 Resposta (sem confronto)',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Tranquilo, sem problema. Só para eu entender: hoje vocês já têm algum sistema que responde os clientes, organiza a agenda e acompanha o contato até o agendamento?"']
                                            },
                                            {
                                                label: '↳ Se "não"',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Entendi. Então talvez valha pelo menos conhecer, porque pode ser uma forma de reduzir perda de oportunidade sem aumentar a estrutura de atendimento."']
                                            },
                                            {
                                                label: '🔒 Fechamento leve',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Não precisa decidir nada agora. Podemos fazer uma conversa rápida só para você entender se faz sentido ou não. Tenho [HORÁRIO] ou [HORÁRIO]. Algum deles funciona?"']
                                            }
                                        ],
                                        tags: ['Rejeição', 'Abordagem leve']
                                    }
                                },
                                {
                                    id: 'c8',
                                    topic: '8 — "Me manda informações"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 8 — Pede material',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: ['"Me manda mais informações."']
                                            },
                                            {
                                                label: '⚠️ Atenção',
                                                labelColor: 'text-red-400',
                                                lines: [
                                                    'Não virar só "envio de material". Isso geralmente = geladeira.']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Claro, te mando sim. Inclusive, prefiro te mandar uma demonstração prática, porque só texto não mostra bem o potencial da solução. Vou te enviar um exemplo para você testar como se fosse uma cliente chamando para agendar."']
                                            },
                                            {
                                                label: '↳ Condutor',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Mas para fazer sentido para o seu negócio, o ideal é uma conversa rápida. Podemos agendar 15 minutos amanhã?"']
                                            },
                                            {
                                                label: '↳ Se insistir',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Sem problema. Eu te envio agora e amanhã faço um contato rápido para saber o que achou. Pode ser nesse mesmo horário?"']
                                            }
                                        ],
                                        tags: ['Material', 'Demo', 'Follow-up garantido']
                                    }
                                },
                                {
                                    id: 'c9',
                                    topic: '9 — "Quanto custa?"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 9 — Pergunta de preço',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: ['"Quanto custa?" / "Qual o valor?"']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"O valor depende um pouco do volume de atendimento e do formato de uso, por isso eu prefiro não te passar algo genérico sem entender o cenário de vocês. Mas a lógica da solução é justamente reduzir custo operacional, evitar perda de agendamento e melhorar a eficiência do atendimento."']
                                            },
                                            {
                                                label: '↳ Complemento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Em muitos casos, o custo de alguns agendamentos perdidos no mês já supera o investimento na plataforma."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Na reunião, conseguimos entender o volume de vocês e indicar o modelo mais adequado. Melhor amanhã de manhã ou à tarde?"']
                                            }
                                        ],
                                        tags: ['Preço', 'ROI']
                                    }
                                },
                                {
                                    id: 'c10',
                                    topic: '10 — "Já temos sistema de agenda"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 10 — Já usa sistema',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: ['"A gente já tem sistema de agenda."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Ótimo. Isso, na verdade, ajuda bastante. A diferença é que muitos sistemas organizam a agenda depois que o agendamento acontece. A nossa solução atua antes: no atendimento, na resposta rápida, na condução da cliente e na conversão do contato em horário marcado."']
                                            },
                                            {
                                                label: '↳ Complemento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"Ou seja, não é só agenda. É atendimento inteligente integrado à jornada da cliente."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Vale a pena te mostrar essa diferença na prática. Podemos marcar amanhã?"']
                                            }
                                        ],
                                        tags: ['Sistema existente', 'Diferenciação']
                                    }
                                },
                                {
                                    id: 'c11',
                                    topic: '11 — "Não quero trocar meu sistema"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 11 — Apegado ao sistema atual',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: [
                                                    '"Não quero trocar meu sistema." / "Já estamos acostumados."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Perfeito. E a ideia não é necessariamente trocar tudo. Primeiro, a gente entende o fluxo atual e identifica onde a IA pode entrar para melhorar atendimento, resposta e conversão. Muitas vezes, ela complementa o processo que a empresa já tem."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Em uma reunião rápida conseguimos avaliar isso sem compromisso. Posso te mostrar amanhã?"']
                                            }
                                        ],
                                        tags: ['Integração', 'Sem troca']
                                    }
                                },
                                {
                                    id: 'c12',
                                    topic: '12 — "Estamos na correria"',
                                    style: {
                                        background: '#c2410c',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cenário 12 — Sem tempo',
                                        content: [{
                                                label: '🗣️ Prospect diz',
                                                labelColor: 'text-orange-300',
                                                lines: [
                                                    '"Agora não temos tempo para mexer com isso." / "Estamos na correria."']
                                            },
                                            {
                                                label: '💬 Resposta',
                                                labelColor: 'text-blue-400',
                                                lines: [
                                                    '"Justamente por isso pode fazer sentido olhar agora. Quando a operação está corrida, normalmente é porque a equipe está absorvendo tarefas repetitivas demais. A proposta da IA é reduzir esse peso, não criar mais trabalho."']
                                            },
                                            {
                                                label: '↳ Complemento',
                                                labelColor: 'text-yellow-400',
                                                lines: [
                                                    '"A implantação é pensada para ser adaptada ao funcionamento de vocês, com treinamento da IA conforme as regras, serviços e preferências da empresa."']
                                            },
                                            {
                                                label: '🔒 Fechamento',
                                                labelColor: 'text-green-400',
                                                lines: [
                                                    '"Podemos fazer uma reunião curta só para mapear se existe ganho real para vocês. Amanhã às [HORÁRIO] funcionaria?"']
                                            }
                                        ],
                                        tags: ['Tempo', 'Correria']
                                    }
                                }
                            ]
                        },

                        // ══════════════════════════════════════════════════════
                        //  ESQUERDA — SUPORTE
                        // ══════════════════════════════════════════════════════

                        {
                            id: 'urgencia',
                            topic: '8. Gatilhos de Urgência',
                            direction: 0,
                            style: {
                                background: '#7c2d12',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Gatilhos — Usar com elegância',
                                content: [{
                                    label: '⚠️ Atenção',
                                    labelColor: 'text-red-400',
                                    lines: [
                                        'Encaixar naturalmente na conversa. Nunca soar como pressão artificial.']
                                }],
                                tags: ['Urgência', 'Escassez']
                            },
                            children: [{
                                    id: 'urg1',
                                    topic: '📅 Agenda limitada',
                                    data: {
                                        category: 'Gatilho 1 — Escassez',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-orange-400',
                                            lines: [
                                                '"Estamos abrindo poucas demonstrações por semana justamente porque cada apresentação é personalizada para o tipo de negócio. Por isso, queria reservar um horário para vocês ainda essa semana."']
                                        }],
                                        tags: ['Escassez']
                                    }
                                },
                                {
                                    id: 'urg2',
                                    topic: '🏆 Vantagem competitiva',
                                    data: {
                                        category: 'Gatilho 2 — Primeiro mover',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-orange-400',
                                            lines: [
                                                '"Esse tipo de atendimento ainda é uma vantagem competitiva no mercado de estética e beleza. Quem estrutura isso antes tende a sair na frente, porque melhora a experiência da cliente desde o primeiro contato."']
                                        }],
                                        tags: ['Competição']
                                    }
                                },
                                {
                                    id: 'urg3',
                                    topic: '💸 Perda silenciosa',
                                    data: {
                                        category: 'Gatilho 3 — Perda invisível',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-orange-400',
                                            lines: [
                                                '"O ponto é que a perda de lead no WhatsApp quase nunca aparece como prejuízo direto. Ela simplesmente vira uma cliente que não agenda, não volta e fecha com outro lugar."']
                                        }],
                                        tags: ['Perda invisível']
                                    }
                                },
                                {
                                    id: 'urg4',
                                    topic: '🚀 Oportunidade imediata',
                                    data: {
                                        category: 'Gatilho 4 — Sem custo extra',
                                        content: [{
                                            label: '💬 Dizer',
                                            labelColor: 'text-orange-400',
                                            lines: [
                                                '"Se vocês já recebem procura pelo Instagram e WhatsApp, provavelmente já existe oportunidade para melhorar conversão sem precisar aumentar investimento em tráfego ou contratar mais gente."']
                                        }],
                                        tags: ['ROI']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'followups',
                            topic: '9. Follow-ups Pós-ligação',
                            direction: 0,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Mensagens após a ligação',
                                content: [],
                                tags: ['Follow-up']
                            },
                            children: [{
                                    id: 'fu_reuniao',
                                    topic: '✅ Com reunião agendada',
                                    style: {
                                        background: '#15803d',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Mensagem — Reunião confirmada',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-green-400',
                                            lines: [
                                                'Oi, [FULANO], tudo bem?', '',
                                                'Conforme combinamos, nossa reunião ficou agendada para [DATA], às [HORÁRIO].',
                                                '',
                                                'Na conversa, vamos te mostrar como funciona a plataforma da MM Cloud: um sistema de agendamento integrado com IA, treinado conforme o padrão de atendimento da sua empresa, para responder clientes pelo WhatsApp, organizar solicitações e conduzir contatos até o agendamento de forma rápida, humanizada e personalizada.',
                                                '',
                                                'Abaixo, segue o link da nossa reunião: [COLAR O LINK]',
                                                '',
                                                'Te vejo mais tarde/amanhã! Abraços, [SEU NOME] DA MM Cloud'
                                            ]
                                        }],
                                        tags: ['Reunião confirmada']
                                    }
                                },
                                {
                                    id: 'fu_sem',
                                    topic: '📋 Sem reunião agendada',
                                    style: {
                                        background: '#b45309',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Mensagem — Sem agendamento',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-yellow-400',
                                            lines: [
                                                'Oi, [FULANO], tudo bem?', '',
                                                'Foi um prazer falar rapidamente com você.', '',
                                                'Como comentei, a MM Cloud desenvolveu uma plataforma de agendamento integrada com IA, que responde clientes pelo WhatsApp de forma humanizada, organiza solicitações e conduz o contato até o agendamento, conforme as regras e preferências da empresa.',
                                                '',
                                                'Vou te enviar uma demonstração rápida para você visualizar como funciona na prática.',
                                                '',
                                                'Amanhã faço um contato breve para saber o que achou.',
                                                '',
                                                'Abraços, [SEU NOME] DA MM Cloud'
                                            ]
                                        }],
                                        tags: ['Sem agendamento', 'Demo']
                                    }
                                },
                                {
                                    id: 'fu1',
                                    topic: '📅 Follow-up 1 (D+1)',
                                    data: {
                                        category: 'Follow-up — Dia seguinte',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                'Oi, [FULANO], tudo bem?', '',
                                                'Conseguiu dar uma olhada na demonstração que te enviei?',
                                                '',
                                                'Fiquei curioso para saber o que achou da forma como a IA conduz o atendimento até o agendamento. Esse costuma ser justamente o ponto em que muitas clínicas e salões perdem oportunidades sem perceber.',
                                                '',
                                                'Aguardo seu retorno, [SEU NOME] DA MM Cloud'
                                            ]
                                        }],
                                        tags: ['D+1']
                                    }
                                },
                                {
                                    id: 'fu2',
                                    topic: '📅 Follow-up 2 — Reforço',
                                    data: {
                                        category: 'Follow-up — Reforço de dor',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                'Oi, [FULANO], tudo bem?', '',
                                                'Lembrei de vocês porque temos visto muitos negócios de estética e beleza com o mesmo desafio: recebem contatos pelo Instagram e WhatsApp, mas parte dessas oportunidades se perde por demora na resposta, falta de acompanhamento ou dificuldade de organizar a agenda em tempo real.',
                                                '',
                                                'Ainda faz sentido olharmos isso juntos em uma conversa rápida?',
                                                '',
                                                'Aguardo seu retorno, [SEU NOME] DA MM Cloud'
                                            ]
                                        }],
                                        tags: ['Reforço', 'Dor']
                                    }
                                },
                                {
                                    id: 'fu3',
                                    topic: '📅 Follow-up 3 — Encerramento',
                                    data: {
                                        category: 'Follow-up — Fechamento elegante',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-blue-400',
                                            lines: [
                                                'Oi, [FULANO], tudo bem?', '',
                                                'Passando só para encerrar meu contato por aqui.', '',
                                                'Se esse não for o momento ideal para vocês avaliarem uma solução de atendimento e agendamento com IA, sem problema. Mas acredito que valha conhecer quando fizer sentido.',
                                                '',
                                                'Posso te chamar mais para frente?', '',
                                                'Abraços, [SEU NOME] DA MM Cloud'
                                            ]
                                        }],
                                        tags: ['Encerramento', 'Elegante']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'instagram',
                            topic: '10. Abordagem Instagram',
                            direction: 0,
                            style: {
                                background: '#5b21b6',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Direct / Instagram — Não vender na primeira mensagem',
                                content: [],
                                tags: ['Instagram', 'Direct']
                            },
                            children: [{
                                    id: 'insta1',
                                    topic: '📱 Direct Consultivo',
                                    style: {
                                        background: '#6d28d9',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Direct 1 — Consultivo (mais elegante)',
                                        content: [{
                                                label: '📩 Mensagem',
                                                labelColor: 'text-purple-400',
                                                lines: ['Oi, tudo bem?', '',
                                                    'Conheci o perfil de vocês por aqui e vi que o Instagram parece ser um canal importante de captação para a clínica/salão.',
                                                    '',
                                                    'Hoje vocês conseguem responder rapidamente todos os contatos que chegam pelo WhatsApp e pelo direct, ou em alguns momentos isso acaba ficando corrido?'
                                                ]
                                            },
                                            {
                                                label: '↳ Se responderem',
                                                labelColor: 'text-yellow-400',
                                                lines: ['Entendi.', '',
                                                    'A MM Cloud desenvolveu uma solução de atendimento e agendamento com IA para negócios de estética e beleza.',
                                                    '',
                                                    'Na prática, a IA responde clientes pelo WhatsApp de forma humanizada, tira dúvidas, organiza solicitações e conduz o contato até o agendamento.',
                                                    '',
                                                    'Podemos marcar uma conversa rápida de 15 minutos para eu mostrar como funcionaria?'
                                                ]
                                            }
                                        ],
                                        tags: ['Consultivo', 'Elegante']
                                    }
                                },
                                {
                                    id: 'insta2',
                                    topic: '📱 Direct com Gatilho de Dor',
                                    style: {
                                        background: '#6d28d9',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Direct — Gatilho de dor',
                                        content: [{
                                                label: '📩 Mensagem',
                                                labelColor: 'text-purple-400',
                                                lines: ['Oi, tudo bem?', '',
                                                    'Uma dúvida rápida: quando uma cliente chama vocês pelo WhatsApp fora do horário comercial querendo agendar, ela recebe algum retorno automático ou só é respondida no próximo expediente?'
                                                ]
                                            },
                                            {
                                                label: '↳ Se "só no expediente"',
                                                labelColor: 'text-red-400',
                                                lines: ['Esse é justamente um dos pontos em que muitas clínicas e salões perdem oportunidades sem perceber.',
                                                    '',
                                                    'A MM Cloud criou uma IA que responde a cliente pelo WhatsApp, tira dúvidas e conduz até o agendamento, de forma humanizada e conforme o padrão da empresa.',
                                                    '',
                                                    'Posso te mostrar uma simulação rápida disso funcionando?'
                                                ]
                                            }
                                        ],
                                        tags: ['Dor', 'Fora do horário']
                                    }
                                },
                                {
                                    id: 'insta3',
                                    topic: '📱 Direct "Matador"',
                                    style: {
                                        background: '#6d28d9',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Direct — Alta curiosidade',
                                        content: [{
                                            label: '📩 Mensagem',
                                            labelColor: 'text-purple-400',
                                            lines: ['Oi, tudo bem?', '',
                                                'Vi o perfil de vocês e acredito que vocês possam estar perdendo oportunidades de agendamento sem perceber, não por falta de qualidade no serviço, mas pelo intervalo entre a mensagem da cliente e a resposta da equipe.',
                                                '',
                                                'Hoje vocês conseguem acompanhar todos os contatos que chegam pelo Instagram e WhatsApp até virarem agendamento?'
                                            ]
                                        }],
                                        tags: ['Curiosidade', 'Alto impacto']
                                    }
                                },
                                {
                                    id: 'insta_audio',
                                    topic: '🎙️ Áudio (35-45s)',
                                    style: {
                                        background: '#4c1d95',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Roteiro de Áudio — Após 1ª mensagem',
                                        content: [{
                                                label: '📌 Quando usar',
                                                labelColor: 'text-purple-400',
                                                lines: [
                                                    'Enviar logo após a primeira mensagem escrita. Máximo 35-45 segundos. Humaniza muito.']
                                            },
                                            {
                                                label: '🎙️ Roteiro',
                                                labelColor: 'text-purple-400',
                                                lines: ['"Oi, [FULANO], tudo bem? Aqui é [NOME], da MM Cloud.',
                                                    '',
                                                    'Eu conheci o perfil de vocês pelo Instagram e achei que a solução poderia fazer sentido para a clínica/salão.',
                                                    '',
                                                    'A gente desenvolveu uma IA de atendimento e agendamento que responde clientes pelo WhatsApp de forma humanizada, tira dúvidas e conduz o contato até o agendamento, inclusive fora do horário comercial.',
                                                    '',
                                                    'Queria te mostrar uma demonstração rápida, sem compromisso. Posso te enviar?"'
                                                ]
                                            },
                                            {
                                                label: '💡 Dica',
                                                labelColor: 'text-yellow-400',
                                                lines: ['Soar leve, próximo e objetivo. Não vender demais.']
                                            }
                                        ],
                                        tags: ['Áudio', '35-45s']
                                    }
                                },
                                {
                                    id: 'insta_cad',
                                    topic: '📅 Cadência (5-6 dias)',
                                    style: {
                                        background: '#4c1d95',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Cadência Instagram',
                                        content: [{
                                                label: '📅 Dia 1',
                                                labelColor: 'text-purple-400',
                                                lines: [
                                                    'Mensagem personalizada com pergunta sobre atendimento.']
                                            },
                                            {
                                                label: '📅 Dia 2',
                                                labelColor: 'text-purple-400',
                                                lines: ['Reforço com dor invisível.']
                                            },
                                            {
                                                label: '📅 Dia 3-4',
                                                labelColor: 'text-purple-400',
                                                lines: ['Oferecer demonstração prática.']
                                            },
                                            {
                                                label: '📅 Dia 5-6',
                                                labelColor: 'text-purple-400',
                                                lines: ['Fechamento elegante — não quer ser inconveniente.']
                                            }
                                        ],
                                        tags: ['Cadência', '5-6 dias']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'qualificacao_lead',
                            topic: '11. Qualificação do Lead',
                            direction: 0,
                            style: {
                                background: '#065f46',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Classificar ANTES de abordar',
                                content: [{
                                    label: '🎯 Objetivo',
                                    labelColor: 'text-green-400',
                                    lines: [
                                        'Priorizar os prospects certos para não desperdiçar tempo com leads fracos.']
                                }],
                                tags: ['Qualificação', 'Priorização']
                            },
                            children: [{
                                    id: 'lead_bom',
                                    topic: '🌟 Lead Muito Bom',
                                    style: {
                                        background: '#15803d',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Lead Muito Bom — Alta Prioridade',
                                        content: [{
                                                label: '✅ Características',
                                                labelColor: 'text-green-400',
                                                lines: ['• Instagram ativo com muito engajamento',
                                                    '• Comentários perguntando preço, horário ou disponibilidade',
                                                    '• Link para WhatsApp na bio',
                                                    '• Clínica/salão com agenda recorrente',
                                                    '• Stories frequentes',
                                                    '• Equipe com mais de uma profissional'
                                                ]
                                            },
                                            {
                                                label: '🎯 Abordagem',
                                                labelColor: 'text-yellow-400',
                                                lines: ['Mais direta, focada em conversão e escala.']
                                            }
                                        ],
                                        tags: ['Alta prioridade']
                                    }
                                },
                                {
                                    id: 'lead_medio',
                                    topic: '🟡 Lead Médio',
                                    style: {
                                        background: '#a16207',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Lead Médio — Prioridade Normal',
                                        content: [{
                                                label: '🟡 Características',
                                                labelColor: 'text-yellow-400',
                                                lines: ['• Instagram ativo, menor volume',
                                                    '• Poucos comentários',
                                                    '• Atendimento centralizado na dona',
                                                    '• Negócio em crescimento'
                                                ]
                                            },
                                            {
                                                label: '🎯 Abordagem',
                                                labelColor: 'text-yellow-400',
                                                lines: ['Focar em economia de tempo e organização.']
                                            }
                                        ],
                                        tags: ['Prioridade normal']
                                    }
                                },
                                {
                                    id: 'lead_fraco',
                                    topic: '🔴 Lead Fraco',
                                    style: {
                                        background: '#4b5563',
                                        color: '#fff'
                                    },
                                    data: {
                                        category: 'Lead Fraco — Baixa Prioridade',
                                        content: [{
                                                label: '🔴 Características',
                                                labelColor: 'text-gray-400',
                                                lines: ['• Perfil parado ou sem atividade',
                                                    '• Pouco sinal de demanda', '• Sem WhatsApp claro',
                                                    '• Operação muito pequena'
                                                ]
                                            },
                                            {
                                                label: '🎯 Abordagem',
                                                labelColor: 'text-gray-400',
                                                lines: ['Não investir muito tempo. Direct simples e seguir.']
                                            }
                                        ],
                                        tags: ['Baixa prioridade']
                                    }
                                }
                            ]
                        },

                        {
                            id: 'frases',
                            topic: '12. Frases de Alto Impacto',
                            direction: 0,
                            style: {
                                background: '#1d4ed8',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Encaixar conforme o contexto da conversa',
                                content: [{
                                    label: '💬 Frases',
                                    labelColor: 'text-blue-400',
                                    lines: [
                                        '"A cliente que chama pelo WhatsApp já está mais perto de comprar. O risco é perder esse momento por demora na resposta."',
                                        '',
                                        '"O sistema não substitui o cuidado humano. Ele garante que a primeira resposta seja rápida, padronizada e bem conduzida."',
                                        '',
                                        '"O atendimento começa antes do procedimento. A percepção de valor da cliente nasce no primeiro contato."',
                                        '',
                                        '"Muitas empresas investem em Instagram, tráfego e conteúdo, mas perdem dinheiro justamente na etapa mais simples: responder e converter o contato."',
                                        '',
                                        '"A IA não é genérica. Ela é treinada para atender como a empresa quer ser percebida."',
                                        '',
                                        '"O objetivo é transformar mensagem em oportunidade e oportunidade em agendamento."'
                                    ]
                                }],
                                tags: ['Frases', 'Alto impacto']
                            }
                        },

                        {
                            id: 'versao_curta',
                            topic: '13. Versão Ultra Curta\n(Prospect com pressa)',
                            direction: 0,
                            style: {
                                background: '#374151',
                                color: '#fff',
                                fontSize: '13',
                                fontWeight: 'bold'
                            },
                            data: {
                                category: 'Versão Ultra Curta — ~1 minuto',
                                content: [{
                                        label: '⚡ Quando usar',
                                        labelColor: 'text-gray-400',
                                        lines: ['Prospect demonstra muito pouco tempo ou pressa logo no início.']
                                    },
                                    {
                                        label: '💬 Script',
                                        labelColor: 'text-blue-400',
                                        lines: [
                                            '"Entendo que você esteja na correria, vou ser bem objetivo.', '',
                                            'A MM Cloud criou uma IA de atendimento e agendamento para clínicas e salões, que responde clientes pelo WhatsApp, tira dúvidas e conduz até o agendamento, inclusive fora do horário comercial.',
                                            '',
                                            'Ela é treinada com o padrão de atendimento da empresa, então não fica com resposta robótica.',
                                            '',
                                            'A ideia é evitar perda de oportunidade por demora, falta de padrão ou excesso de mensagens.',
                                            '',
                                            'Podemos marcar 15 minutos para eu te mostrar funcionando aplicado ao seu negócio? Amanhã às [HORÁRIO] ou [HORÁRIO]?"'
                                        ]
                                    }
                                ],
                                tags: ['Pressa', '~1 minuto']
                            }
                        }

                    ]
                }
            };

            // ────────────────────────────────────────────────────────────────────────────
            //  REGISTRY — id → dados do painel
            // ────────────────────────────────────────────────────────────────────────────
            const NODE_REGISTRY = {};

            function buildRegistry(node) {
                if (node.data) {
                    NODE_REGISTRY[node.id] = {
                        topic: node.topic,
                        category: node.data.category || '',
                        content: node.data.content || [],
                        tags: node.data.tags || []
                    };
                }
                if (node.children) node.children.forEach(buildRegistry);
            }
            buildRegistry(SCRIPT_DATA.nodeData);

            // ────────────────────────────────────────────────────────────────────────────
            //  ALPINE COMPONENT
            // ────────────────────────────────────────────────────────────────────────────
            function scriptVendas() {
                return {
                    me: null,
                    selectedNode: null,
                    zoomLevel: 1,

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                            if (window.lucide) lucide.createIcons();
                        });
                    },

                    // ── Zoom ──────────────────────────────────────────────────
                    _canvas() {
                        return document.querySelector('#me-container .map-canvas');
                    },
                    zoomIn() {
                        const c = this._canvas(); if (!c || !this.me) return;
                        this.zoomLevel = Math.min(3, (this.zoomLevel || 1) * 1.25);
                        this.me.scaleVal = this.zoomLevel;
                        c.style.transform = `scale(${this.zoomLevel})`;
                    },
                    zoomOut() {
                        const c = this._canvas(); if (!c || !this.me) return;
                        this.zoomLevel = Math.max(0.15, (this.zoomLevel || 1) * 0.8);
                        this.me.scaleVal = this.zoomLevel;
                        c.style.transform = `scale(${this.zoomLevel})`;
                    },
                    zoomReset() {
                        const c = this._canvas(); if (!c || !this.me) return;
                        this.zoomLevel = 1;
                        this.me.scaleVal = 1;
                        c.style.transform = 'scale(1)';
                        this.me.toCenter();
                    },
                    center() {
                        if (this.me) this.me.toCenter();
                    },

                    initMap() {
                        if (typeof MindElixir === 'undefined') {
                            console.error('MindElixir não carregou.');
                            return;
                        }

                        const isDark = document.documentElement.classList.contains('dark');
                        const DIRECTION = (MindElixir.SIDE !== undefined) ? MindElixir.SIDE : 2;
                        const self = this;

                        const me = new MindElixir({
                            el: '#me-container',
                            direction: DIRECTION,
                            data: SCRIPT_DATA,
                            draggable: true,
                            editable: true,
                            contextMenu: true,
                            toolBar: false,
                            nodeMenu: false,
                            keypress: true,
                            allowUndo: true,
                            theme: {
                                name: 'mmcloud',
                                palette: ['#f97316', '#1d4ed8', '#15803d', '#6d28d9', '#b91c1c'],
                                cssVar: {
                                    '--main-color':          isDark ? '#ffffff'  : '#111827',
                                    '--main-bgcolor':        isDark ? '#1e293b'  : '#e2e8f0',
                                    '--color':               isDark ? '#e2e8f0'  : '#374151',
                                    '--bgcolor':             isDark ? '#0f172a'  : '#f8fafc',
                                    '--panel-color':         isDark ? '#f1f5f9'  : '#374151',
                                    '--panel-bgcolor':       isDark ? '#1e293b'  : '#ffffff',
                                    '--panel-border-color':  isDark ? '#334155'  : '#e2e8f0',
                                    '--selected-color':      '#ffffff',
                                    '--selected-bgcolor':    '#f97316',
                                    '--root-color':          '#ffffff',
                                    '--root-bgcolor':        '#ea580c',
                                }
                            }
                        });

                        me.bus.addListener('selectNode', (nodeObj) => {
                            const reg = NODE_REGISTRY[nodeObj.id];
                            self.selectedNode = reg || null;
                            self.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                        });

                        me.init(SCRIPT_DATA);

                        // Centralizar após render
                        requestAnimationFrame(() => requestAnimationFrame(() => {
                            me.toCenter();
                            self.zoomLevel = me.scaleVal || 1;
                        }));

                        // ── Botão do meio = arrastar ──────────────────────────
                        const el = document.getElementById('me-container');
                        let pan = { on: false, x0: 0, y0: 0, sl: 0, st: 0 };

                        el.addEventListener('mousedown', e => {
                            if (e.button !== 1) return;
                            pan = { on: true, x0: e.clientX, y0: e.clientY,
                                    sl: el.scrollLeft, st: el.scrollTop };
                            el.style.cursor = 'grabbing';
                            e.preventDefault();
                        });
                        document.addEventListener('mousemove', e => {
                            if (!pan.on) return;
                            el.scrollLeft = pan.sl - (e.clientX - pan.x0);
                            el.scrollTop  = pan.st - (e.clientY - pan.y0);
                        });
                        document.addEventListener('mouseup', e => {
                            if (e.button === 1) { pan.on = false; el.style.cursor = ''; }
                        });

                        // ── Scroll do mouse = zoom ─────────────────────────────
                        el.addEventListener('wheel', e => {
                            e.preventDefault();
                            const factor = e.deltaY > 0 ? 0.9 : 1.1;
                            self.zoomLevel = Math.max(0.15, Math.min(3, (me.scaleVal || 1) * factor));
                            me.scaleVal = self.zoomLevel;
                            const c = el.querySelector('.map-canvas');
                            if (c) c.style.transform = `scale(${self.zoomLevel})`;
                        }, { passive: false });

                        this.me = me;
                    }
                };
            }
        </script>
    @endpush
@endsection
