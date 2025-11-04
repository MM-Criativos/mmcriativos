<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('classes')->insert([
            // 🌱 HIERARQUIA 1 — CLASSES PRIMÁRIAS
            [
                'hierarquia' => 1,
                'classe' => 'Designer de Interfaces',
                'build' => null,
                'description' => 'Especialista em transformar conceitos visuais em interfaces funcionais e esteticamente equilibradas. Une design e código para criar experiências intuitivas e atraentes.',
                'skills' => json_encode(['HTML5', 'CSS3', 'Tailwind', 'Figma', 'Design Responsivo']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Animador de Interfaces',
                'build' => null,
                'description' => 'Profissional focado em dar vida às interações visuais. Trabalha com animações, transições e microinterações que tornam o digital mais envolvente e dinâmico.',
                'skills' => json_encode(['JavaScript', 'Vue.js', 'React', 'GSAP', 'Motion Design']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Arquiteto de Experiência (UX)',
                'build' => null,
                'description' => 'Entende pessoas, jornadas e comportamentos. Cria fluxos claros e experiências digitais simples, eficientes e humanas.',
                'skills' => json_encode(['UX Research', 'Wireframes', 'Usabilidade', 'Prototipagem']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Desenvolvedor Back-End',
                'build' => null,
                'description' => 'Responsável pela estrutura lógica e pela inteligência do sistema. Cria bases sólidas, seguras e escaláveis que sustentam o produto digital.',
                'skills' => json_encode(['PHP', 'Laravel', 'SQL', 'APIs', 'Segurança']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Engenheiro de Inteligência Digital (IA)',
                'build' => null,
                'description' => 'Conecta tecnologia e automação. Cria soluções baseadas em IA e fluxos inteligentes que potencializam o trabalho humano.',
                'skills' => json_encode(['OpenAI API', 'n8n', 'Automação', 'Prompt Engineering']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Especialista em SEO e Performance',
                'build' => null,
                'description' => 'Cuida para que o projeto seja rápido, acessível e visível. Trabalha a otimização técnica e estratégica que melhora o alcance e o desempenho online.',
                'skills' => json_encode(['SEO Técnico', 'Core Web Vitals', 'Otimização', 'Google Analytics']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Designer de Identidade Visual',
                'build' => null,
                'description' => 'Profissional criativo que traduz valores e propósito em imagens, tipografias e cores. Dá forma à essência das marcas.',
                'skills' => json_encode(['Branding', 'Direção de Arte', 'Paleta de Cores', 'Tipografia']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 1,
                'classe' => 'Automatizador Digital',
                'build' => null,
                'description' => 'Focado em integrações e automações entre ferramentas e sistemas. Otimiza processos e elimina tarefas repetitivas.',
                'skills' => json_encode(['n8n', 'Zapier', 'APIs', 'Webhooks', 'Integrações SaaS']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 🌀 HIERARQUIA 2 — CLASSES SECUNDÁRIAS
            [
                'hierarquia' => 2,
                'classe' => 'Construtor Visual',
                'build' => null,
                'description' => 'Une o olhar criativo do designer à precisão técnica do desenvolvedor. Transforma ideias em experiências completas, belas e funcionais.',
                'skills' => json_encode(['Front-end', 'UX/UI', 'Figma', 'JavaScript', 'CSS']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Engenheiro Criativo',
                'build' => null,
                'description' => 'Junta lógica e design em equilíbrio. Constrói sistemas inteligentes com foco em usabilidade, performance e inovação.',
                'skills' => json_encode(['Back-end', 'Front-end', 'Laravel', 'Vue.js']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Estrategista de Presença Digital',
                'build' => null,
                'description' => 'Integra branding, conteúdo e SEO para criar presença digital consistente e memorável.',
                'skills' => json_encode(['Branding', 'SEO', 'UX Writing', 'Marketing Digital']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Orquestrador de Sistemas',
                'build' => null,
                'description' => 'Conecta automação, dados e inteligência artificial para criar ecossistemas digitais autônomos e eficientes.',
                'skills' => json_encode(['n8n', 'OpenAI', 'Integrações', 'API Design']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Diretor de Experiência Digital',
                'build' => null,
                'description' => 'Supervisiona a jornada completa do usuário, garantindo coerência entre design, tecnologia e propósito.',
                'skills' => json_encode(['UX', 'UI', 'Front-end', 'Usabilidade']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Desenvolvedor Full Spectrum',
                'build' => null,
                'description' => 'Atua em todas as camadas do desenvolvimento. Entrega soluções completas, do design à lógica do servidor.',
                'skills' => json_encode(['Full Stack', 'Laravel', 'Vue.js', 'APIs']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Cientista de Interfaces',
                'build' => null,
                'description' => 'Une dados e comportamento. Cria interfaces inteligentes que aprendem e evoluem conforme o uso.',
                'skills' => json_encode(['UX', 'IA', 'Analytics', 'Prototipagem Avançada']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 2,
                'classe' => 'Arquiteto de Performance Digital',
                'build' => null,
                'description' => 'Otimiza todo o ecossistema técnico — código, automação e SEO — para máxima eficiência e resultados.',
                'skills' => json_encode(['SEO', 'Performance', 'Automação', 'Backend']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 🌟 HIERARQUIA 3 — CLASSES FINAIS
            [
                'hierarquia' => 3,
                'classe' => 'Arquiteto Digital',
                'build' => null,
                'description' => 'Profissional que une design, tecnologia e estratégia. Cria soluções completas, do conceito à execução, com visão técnica e criativa.',
                'skills' => json_encode(['Front-end', 'Back-end', 'UX/UI', 'Automação', 'SEO']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 3,
                'classe' => 'Diretor Criativo Digital',
                'build' => null,
                'description' => 'Lidera processos criativos e técnicos. Atua na direção de arte, experiência e tecnologia, garantindo consistência em toda a entrega.',
                'skills' => json_encode(['UX/UI', 'Branding', 'Front-end', 'Automação']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 3,
                'classe' => 'Engenheiro de Produto Digital',
                'build' => null,
                'description' => 'Constrói plataformas, sistemas e experiências integradas com base em dados, IA e automação.',
                'skills' => json_encode(['Full Stack', 'IA', 'Automação', 'Arquitetura de Sistemas']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 3,
                'classe' => 'Estrategista de Inovação',
                'build' => null,
                'description' => 'Pensa o futuro do digital. Integra IA, automação e design para criar soluções visionárias e sustentáveis.',
                'skills' => json_encode(['Automação', 'IA', 'UX', 'Estratégia']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hierarquia' => 3,
                'classe' => 'Criador Multidisciplinar',
                'build' => null,
                'description' => 'Profissional completo, que domina arte, código e estratégia, transformando ideias em ecossistemas digitais inteligentes.',
                'skills' => json_encode(['Design', 'Desenvolvimento', 'Automação', 'Branding', 'IA']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
