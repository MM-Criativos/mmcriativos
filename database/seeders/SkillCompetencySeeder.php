<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillCompetencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('skill_competencies')->insert([
            // 1️⃣ Front End
            ['skill_id' => 1, 'competency' => 'HTML5', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'CSS3', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'JavaScript (ES6+)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'Vue.js', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'React', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'TailwindCSS', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'Bootstrap', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'Responsive Design', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'Accessibility (WCAG)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 1, 'competency' => 'Animations (CSS / GSAP)', 'created_at' => now(), 'updated_at' => now()],

            // 2️⃣ Back End
            ['skill_id' => 2, 'competency' => 'PHP (Laravel, Lumen)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Node.js / Express.js', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'RESTful APIs', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Authentication (Sanctum, JWT)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Eloquent ORM', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Queues and Schedulers', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'File Upload and Storage', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Database Migrations and Seeders', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Integration with External APIs', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 2, 'competency' => 'Security Best Practices (OWASP)', 'created_at' => now(), 'updated_at' => now()],

            // 3️⃣ UX e UI
            ['skill_id' => 3, 'competency' => 'Design Systems', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Figma', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Prototyping and Wireframing', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Information Architecture', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Visual Hierarchy', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Accessibility Design', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Typography and Color Theory', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Microinteractions', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Responsive UI Design', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 3, 'competency' => 'Usability Testing', 'created_at' => now(), 'updated_at' => now()],

            // 4️⃣ SEO e Performance
            ['skill_id' => 4, 'competency' => 'Semantic HTML', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Meta Tags and Rich Snippets', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Sitemap & Robots.txt', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Core Web Vitals Optimization', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Lazy Loading and Asset Optimization', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Cache Strategies (HTTP, Laravel)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'SEO Technical Implementation', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Internal Linking Optimization', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'PageSpeed Insights Analysis', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 4, 'competency' => 'Google Analytics & Search Console', 'created_at' => now(), 'updated_at' => now()],

            // 5️⃣ Automatização e IA
            ['skill_id' => 5, 'competency' => 'n8n Workflow Automation', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Zapier & Make (Integromat)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Webhooks Integration', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'AI Content Generation (GPT, Claude, Mistral)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Prompt Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Text Classification & Moderation', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'API Integrations (OpenAI, HuggingFace)', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'SaaS Integrations', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Workflow Automation Systems', 'created_at' => now(), 'updated_at' => now()],
            ['skill_id' => 5, 'competency' => 'Basic Model Training', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
