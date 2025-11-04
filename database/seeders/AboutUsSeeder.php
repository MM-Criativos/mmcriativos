<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AboutUsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('aboutus')->insert([
            'photo' => 'assets/images/resources/marcusm.jpg',
            'title' => 'Mais do que sites, criamos presença',
            'subtitle' => 'Sobre a MM Criativos',
            'description' => "
                <p>A MM Criativos une design e desenvolvimento para construir a vitrine digital que sua marca merece.
                Criamos sites que refletem o profissionalismo do seu negócio e aproximam seus clientes de você,
                com identidade, propósito e um clique de distância.</p>

                <p>Desenvolvemos de forma independente, sem amarras a plataformas ou mensalidades.
                Aqui, o site é realmente seu! Você pode hospedá-lo conosco ou onde preferir, com total liberdade e propriedade.</p>

                <p>Fugimos de padrões prontos. Cada projeto é único, feito à mão, com estética moderna e foco na personalidade da sua marca.
                Nossa equipe combina técnica e sensibilidade para entregar resultados que superam expectativas e colocam sua empresa em destaque no digital.</p>
            ",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
