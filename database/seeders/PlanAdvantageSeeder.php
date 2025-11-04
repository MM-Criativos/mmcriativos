<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanAdvantageSeeder extends Seeder
{
    public function run(): void
    {
        $advantages = [
            'Landing Page' => [
                'Design direcionado à conversão',
                'Navegação fluida e suave',
                'Storytelling estruturado e CTA estratégico',
                'Integração com WhatsApp, formulários e redes sociais',
                'SEO básico e carregamento otimizado',
            ],
            'Site Single Page' => [
                'Design moderno e responsivo',
                'Navegação fluida e suave',
                'Integração com WhatsApp, formulários e redes sociais',
                'SEO técnico leve e otimização de performance',
                'Hospedagem + domínio configurado',
            ],
            'Site MultiPage' => [
                'Conteúdo dividido em páginas',
                'Conteúdo mais aprofundado',
                'SEO avançado com sitemap e URLs otimizadas',
                'Integração com WhatsApp, formulários e redes sociais',
                'Otimização para navegação fluida entre páginas',
            ],
            'Portal' => [ // Plataforma Básica
                'Painel administrativo',
                'Site público e dashboard do administrador',
                'Banco de dados integrado',
                'Controle de tarefas e gerenciamento de conteúdo',
                'Layout exclusivo para camada administrativa',
            ],
            'Sistema Personalizado' => [ // Sistema Empresarial
                'Desenvolvimento de soluções personalizadas',
                'Automação de processos e tarefas repetitivas',
                'Painéis otimizados com métricas inteligentes e estratégicas',
                'Relatórios dinâmicos',
                'Integração com APIs',
            ],
            'SaaS e Integrações' => [
                'Autenticação multiusuário e gerenciamento de permissões',
                'Integração com APIs, automações e gateways de pagamento',
                'Infraestrutura otimizada com hospedagem dedicada',
                'Atualizações contínuas',
            ],
        ];

        foreach ($advantages as $serviceName => $items) {
            $plan = Plan::whereHas('service', fn($q) => $q->where('name', $serviceName))->first();

            if (!$plan) {
                $this->command->warn("Plano não encontrado para: {$serviceName}");
                continue;
            }

            $plan->advantages()->delete(); // limpa vantagens antigas

            foreach ($items as $text) {
                $plan->advantages()->create(['title' => $text]);
            }
        }
    }
}
