<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Process;
use Illuminate\Validation\Rules\Email;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Executa todos os seeders personalizados
        $this->call([
            ServiceSeeder::class,
            SkillSeeder::class,
            SkillCompetencySeeder::class,
            SocialMediaSeeder::class,
            ProcessSeeder::class,
            ServiceInfoSeeder::class,
            ServiceBenefitSeeder::class,
            ServiceFeatureSeeder::class,
            ServiceProcessSeeder::class,
            ServiceCtaSeeder::class,
            ClassSeeder::class,
            AboutUsSeeder::class,
            PlanSeeder::class,
            PlanAdvantageSeeder::class,
            ExtrasSeeder::class,
            ExtraServiceSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}
