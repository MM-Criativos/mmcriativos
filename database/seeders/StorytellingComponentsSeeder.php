<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorytellingComponentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('storytelling_components')->insert([

            // 🟧 IDENTIDADE
            [
                'name' => 'Hero Header',
                'slug' => 'hero-header',
                'layer' => 'identidade',
                'component_type' => 'blade',
                'description' => 'Bloco principal de abertura, com vídeo ou imagem de destaque, título e chamada inicial.',
                'props' => json_encode([
                    'title' => 'Transformamos ideias em código',
                    'subtitle' => 'Design, tecnologia e estratégia reunidos para impulsionar seu negócio.',
                    'cta_text' => 'Começar projeto'
                ]),
            ],
            [
                'name' => 'Intro Manifesto',
                'slug' => 'intro-manifesto',
                'layer' => 'identidade',
                'component_type' => 'blade',
                'description' => 'Apresenta a essência da marca em uma frase de impacto com breve texto de apoio.',
                'props' => json_encode([
                    'headline' => 'Criar é a nossa forma de viver.',
                    'text' => 'A MM Criativos acredita em transformar visão em presença digital com propósito.'
                ]),
            ],
            [
                'name' => 'Logo Showcase',
                'slug' => 'logo-showcase',
                'layer' => 'identidade',
                'component_type' => 'vue',
                'description' => 'Carrossel animado com logotipos ou símbolos da marca.',
                'props' => json_encode(['logos' => []]),
            ],
            [
                'name' => 'About Summary',
                'slug' => 'about-summary',
                'layer' => 'identidade',
                'component_type' => 'blade',
                'description' => 'Resumo da história ou missão da marca, usado em seções “Sobre nós”.',
                'props' => json_encode(['paragraph' => 'Fundada para unir estética e performance em cada linha de código.']),
            ],
            [
                'name' => 'Value Highlights',
                'slug' => 'value-highlights',
                'layer' => 'identidade',
                'component_type' => 'vue',
                'description' => 'Apresenta valores centrais em ícones e palavras-chave.',
                'props' => json_encode([
                    'values' => ['Inovação', 'Transparência', 'Excelência']
                ]),
            ],

            // 🟠 PROPÓSITO
            [
                'name' => 'Mission Statement',
                'slug' => 'mission-statement',
                'layer' => 'proposito',
                'component_type' => 'blade',
                'description' => 'Bloco textual apresentando missão e visão da empresa.',
                'props' => json_encode([
                    'mission' => 'Transformar criatividade em resultados tangíveis.',
                    'vision' => 'Ser referência em soluções digitais criativas e escaláveis.'
                ]),
            ],
            [
                'name' => 'Timeline Story',
                'slug' => 'timeline-story',
                'layer' => 'proposito',
                'component_type' => 'vue',
                'description' => 'Linha do tempo mostrando marcos e evolução da marca.',
                'props' => json_encode(['events' => []]),
            ],
            [
                'name' => 'Founder Quote',
                'slug' => 'founder-quote',
                'layer' => 'proposito',
                'component_type' => 'blade',
                'description' => 'Citação inspiradora com foto e nome do fundador ou líder da marca.',
                'props' => json_encode([
                    'quote' => 'Ideias só ganham vida quando encontram propósito.',
                    'author' => 'Marcus Multini'
                ]),
            ],
            [
                'name' => 'Purpose Grid',
                'slug' => 'purpose-grid',
                'layer' => 'proposito',
                'component_type' => 'vue',
                'description' => 'Grade visual que representa pilares do propósito da empresa.',
                'props' => json_encode(['items' => ['Criar', 'Conectar', 'Evoluir']]),
            ],

            // 🟡 PROVA DE VALOR
            [
                'name' => 'Service Grid',
                'slug' => 'service-grid',
                'layer' => 'prova_de_valor',
                'component_type' => 'vue',
                'description' => 'Grade responsiva exibindo serviços com ícones e descrições curtas.',
                'props' => json_encode(['columns' => 3]),
            ],
            [
                'name' => 'Feature List',
                'slug' => 'feature-list',
                'layer' => 'prova_de_valor',
                'component_type' => 'blade',
                'description' => 'Lista de recursos e funcionalidades principais com ícones.',
                'props' => json_encode(['features' => []]),
            ],
            [
                'name' => 'Comparison Table',
                'slug' => 'comparison-table',
                'layer' => 'prova_de_valor',
                'component_type' => 'vue',
                'description' => 'Tabela comparativa entre planos, serviços ou produtos.',
                'props' => json_encode(['columns' => ['Básico', 'Pro', 'Premium']]),
            ],
            [
                'name' => 'Interactive Demo',
                'slug' => 'interactive-demo',
                'layer' => 'prova_de_valor',
                'component_type' => 'vue',
                'description' => 'Bloco interativo demonstrando produto ou sistema em ação.',
                'props' => json_encode(['demo_url' => null]),
            ],
            [
                'name' => 'Tech Stack',
                'slug' => 'tech-stack',
                'layer' => 'prova_de_valor',
                'component_type' => 'blade',
                'description' => 'Mostra tecnologias usadas nos projetos (ícones animados ou grade).',
                'props' => json_encode(['stacks' => ['Laravel', 'Vue', 'Tailwind', 'GSAP']]),
            ],

            // 🟢 VALIDAÇÃO
            [
                'name' => 'Case Showcase',
                'slug' => 'case-showcase',
                'layer' => 'validacao',
                'component_type' => 'blade',
                'description' => 'Exibe cases e projetos realizados com resultados mensuráveis.',
                'props' => json_encode(['limit' => 6]),
            ],
            [
                'name' => 'Testimonial Slider',
                'slug' => 'testimonial-slider',
                'layer' => 'validacao',
                'component_type' => 'vue',
                'description' => 'Carrossel de depoimentos com nome, foto e citação de clientes.',
                'props' => json_encode(['autoplay' => true]),
            ],
            [
                'name' => 'Client Logos',
                'slug' => 'client-logos',
                'layer' => 'validacao',
                'component_type' => 'blade',
                'description' => 'Apresenta logos de clientes e parceiros em carrossel horizontal.',
                'props' => json_encode(['count' => 10]),
            ],
            [
                'name' => 'Metrics Counter',
                'slug' => 'metrics-counter',
                'layer' => 'validacao',
                'component_type' => 'vue',
                'description' => 'Bloco de estatísticas animadas, como número de projetos e clientes.',
                'props' => json_encode(['metrics' => ['Projetos' => 120, 'Clientes' => 80]]),
            ],
            [
                'name' => 'Awards Gallery',
                'slug' => 'awards-gallery',
                'layer' => 'validacao',
                'component_type' => 'blade',
                'description' => 'Galeria de prêmios, selos e certificações recebidas.',
                'props' => json_encode(['awards' => []]),
            ],

            // 🔵 CONVERSÃO
            [
                'name' => 'CTA Block',
                'slug' => 'cta-block',
                'layer' => 'conversao',
                'component_type' => 'blade',
                'description' => 'Bloco de chamada à ação com botão e frase persuasiva.',
                'props' => json_encode([
                    'title' => 'Pronto para começar?',
                    'button_text' => 'Solicitar orçamento'
                ]),
            ],
            [
                'name' => 'Contact Form',
                'slug' => 'contact-form',
                'layer' => 'conversao',
                'component_type' => 'blade',
                'description' => 'Formulário de contato com campos básicos.',
                'props' => json_encode(['fields' => ['nome', 'email', 'mensagem']]),
            ],
            [
                'name' => 'Newsletter Signup',
                'slug' => 'newsletter-signup',
                'layer' => 'conversao',
                'component_type' => 'vue',
                'description' => 'Bloco para captura de leads com campo de e-mail e botão de inscrição.',
                'props' => json_encode(['placeholder' => 'Seu e-mail']),
            ],
            [
                'name' => 'Pricing CTA',
                'slug' => 'pricing-cta',
                'layer' => 'conversao',
                'component_type' => 'blade',
                'description' => 'Chamada à ação dentro da página de preços ou planos.',
                'props' => json_encode([
                    'cta_text' => 'Escolha o plano ideal e comece agora.'
                ]),
            ],
            [
                'name' => 'FAQ Accordion',
                'slug' => 'faq-accordion',
                'layer' => 'conversao',
                'component_type' => 'vue',
                'description' => 'Seção de perguntas frequentes com interação de abrir e fechar.',
                'props' => json_encode(['items' => []]),
            ],
        ]);
    }
}
