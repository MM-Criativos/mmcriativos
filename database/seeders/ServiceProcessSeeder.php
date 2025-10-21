<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_processes')->insert([
            // ------------------------------
            // SERVICE 1 - Landing Page
            // ------------------------------
            [
                'service_id' => 1,
                'order' => 1,
                'title' => 'Imersão Estratégica',
                'description' => 'Entendemos o seu produto, público e objetivos para definir uma direção clara e eficaz de comunicação.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'order' => 2,
                'title' => 'Direção Visual',
                'description' => 'Criamos um conceito visual único, alinhado à sua identidade e voltado à conversão do visitante em ação.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'order' => 3,
                'title' => 'Lançamento e Performance',
                'description' => 'Publicamos, configuramos as integrações e otimizamos a página para garantir resultados desde o primeiro dia.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ------------------------------
            // SERVICE 2 - Site Single Page
            // ------------------------------
            [
                'service_id' => 2,
                'order' => 1,
                'title' => 'Imersão de Marca',
                'description' => 'Coletamos e refinamos informações sobre sua marca, história e valores para construir uma base sólida de identidade.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'order' => 2,
                'title' => 'Construção Visual',
                'description' => 'Desenvolvemos uma estrutura única que apresenta a essência da marca e seus diferenciais com clareza e impacto.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'order' => 3,
                'title' => 'Publicação Estruturada',
                'description' => 'Realizamos a publicação com integrações técnicas e SEO base, garantindo uma presença digital sólida e duradoura.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ------------------------------
            // SERVICE 3 - Site Multi Page
            // ------------------------------
            [
                'service_id' => 3,
                'order' => 1,
                'title' => 'Diagnóstico e Planejamento',
                'description' => 'Analisamos marca, público e objetivos para definir estrutura e propósito de cada página.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3,
                'order' => 2,
                'title' => 'Arquitetura e Conteúdo',
                'description' => 'Mapa do site, organização de seções e profundidade de comunicação.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3,
                'order' => 3,
                'title' => 'Design e Desenvolvimento',
                'description' => 'Interfaces únicas com tecnologias atuais para navegação fluida e responsiva.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3,
                'order' => 4,
                'title' => 'Publicação e Otimização',
                'description' => 'SEO avançado, integrações e acompanhamento técnico de performance.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ------------------------------
            // SERVICE 4 - Portal / Plataforma
            // ------------------------------
            [
                'service_id' => 4,
                'order' => 1,
                'title' => 'Mapeamento de Necessidades',
                'description' => 'Identificamos o que sua marca precisa gerenciar e como cada parte do conteúdo se conecta.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'order' => 2,
                'title' => 'Arquitetura do Portal',
                'description' => 'Estruturamos o painel administrativo, definindo fluxos, áreas e cadastros essenciais.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'order' => 3,
                'title' => 'Design de Interface',
                'description' => 'Criamos o visual do portal, garantindo harmonia entre estética, clareza e usabilidade.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'order' => 4,
                'title' => 'Desenvolvimento Funcional',
                'description' => 'Implementamos os recursos dinâmicos, integrações e cadastros do painel administrativo.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'order' => 5,
                'title' => 'Implantação e Treinamento',
                'description' => 'Publicamos o portal, configuramos integrações e treinamos você para total autonomia.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ------------------------------
            // SERVICE 5 - Sistema Empresarial
            // ------------------------------
            [
                'service_id' => 5,
                'order' => 1,
                'title' => 'Diagnóstico e Mapeamento',
                'description' => 'Entendemos os fluxos e desafios do negócio para definir o que o sistema precisa resolver.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 2,
                'title' => 'Planejamento de Módulos',
                'description' => 'Definimos as áreas, permissões e funcionalidades de cada módulo com base na rotina da empresa.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 3,
                'title' => 'Arquitetura Técnica',
                'description' => 'Estruturamos banco de dados, rotas e integrações que sustentam o funcionamento do sistema.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 4,
                'title' => 'Design de Interface',
                'description' => 'Criamos interfaces claras e funcionais que facilitam o uso diário pelos colaboradores.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 5,
                'title' => 'Desenvolvimento e Integrações',
                'description' => 'Implementamos os módulos, APIs e automações que conectam o sistema ao seu ecossistema digital.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 6,
                'title' => 'Validação e Treinamento',
                'description' => 'Realizamos testes práticos e treinamos a equipe para garantir uso eficiente e autônomo.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'order' => 7,
                'title' => 'Monitoramento e Evolução',
                'description' => 'Acompanhamos métricas e feedbacks para aprimorar recursos e garantir performance contínua.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ------------------------------
            // SERVICE 6 - SaaS
            // ------------------------------
            [
                'service_id' => 6,
                'order' => 1,
                'title' => 'Descoberta e Validação',
                'description' => 'Entendemos o propósito do produto, público e diferenciais para validar a proposta de valor do SaaS.',
                'image' => 'assets/images/resources/work-process-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 2,
                'title' => 'Mapeamento de Fluxos',
                'description' => 'Desenhamos as jornadas de usuário e o fluxo de dados para definir uma experiência clara e funcional.',
                'image' => 'assets/images/resources/work-process-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 3,
                'title' => 'Modelagem do Produto',
                'description' => 'Estruturamos módulos, planos, níveis de acesso e estratégias de monetização do sistema SaaS.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 4,
                'title' => 'Arquitetura Técnica',
                'description' => 'Definimos o ambiente multitenant, banco de dados e integrações que sustentam o funcionamento do SaaS.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 5,
                'title' => 'Design de Interface',
                'description' => 'Criamos uma interface intuitiva e responsiva que une estética, usabilidade e conversão para o usuário final.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 6,
                'title' => 'Desenvolvimento e Integrações',
                'description' => 'Implementamos funcionalidades, APIs e automações que conectam o sistema a serviços externos e internos.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 7,
                'title' => 'Testes e Segurança',
                'description' => 'Realizamos testes de performance, autenticação e integridade para garantir confiabilidade total da plataforma.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 8,
                'title' => 'Deploy e Onboarding',
                'description' => 'Publicamos o sistema, configuramos o ambiente em nuvem e criamos um onboarding otimizado para novos usuários.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'order' => 9,
                'title' => 'Evolução Contínua',
                'description' => 'Monitoramos métricas, feedbacks e uso real para aprimorar recursos, desempenho e novas integrações.',
                'image' => 'assets/images/resources/work-process-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
