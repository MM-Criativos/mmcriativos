<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualitative_templates', function (Blueprint $table) {
            $table->id();

            // 🔖 Classificação da pergunta
            $table->string('service_type')->nullable(); // ex: 'landing_page', 'portal', 'saas'
            $table->string('category')->nullable(); // ex: 'Público-Alvo', 'Conteúdo', 'Concorrência'

            // 🧠 Estrutura da pergunta
            $table->text('question'); // texto da pergunta
            $table->enum('type', ['text', 'textarea', 'choice', 'multi_choice', 'file'])->default('textarea');

            // ⚙️ Opções e placeholders
            $table->json('options')->nullable(); // usado se for choice/multi_choice
            $table->string('placeholder')->nullable(); // texto auxiliar
            $table->boolean('is_required')->default(false); // marca se é obrigatória
            $table->boolean('is_active')->default(true); // ativa/desativa pergunta

            // 📊 Controle de exibição
            $table->integer('sort_order')->default(0); // posição da pergunta no briefing

            // 📅 Registro
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualitative_templates');
    }
};
