<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtrasSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('extras')->insert([

            // ============================================================
            // 🔹 INFRAESTRUTURA
            // ============================================================
            [
                'name' => 'Hospedagem anual',
                'description' => 'Servidor dedicado ou cloud para manter o site ou sistema online.',
                'price' => 600,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Deploy e configuração',
                'description' => 'Publicação e configuração do projeto em ambiente de produção.',
                'price' => 300,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Backup automatizado',
                'description' => 'Configuração de backup diário e armazenamento em nuvem.',
                'price' => 300,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'E-mails corporativos',
                'description' => 'Criação e hospedagem de contas @suaempresa.com.br com configuração de DNS e suporte técnico.',
                'price' => 250,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Certificado SSL',
                'description' => 'Instalação e configuração de certificado SSL para navegação segura (https).',
                'price' => 200,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Registro de domínio',
                'description' => 'Registro e configuração de domínio personalizado (ex: www.suaempresa.com.br).',
                'price' => 120,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Infraestrutura',
                'is_active' => true,
                'sort' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 🎨 DESIGN & UX
            // ============================================================
            [
                'name' => 'Animações e microinterações',
                'description' => 'Aplicação de efeitos GSAP, Lottie e transições suaves para uma experiência premium.',
                'price' => 600,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Design & UX',
                'is_active' => true,
                'sort' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Versões A/B Test',
                'description' => 'Criação de variações de layout e textos para otimizar conversão de visitantes.',
                'price' => 400,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Design & UX',
                'is_active' => true,
                'sort' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Design responsivo premium',
                'description' => 'Aprimoramento visual e adaptação minuciosa para tablets e smartphones.',
                'price' => 500,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Design & UX',
                'is_active' => true,
                'sort' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 🔍 CONTEÚDO & SEO
            // ============================================================
            [
                'name' => 'SEO Avançado',
                'description' => 'Configuração de sitemap, schema, metatags e otimização técnica.',
                'price' => 500,
                'default_discount' => 100,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Conteúdo & SEO',
                'is_active' => true,
                'sort' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Copywriting estratégico',
                'description' => 'Criação de textos otimizados para conversão e SEO.',
                'price' => 400,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Conteúdo & SEO',
                'is_active' => true,
                'sort' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tradução multilíngue',
                'description' => 'Adaptação completa do site ou sistema para outro idioma.',
                'price' => 600,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Conteúdo & SEO',
                'is_active' => true,
                'sort' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 🔗 INTEGRAÇÕES & AUTOMAÇÃO
            // ============================================================
            [
                'name' => 'Integração com API externa',
                'description' => 'Integração com sistemas terceiros como RD Station, Hubspot ou ERPs.',
                'price' => 800,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Integrações & Automação',
                'is_active' => true,
                'sort' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Automação via n8n ou Zapier',
                'description' => 'Criação de fluxos automatizados de e-mails, leads e tarefas integradas.',
                'price' => 700,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Integrações & Automação',
                'is_active' => true,
                'sort' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Login social (Google/LinkedIn)',
                'description' => 'Implementação de autenticação por redes sociais.',
                'price' => 400,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Integrações & Automação',
                'is_active' => true,
                'sort' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 🧮 RELATÓRIOS & BI
            // ============================================================
            [
                'name' => 'Dashboard analítico',
                'description' => 'Painel com gráficos interativos e KPIs personalizados.',
                'price' => 1200,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Relatórios & BI',
                'is_active' => true,
                'sort' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Integração com Google Data Studio ou Power BI',
                'description' => 'Conexão de dados e relatórios dinâmicos com plataformas externas.',
                'price' => 800,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Relatórios & BI',
                'is_active' => true,
                'sort' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 💳 COMERCIAL & SAAS
            // ============================================================
            [
                'name' => 'Integração com gateway de pagamento',
                'description' => 'Configuração de pagamentos automáticos e planos de assinatura.',
                'price' => 1500,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Comercial & SaaS',
                'is_active' => true,
                'sort' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Domínio customizado (CNAME)',
                'description' => 'Configuração de domínio próprio por cliente (ex: app.empresa.com).',
                'price' => 500,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'yearly',
                'category' => 'Comercial & SaaS',
                'is_active' => true,
                'sort' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'White Label completo',
                'description' => 'Personalização de marca, logotipo e domínio para cada cliente.',
                'price' => 2500,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Comercial & SaaS',
                'is_active' => true,
                'sort' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================================
            // 🤝 SUPORTE
            // ============================================================
            [
                'name' => 'Suporte mensal',
                'description' => 'Manutenção, correções e pequenas melhorias contínuas.',
                'price' => 300,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'monthly',
                'category' => 'Suporte',
                'is_active' => true,
                'sort' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Treinamento do cliente',
                'description' => 'Sessão de treinamento para uso do painel ou CMS.',
                'price' => 400,
                'default_discount' => 0,
                'price_type' => 'fixed',
                'billing_period' => 'one_time',
                'category' => 'Suporte',
                'is_active' => true,
                'sort' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
