<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('processes')->insert([
            [
                'id' => 1,
                'name' => 'Wireframe e Estrutura',
                'slug' => 'wireframe',
                'icon' => '<i class="fa-solid fa-table-layout"></i>',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Design de Interface (UI)',
                'slug' => 'ui',
                'icon' => '<i class="fa-solid fa-pen-ruler"></i>',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Lógica do Sistema',
                'slug' => 'logica',
                'icon' => '<i class="fa-solid fa-gears"></i>',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
