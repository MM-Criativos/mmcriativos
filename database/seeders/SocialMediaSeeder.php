<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('social_medias')->insert([
            [
                'name' => 'Facebook',
                'slug' => 'facebook',
                'icon' => 'fa-brands fa-facebook-f',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instagram',
                'slug' => 'instagram',
                'icon' => 'fa-brands fa-instagram',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'TikTok',
                'slug' => 'tiktok',
                'icon' => 'fa-brands fa-tiktok',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'X',
                'slug' => 'x',
                'icon' => 'fa-brands fa-x-twitter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'LinkedIn',
                'slug' => 'linkedin',
                'icon' => 'fa-brands fa-linkedin-in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'YouTube',
                'slug' => 'youtube',
                'icon' => 'fa-brands fa-youtube',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Behance',
                'slug' => 'behance',
                'icon' => 'fa-brands fa-behance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dribbble',
                'slug' => 'dribbble',
                'icon' => 'fa-brands fa-dribbble',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GitHub',
                'slug' => 'github',
                'icon' => 'fa-brands fa-github',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'WhatsApp',
                'slug' => 'whatsapp',
                'icon' => 'fa-brands fa-whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
