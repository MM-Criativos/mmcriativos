<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SkillInfoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('skill_infos')->delete();

        DB::table('skill_infos')->insert([
            [
                'skill_id' => 1,
                'image' => 'storage/skills/30c0c963-3641-42da-a8c7-b616452f5b7d-20251031-080458.png',
                'title' => 'Transformando ideias em experiências visuais',
                'subtitle' => 'A camada visível do digital',
                'description' => "O front-end é o ponto onde o design ganha vida e o conceito se torna tangível. É o espaço onde a imaginação se transforma em forma, cor e movimento, criando interfaces reais, intuitivas e responsivas.\n\nCada linha de código, cada transição e cada microanimação são cuidadosamente planejadas para transmitir propósito e emoção.\n\nMais do que estética, o front-end é sobre experiência, ele define como as pessoas interagem com a marca, como percebem o valor do conteúdo e como navegam entre o visual e o funcional de forma fluida.\n\nÉ nesse equilíbrio entre beleza e usabilidade que nasce a verdadeira identidade digital de um projeto.",
                'created_at' => Carbon::parse('2025-10-27 23:35:55'),
                'updated_at' => Carbon::parse('2025-10-31 08:04:58'),
            ],
            [
                'skill_id' => 2,
                'image' => 'storage/skills/2bf3d3db-0214-4aa7-a974-3c651aadf6e4-20251031-080517.png',
                'title' => 'A base invisível que mantém tudo em movimento',
                'subtitle' => 'O núcleo que sustenta o digital',
                'description' => "Enquanto o front-end dá forma à experiência, o back-end é o que a torna possível.\nÉ aqui que dados se organizam, lógicas se conectam e cada ação ganha propósito.\nEssa camada invisível garante que tudo funcione como deve: processando informações, controlando fluxos e mantendo o sistema estável, seguro e eficiente.\n\nCada linha de código no back-end carrega responsabilidade, ela define como o site pensa, responde e se adapta.\nÉ o elo que conecta o usuário à informação, transformando cliques em resultados e interações em valor.\n\nMais do que infraestrutura, o back-end é o coração pulsante do projeto.\nSem ele, nada se move. Com ele, o digital respira com inteligência, consistência e poder.",
                'created_at' => Carbon::parse('2025-10-28 00:20:00'),
                'updated_at' => Carbon::parse('2025-10-31 08:05:17'),
            ],
            [
                'skill_id' => 3,
                'image' => 'storage/skills/7b1d32a8-6988-435e-aba2-ad051ca39ff6-20251031-080556.png',
                'title' => 'Projetando jornadas que unem emoção e funcionalidade',
                'subtitle' => 'Entender pessoas, criar sentido',
                'description' => "A experiência do usuário é o ponto onde tecnologia e empatia se encontram.\n\nÉ o estudo de como as pessoas pensam, sentem e se comportam ao interagir com o digital, e como transformar isso em caminhos intuitivos e agradáveis.\n\nCada fluxo, botão e microinteração é resultado de observação e propósito.\nTudo é planejado para guiar o usuário sem esforço, mantendo o equilíbrio entre estética e eficiência.\nA boa experiência não se impõe: ela se revela de forma natural, tornando o uso tão fluido que o design quase desaparece.\n\nMais do que criar telas bonitas, UX e UI moldam conexões humanas.\nSão elas que transformam um simples clique em confiança, uma navegação em descoberta e um site em algo memorável.",
                'created_at' => Carbon::parse('2025-10-28 02:23:45'),
                'updated_at' => Carbon::parse('2025-10-31 08:05:56'),
            ],
            [
                'skill_id' => 4,
                'image' => 'storage/skills/70470a3f-06b3-4ca4-9ee4-66d9e8365bd1-20251031-080620.png',
                'title' => 'Otimização inteligente para resultados que aparecem',
                'subtitle' => 'Velocidade, alcance e relevância',
                'description' => "SEO e performance são o motor invisível que impulsiona um site rumo à visibilidade e ao sucesso.\nNão basta ter um projeto bonito, ele precisa ser encontrado, rápido e eficiente.\nÉ aqui que a técnica se alia à estratégia para garantir que cada página carregue com agilidade, cada palavra tenha propósito e cada detalhe conte pontos nos mecanismos de busca.\n\nPor trás das métricas, há engenharia: estrutura limpa, código otimizado e experiência fluida em qualquer dispositivo.\nCada segundo economizado, cada clique conquistado e cada posição alcançada refletem o cuidado com o desempenho.\n\nMais do que posicionamento, SEO e performance são sobre credibilidade.\nEles constroem confiança, fortalecem a presença digital e fazem a diferença entre ser visto, ou ser esquecido.",
                'created_at' => Carbon::parse('2025-10-28 02:35:55'),
                'updated_at' => Carbon::parse('2025-10-31 08:06:20'),
            ],
            [
                'skill_id' => 5,
                'image' => 'storage/skills/349fb934-048e-433d-8409-95b5945dacb2-20251031-080442.png',
                'title' => 'Quando o código aprende, o digital evolui',
                'subtitle' => 'O futuro que trabalha ao seu lado',
                'description' => "A automatização e a inteligência artificial transformam o modo como criamos, otimizamos e escalamos soluções.\nSão sistemas que observam, aprendem e executam tarefas complexas com precisão e velocidade, liberando tempo para o que realmente importa: a criatividade e a estratégia.\n\nPor trás de cada processo automatizado há lógica, integração e aprendizado contínuo.\nSão fluxos inteligentes que analisam padrões, antecipam demandas e mantêm o funcionamento do digital em ritmo constante, sem falhas e sem pausas.\n\nA IA não substitui o humano, ela amplia suas possibilidades.\nÉ a ponte entre eficiência e inovação, entre dados e decisão, entre o presente e o que vem depois.\n\nAutomatização e IA são o estágio em que o digital deixa de reagir e começa a pensar.\nE é nesse ponto que a tecnologia se torna realmente viva.",
                'created_at' => Carbon::parse('2025-10-28 02:39:35'),
                'updated_at' => Carbon::parse('2025-10-31 08:04:42'),
            ],
        ]);
    }
}
