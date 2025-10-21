<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_infos')->insert([
            [
                'service_id' => 1,
                'subtitle' => 'transforme cliques em resultados',
                'title' => 'Por que investir em uma Landing Page?',
                'description' => 'Uma Landing Page é o formato ideal para quem precisa gerar resultado rápido e mensurável, seja captar leads, divulgar um produto ou validar uma ideia.
                <br><br>
                Diferente de um site institucional, ela é direta, estratégica e centrada em conversão.
                Cada detalhe é pensado para conduzir o visitante até a ação que importa: clicar, preencher, comprar ou entrar em contato.
                <br><br>
                Se o seu objetivo é vender mais, captar contatos ou dar destaque a uma campanha, esse é o serviço certo.
                A Landing Page da <strong>MM Criativos</strong> combina design de impacto, copy estratégica e performance técnica, tudo voltado para o que realmente interessa: converter visitantes em clientes.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'subtitle' => 'tudo no lugar certo',
                'title' => 'Por que escolher uma Single Page?',
                'description' => 'Uma Single Page é o formato ideal para quem busca apresentar sua marca, serviço ou produto de forma clara, fluida e impactante, tudo em uma única página.
                Ela oferece uma experiência de navegação contínua, em que cada rolagem conduz o visitante por uma história visual bem estruturada, sem distrações.
                <br><br>
                É a escolha perfeita para portfólios, perfis profissionais, lançamentos e pequenas empresas que desejam unir agilidade, design moderno e conteúdo direto ao ponto.
                <br><br>
                As Single Pages da <strong>MM Criativos</strong> são projetadas com foco em identidade visual, narrativa coerente e performance otimizada, entregando uma presença digital leve, objetiva e memorável.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3,
                'subtitle' => 'presença completa e expansível',
                'title' => 'Por que escolher um Site Multipage?',
                'description' => 'Um Site Multipage é a escolha ideal para marcas e empresas que precisam de uma presença digital robusta, com páginas dedicadas a cada parte da sua comunicação — produtos, serviços, equipe, contato e muito mais.
                <br><br>
                Diferente da Single Page, ele permite aprofundar informações, trabalhar estratégias de SEO com mais precisão e criar jornadas de navegação detalhadas, conduzindo o usuário por diferentes seções de forma lógica e envolvente.
                <br><br>
                É o formato indicado para negócios em crescimento, que buscam consolidar autoridade, melhorar posicionamento no Google e oferecer uma experiência completa a seus visitantes.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'subtitle' => 'autonomia e expansão contínua',
                'title' => 'Por que escolher um Portal?',
                'description' => 'Um <strong>Portal</strong> é o formato ideal para quem precisa de um site com estrutura profissional e liberdade para atualizar o conteúdo sempre que quiser.
                <br><br>
                Ele une o design e a experiência de um site tradicional com o poder de um painel administrativo, permitindo que você adicione novos produtos, projetos, artigos ou coleções com facilidade — sem depender de suporte técnico.
                <br><br>
                Diferente de um sistema corporativo, é leve, intuitivo e feito sob medida para negócios criativos, lojas especializadas, estúdios e profissionais que desejam autonomia, performance e crescimento contínuo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'subtitle' => 'eficiência que impulsiona o seu negócio',
                'title' => 'Por que escolher um Sistema Empresarial?',
                'description' => 'Um <strong>Sistema Empresarial</strong> é a solução ideal para quem precisa otimizar tarefas, centralizar processos e ganhar tempo na operação do dia a dia.
                <br><br>
                Ele conecta setores, automatiza rotinas e organiza informações em um só lugar, reduzindo erros e aumentando a produtividade da equipe.
                <br><br>
                O sistema se adapta à sua realidade e cresce junto com o seu negócio, oferecendo controle total, relatórios personalizados e integrações inteligentes para um fluxo de trabalho muito mais eficiente.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'subtitle' => 'tecnologia que escala o seu negócio',
                'title' => 'Por que investir em um SaaS e Integrações?',
                'description' => 'Um <strong>SaaS</strong> é o modelo ideal para quem deseja transformar uma ideia em um produto digital real, acessível e escalável.
                <br><br>
                Ele permite que sua solução funcione online, com login, painéis, planos e recursos automatizados, tudo disponível para múltiplos usuários simultaneamente.
                <br><br>
                O sistema se conecta a APIs, automações e serviços externos, criando um ecossistema que trabalha por você.
                <br><br>
                É a escolha certa para startups, empresas de tecnologia e negócios que querem transformar processos em produtos e gerar receita recorrente.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
