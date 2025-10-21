<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('skills')->insert([
            [
                'name' => 'Layout e Interface',
                'slug' => 'frontend',
                'icon' => 'fa-light fa-code',
                'description' => 'Interfaces modernas e responsivas que destacam sua marca e encantam o usuário.',
                'thumb' => 'assets/images/skills/thumb_front.jpg',
                'cover' => 'assets/images/skills/cover_front.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Estrutura e Lógica',
                'slug' => 'backend',
                'icon' => 'fa-light fa-database',
                'description' => 'Códigos limpos e estáveis garantem performance, segurança e escalabilidade.',
                'thumb' => 'assets/images/skills/thumb_back.jpg',
                'cover' => 'assets/images/skills/cover_back.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Experiência do Usuário',
                'slug' => 'uxui',
                'icon' => 'fa-light fa-bezier-curve',
                'description' => 'Estética e funcionalidade unidas para uma navegação fluida e envolvente.',
                'thumb' => 'assets/images/skills/thumb_ux.jpg',
                'cover' => 'assets/images/skills/cover_ux.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SEO e Performance',
                'slug' => 'seo',
                'icon' => 'fa-light fa-rocket',
                'description' => 'Sites otimizados para aparecer no Google e carregar em segundos.',
                'thumb' => 'assets/images/skills/thumb_seo.jpg',
                'cover' => 'assets/images/skills/cover_seo.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Automatização e IA',
                'slug' => 'automacao',
                'icon' => 'fa-light fa-robot',
                'description' => 'Conectamos ferramentas para otimizar rotinas e impulsionar resultados.',
                'thumb' => 'assets/images/skills/thumb_automacao.jpg',
                'cover' => 'assets/images/skills/cover_automacao.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
