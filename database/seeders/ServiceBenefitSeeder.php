<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceBenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_benefits')->insert([

            // 🔹 Service 1 - Landing Page
            ['service_id' => 1, 'title' => 'Alta Conversão', 'subtitle' => 'Estrutura pensada para transformar cliques em resultados reais.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Design Estratégico', 'subtitle' => 'Visual moderno criado para guiar o usuário até a ação principal.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Performance Rápida', 'subtitle' => 'Carregamento rápido para maior retenção do usuário.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Experiência Clara', 'subtitle' => 'Navegação intuitiva e conteúdo direto ao ponto.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 2 - Site Single Page
            ['service_id' => 2, 'title' => 'Design Linear', 'subtitle' => 'Layout fluido que conduz o visitante por uma jornada contínua e envolvente.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Fluxo Intuitivo', 'subtitle' => 'Navegação simples e funcional, com seções conectadas de forma natural.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Identidade Focada', 'subtitle' => 'Cada elemento visual comunica sua essência com clareza e propósito.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Performance Leve', 'subtitle' => 'Código otimizado e rápido, garantindo carregamento fluido em qualquer tela.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 3 - Site Multipage
            ['service_id' => 3, 'title' => 'Presença Estruturada', 'subtitle' => 'Organiza suas informações em páginas dedicadas e aprofunda sua história.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Expansão Contínua', 'subtitle' => 'Estrutura flexível que cresce junto com o seu negócio, pronta para novos conteúdos e seções.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Autoridade Digital', 'subtitle' => 'Aprofunda sua comunicação, fortalecendo o posicionamento da marca no mercado.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Experiência Completa', 'subtitle' => 'Melhor maneira de apresentar sua marca e seus produtos ao público.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 4 - Portal
            ['service_id' => 4, 'title' => 'Presença Estruturada', 'subtitle' => 'Organização clara do conteúdo em páginas únicas que reforçam a credibilidade da sua marca.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Expansão Contínua', 'subtitle' => 'Estrutura adaptável que permite adicionar novas seções, categorias e funcionalidades.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Autonomia Total', 'subtitle' => 'Painel administrativo intuitivo para cadastrar, editar e atualizar conteúdos.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Experiência Completa', 'subtitle' => 'Oferece ao visitante uma jornada imersiva, com páginas que se conectam.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 5 - Sistema Empresarial
            ['service_id' => 5, 'title' => 'Automação Inteligente', 'subtitle' => 'Reduz tarefas manuais com processos automatizados que otimizam tempo e eficiência.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Gestão Centralizada', 'subtitle' => 'Controle informações, pedidos e relatórios em um único painel, de forma clara e organizada.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Produtividade Escalada', 'subtitle' => 'Ganhe velocidade nas entregas e libere sua equipe para focar no que realmente gera resultado.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Controle Estratégico', 'subtitle' => 'Acompanhe dados em tempo real e tome decisões baseadas em métricas.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 6 - SaaS e Integrações
            ['service_id' => 6, 'title' => 'Escalabilidade Ilimitada', 'subtitle' => 'Cresça com sistemas preparados para múltiplos usuários e empresas.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Integrações Inteligentes', 'subtitle' => 'Conecte o SaaS a APIs e automações externas para manter um fluxo digital eficiente.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Monetização Sustentável', 'subtitle' => 'Gere receita recorrente com planos, assinaturas e controle de acesso.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Operação Automatizada', 'subtitle' => 'Elimine tarefas repetitivas com rotinas automáticas para processos e tarefas.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
