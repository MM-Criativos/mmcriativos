<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_features')->insert([

            // 🔹 Service 1 - Landing Page
            ['service_id' => 1, 'title' => 'Desempenho Otimizado', 'subtitle' => 'Velocidade e estabilidade em todas as telas, garantindo navegação fluida e sem travamentos.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Storytelling de Conversão', 'subtitle' => 'Cada seção da página é planejada como uma narrativa visual que conduz o visitante à ação.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Integrações Inteligentes', 'subtitle' => 'Conecte com Google Analytics, Meta Pixel, WhatsApp e automações para medir resultados reais.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Pronta para Campanhas', 'subtitle' => 'Implantação rápida e ideal para lançamentos, captações e campanhas temporárias.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 1, 'title' => 'Otimização Contínua', 'subtitle' => 'Aprimoramento contínuo com base em métricas reais, garantindo que ela performe mesmo após o lançamento.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 2 - Site Single Page
            ['service_id' => 2, 'title' => 'Apresentação de Marca', 'subtitle' => 'Estrutura contínua e envolvente que apresenta a identidade, história e proposta de valor da sua marca.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Conteúdo Direcionado', 'subtitle' => 'Cada seção é construída com propósito, destacando informações essenciais e guiando o visitante com clareza.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Estrutura Simplificada', 'subtitle' => 'Menos páginas, mais objetividade. Tudo o que importa em uma única navegação, sem excessos ou distrações.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Gestão Prática', 'subtitle' => 'Fácil de atualizar e manter, permitindo ajustes rápidos de conteúdo e visual sem depender de grandes revisões.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 2, 'title' => 'Escalabilidade Visual', 'subtitle' => 'Pode evoluir com sua marca, recebendo novas seções, elementos e recursos sem perder leveza ou consistência.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 3 - Site Multipage
            ['service_id' => 3, 'title' => 'Arquitetura Modular', 'subtitle' => 'Cada página tem função própria, criando um ecossistema digital coeso e fácil de navegar.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'SEO Avançado', 'subtitle' => 'Estrutura otimizada para buscadores, com páginas indexáveis e conteúdo estratégico para ranqueamento.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Gestão Escalável', 'subtitle' => 'Permite incluir novos produtos, serviços e áreas do site sem comprometer performance ou design.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Conteúdo Profundo', 'subtitle' => 'Espaço para explorar detalhes sobre sua empresa, cases e diferenciais com clareza e propósito.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 3, 'title' => 'Integração Total', 'subtitle' => 'Suporte a ferramentas de automação, análise e contato, criando um fluxo completo de comunicação digital.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 4 - Portal
            ['service_id' => 4, 'title' => 'Arquitetura Modular', 'subtitle' => 'Cada página tem função própria, criando um ecossistema digital organizado, intuitivo e escalável.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'SEO Avançado', 'subtitle' => 'Estrutura otimizada para buscadores, com URLs amigáveis, metadados e hierarquia de conteúdo.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Gestão Escalável', 'subtitle' => 'Painel administrativo leve e adaptável que cresce junto com o volume de dados e conteúdos do site.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Conteúdo Profundo', 'subtitle' => 'Permite explorar detalhes, projetos e histórias da marca com maior densidade e narrativa envolvente.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 4, 'title' => 'Integração Total', 'subtitle' => 'Conecte formulários, automações, analytics e APIs externas para centralizar a gestão digital.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 5 - Sistema Empresarial
            ['service_id' => 5, 'title' => 'Fluxos Automatizados', 'subtitle' => 'Criação de rotinas automáticas para cadastros, relatórios e comunicações internas.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Controle Personalizado', 'subtitle' => 'Cada módulo é moldado às operações da sua empresa, priorizando agilidade e praticidade.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Segurança de Dados', 'subtitle' => 'Armazenamento seguro, autenticação de usuários e camadas extras de proteção de informações.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Integrações Corporativas', 'subtitle' => 'Conecte o sistema a ERPs, CRMs, gateways e APIs externas para uma gestão totalmente unificada.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 5, 'title' => 'Escalabilidade Técnica', 'subtitle' => 'Estrutura preparada para receber novos usuários, módulos e dados sem perda de desempenho.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],

            // 🔹 Service 6 - SaaS e Integrações
            ['service_id' => 6, 'title' => 'Arquitetura Multitenant', 'subtitle' => 'Permite que múltiplos clientes usem o mesmo sistema com segurança, dados isolados e gestão independente.', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'APIs Conectadas', 'subtitle' => 'Integração com gateways, CRMs, automações e serviços externos para maximizar o potencial do SaaS.', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Painel Inteligente', 'subtitle' => 'Administração centralizada com métricas, relatórios e insights de performance em tempo real.', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Segurança Avançada', 'subtitle' => 'Controle de acesso, autenticação, criptografia e monitoramento contínuo para proteção total de dados.', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['service_id' => 6, 'title' => 'Infraestrutura Escalável', 'subtitle' => 'Sistema preparado para expansão constante, suportando alto volume de usuários e integrações simultâneas.', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
