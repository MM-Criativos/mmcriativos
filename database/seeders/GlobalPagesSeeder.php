<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalPagesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('global_pages')->insert([
            // 🟧 IDENTIDADE
            [
                'name' => 'Home',
                'slug' => 'home',
                'service_id' => 1, // usado em todos
                'description' => 'Página inicial. Apresenta identidade visual, proposta de valor e chamada principal.',
                'meta' => json_encode(['layout' => 'hero+proof+cta', 'primary_layer' => 'identidade']),
            ],
            [
                'name' => 'Sobre',
                'slug' => 'sobre',
                'service_id' => 1,
                'description' => 'Página institucional que narra a história, missão, visão e valores da marca.',
                'meta' => json_encode(['layout' => 'timeline+statement', 'primary_layer' => 'proposito']),
            ],
            [
                'name' => 'Equipe',
                'slug' => 'equipe',
                'service_id' => 3,
                'description' => 'Apresenta o time, suas funções e cultura de trabalho.',
                'meta' => json_encode(['layout' => 'cards+bio', 'primary_layer' => 'identidade']),
            ],
            [
                'name' => 'Carreiras',
                'slug' => 'carreiras',
                'service_id' => 5,
                'description' => 'Divulga oportunidades e apresenta a cultura da empresa para novos talentos.',
                'meta' => json_encode(['layout' => 'list+cta', 'primary_layer' => 'identidade']),
            ],

            // 🟠 PROPÓSITO
            [
                'name' => 'Missão & Valores',
                'slug' => 'missao-valores',
                'service_id' => 3,
                'description' => 'Expande o propósito e valores centrais da marca, com storytelling visual.',
                'meta' => json_encode(['layout' => 'statement+visual', 'primary_layer' => 'proposito']),
            ],
            [
                'name' => 'Manifesto Visual',
                'slug' => 'manifesto-visual',
                'service_id' => 2,
                'description' => 'Bloco criativo que une design, propósito e narrativa da marca.',
                'meta' => json_encode(['layout' => 'video+text', 'primary_layer' => 'proposito']),
            ],

            // 🟡 PROVA DE VALOR
            [
                'name' => 'Serviços',
                'slug' => 'servicos',
                'service_id' => 1,
                'description' => 'Apresenta o que a marca oferece e como entrega valor ao cliente.',
                'meta' => json_encode(['layout' => 'grid+feature', 'primary_layer' => 'prova_de_valor']),
            ],
            [
                'name' => 'Detalhe do Serviço',
                'slug' => 'servico-detalhe',
                'service_id' => 3,
                'description' => 'Página individual detalhando cada serviço com foco em benefícios e diferenciais.',
                'meta' => json_encode(['layout' => 'hero+details', 'primary_layer' => 'prova_de_valor']),
            ],
            [
                'name' => 'Planos e Preços',
                'slug' => 'planos',
                'service_id' => 6,
                'description' => 'Tabela comparativa de planos e preços, com destaque para o plano recomendado.',
                'meta' => json_encode(['layout' => 'pricing+cta', 'primary_layer' => 'prova_de_valor']),
            ],
            [
                'name' => 'Funcionalidades',
                'slug' => 'funcionalidades',
                'service_id' => 6,
                'description' => 'Demonstra as principais funcionalidades de um sistema ou SaaS.',
                'meta' => json_encode(['layout' => 'icons+list', 'primary_layer' => 'prova_de_valor']),
            ],
            [
                'name' => 'Integrações',
                'slug' => 'integracoes',
                'service_id' => 6,
                'description' => 'Apresenta integrações disponíveis com outras plataformas e APIs.',
                'meta' => json_encode(['layout' => 'logos+text', 'primary_layer' => 'prova_de_valor']),
            ],
            [
                'name' => 'Recursos',
                'slug' => 'recursos',
                'service_id' => 4,
                'description' => 'Mostra o ecossistema de recursos oferecidos pela solução.',
                'meta' => json_encode(['layout' => 'grid+details', 'primary_layer' => 'prova_de_valor']),
            ],

            // 🟢 VALIDAÇÃO
            [
                'name' => 'Cases',
                'slug' => 'cases',
                'service_id' => 1,
                'description' => 'Exibe projetos concluídos, destacando resultados e impacto gerado.',
                'meta' => json_encode(['layout' => 'cards+testimonial', 'primary_layer' => 'validacao']),
            ],
            [
                'name' => 'Depoimentos',
                'slug' => 'depoimentos',
                'service_id' => 1,
                'description' => 'Reúne falas e citações de clientes e parceiros.',
                'meta' => json_encode(['layout' => 'slider', 'primary_layer' => 'validacao']),
            ],
            [
                'name' => 'Clientes e Parceiros',
                'slug' => 'clientes-parceiros',
                'service_id' => 4,
                'description' => 'Lista de marcas e empresas que confiam na solução.',
                'meta' => json_encode(['layout' => 'logos', 'primary_layer' => 'validacao']),
            ],
            [
                'name' => 'Blog',
                'slug' => 'blog',
                'service_id' => 4,
                'description' => 'Publicações e conteúdos de autoridade que reforçam a marca.',
                'meta' => json_encode(['layout' => 'grid+single', 'primary_layer' => 'validacao']),
            ],
            [
                'name' => 'Prêmios e Reconhecimentos',
                'slug' => 'premios',
                'service_id' => 5,
                'description' => 'Exibe selos, prêmios e certificações conquistadas.',
                'meta' => json_encode(['layout' => 'gallery+text', 'primary_layer' => 'validacao']),
            ],

            // 🔵 CONVERSÃO
            [
                'name' => 'Contato',
                'slug' => 'contato',
                'service_id' => 1,
                'description' => 'Página de contato com formulário e informações principais.',
                'meta' => json_encode(['layout' => 'form+map', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'Orçamento',
                'slug' => 'orcamento',
                'service_id' => 1,
                'description' => 'Formulário detalhado para solicitação de orçamento personalizado.',
                'meta' => json_encode(['layout' => 'form+cta', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'Download / Material Rico',
                'slug' => 'download',
                'service_id' => 2,
                'description' => 'Landing de material gratuito para captação de leads.',
                'meta' => json_encode(['layout' => 'cta+form', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'Login',
                'slug' => 'login',
                'service_id' => 6,
                'description' => 'Página de autenticação de usuários do sistema ou SaaS.',
                'meta' => json_encode(['layout' => 'auth+form', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'FAQ e Suporte',
                'slug' => 'faq',
                'service_id' => 6,
                'description' => 'Central de ajuda e dúvidas frequentes.',
                'meta' => json_encode(['layout' => 'accordion+search', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'Política de Privacidade',
                'slug' => 'politica-privacidade',
                'service_id' => 1,
                'description' => 'Página institucional obrigatória de compliance e LGPD.',
                'meta' => json_encode(['layout' => 'text', 'primary_layer' => 'conversao']),
            ],
            [
                'name' => 'Termos de Uso',
                'slug' => 'termos-uso',
                'service_id' => 1,
                'description' => 'Termos legais para uso do site, app ou plataforma.',
                'meta' => json_encode(['layout' => 'text', 'primary_layer' => 'conversao']),
            ],
        ]);
    }
}
