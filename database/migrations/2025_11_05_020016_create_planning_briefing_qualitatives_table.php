<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_briefing_qualitatives', function (Blueprint $table) {
            $table->id();

            // Relações principais
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            // Identificação
            $table->string('title')->default('Briefing Qualitativo');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved'])->default('draft');

            // Perguntas selecionadas (IDs de qualitative_templates)
            $table->json('selected_templates')->nullable();

            // Dados adicionais (instruções, observações, configurações)
            $table->json('meta')->nullable();

            // Controle de progresso
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_briefing_qualitatives');
    }
};
