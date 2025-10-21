<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCtaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('service_ctas')->insert([
            [
                'service_id' => 1,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+gostaria+de+criar+minha+Landing+Page+com+a+MM+Criativos!',
                'title' => 'Pronto para lançar sua Landing Page? Fale agora com nossa equipe no WhatsApp!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+site+Single+Page+com+a+MM+Criativos!',
                'title' => 'Pronto para apresentar sua marca ao mundo? Fale com nossa equipe agora!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 3,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+site+Multipage+com+a+MM+Criativos!',
                'title' => 'Fale com nossa equipe. Aprofunde a história da sua marca no digital agora mesmo!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 4,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+quero+construir+meu+Portal+com+a+MM+Criativos!',
                'title' => 'Fale com nossa equipe. Construa um portal completo e sob medida para o seu negócio!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 5,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+quero+criar+meu+Sistema+Empresarial+com+a+MM+Criativos!',
                'title' => 'Otimize sua operação com um sistema feito para o seu negócio. Fale com nossa equipe!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 6,
                'image' => 'assets/images/resources/cta-1-1.jpg',
                'phone' => 'https://wa.me/5511958469546?text=Olá%2C+quero+criar+meu+SaaS+com+a+MM+Criativos!',
                'title' => 'Transforme sua ideia em um SaaS escalável e pronto para crescer com a MM Criativos.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
