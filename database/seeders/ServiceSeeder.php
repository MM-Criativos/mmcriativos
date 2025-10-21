<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'name' => 'Landing Page',
                'slug' => 'landing',
                'icon' => '<i class="fa-light fa-bullseye-arrow"></i>',
                'thumb' => 'assets/images/service/thumb_landing.jpg',
                'cover' => 'assets/images/service/cover_landing.jpg',
                'description' => 'Feita para conversão. Ideal para campanhas e lançamentos.',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Site Single Page',
                'slug' => 'single',
                'icon' => '<i class="fa-light fa-window-flip"></i>',
                'thumb' => 'assets/images/service/thumb_single.jpg',
                'cover' => 'assets/images/service/cover_single.jpg',
                'description' => 'Tudo em uma única página, direto ao ponto, com design impactante.',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Site Multipage',
                'slug' => 'multi',
                'icon' => '<i class="fa-light fa-browsers"></i>',
                'thumb' => 'assets/images/service/thumb_multi.jpg',
                'cover' => 'assets/images/service/cover_multi.jpg',
                'description' => 'Estrutura completa para apresentar sua marca com profundidade.',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Portal',
                'slug' => 'portal',
                'icon' => '<i class="fa-light fa-network-wired"></i>',
                'thumb' => 'assets/images/service/thumb_portal.jpg',
                'cover' => 'assets/images/service/cover_portal.jpg',
                'description' => 'Soluções sob medida para portais com gerenciamento online.',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sistema Personalizado',
                'slug' => 'sistema',
                'icon' => '<i class="fa-light fa-pen-paintbrush"></i>',
                'thumb' => 'assets/images/service/thumb_sistema.jpg',
                'cover' => 'assets/images/service/cover_sistema.jpg',
                'description' => 'Desenvolvemos sistemas únicos, feitos para o fluxo do seu negócio.',
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SaaS e Integrações',
                'slug' => 'saas',
                'icon' => '<i class="fa-light fa-chart-network"></i>',
                'thumb' => 'assets/images/service/thumb_saas.jpg',
                'cover' => 'assets/images/service/cover_saas.jpg',
                'description' => 'Conectamos automações e serviços em um ecossistema inteligente.',
                'order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
