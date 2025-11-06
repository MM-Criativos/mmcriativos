<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalPageComponentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('global_page_component')->insert([
            // 🏠 HOME (Landing / Multi / SaaS)
            ['page_id' => 1, 'component_id' => 1,  'order' => 1], // Hero Header
            ['page_id' => 1, 'component_id' => 10, 'order' => 2], // Service Grid
            ['page_id' => 1, 'component_id' => 17, 'order' => 3], // Testimonial Slider
            ['page_id' => 1, 'component_id' => 21, 'order' => 4], // CTA Block

            // 👥 SOBRE
            ['page_id' => 2, 'component_id' => 2, 'order' => 1], // Intro Manifesto
            ['page_id' => 2, 'component_id' => 7, 'order' => 2], // Timeline Story
            ['page_id' => 2, 'component_id' => 8, 'order' => 3], // Founder Quote
            ['page_id' => 2, 'component_id' => 22, 'order' => 4], // Contact Form

            // 🧱 SERVIÇOS
            ['page_id' => 7,  'component_id' => 1,  'order' => 1], // Hero Header
            ['page_id' => 7,  'component_id' => 10, 'order' => 2], // Service Grid
            ['page_id' => 7,  'component_id' => 11, 'order' => 3], // Feature List
            ['page_id' => 7,  'component_id' => 21, 'order' => 4], // CTA Block

            // 💼 CASES / PORTFÓLIO
            ['page_id' => 13, 'component_id' => 15, 'order' => 1], // Case Showcase
            ['page_id' => 13, 'component_id' => 17, 'order' => 2], // Testimonial Slider
            ['page_id' => 13, 'component_id' => 21, 'order' => 3], // CTA Block

            // 💬 DEPOIMENTOS
            ['page_id' => 14, 'component_id' => 17, 'order' => 1], // Testimonial Slider
            ['page_id' => 14, 'component_id' => 21, 'order' => 2], // CTA Block

            // 📰 BLOG
            ['page_id' => 16, 'component_id' => 1,  'order' => 1], // Hero Header
            ['page_id' => 16, 'component_id' => 15, 'order' => 2], // Case Showcase (usado como grid de posts)
            ['page_id' => 16, 'component_id' => 21, 'order' => 3], // CTA Block

            // 📞 CONTATO
            ['page_id' => 18, 'component_id' => 22, 'order' => 1], // Contact Form
            ['page_id' => 18, 'component_id' => 21, 'order' => 2], // CTA Block

            // 💡 ORÇAMENTO
            ['page_id' => 19, 'component_id' => 22, 'order' => 1], // Contact Form
            ['page_id' => 19, 'component_id' => 21, 'order' => 2], // CTA Block

            // ⚙️ FUNCIONALIDADES (SaaS)
            ['page_id' => 10, 'component_id' => 12, 'order' => 1], // Comparison Table
            ['page_id' => 10, 'component_id' => 14, 'order' => 2], // Tech Stack
            ['page_id' => 10, 'component_id' => 21, 'order' => 3], // CTA Block

            // 💬 FAQ
            ['page_id' => 23, 'component_id' => 24, 'order' => 1], // FAQ Accordion
            ['page_id' => 23, 'component_id' => 21, 'order' => 2], // CTA Block
        ]);
    }
}
