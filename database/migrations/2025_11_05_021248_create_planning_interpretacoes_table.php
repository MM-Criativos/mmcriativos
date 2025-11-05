<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_interpretacoes', function (Blueprint $table) {
            $table->id();

            // Relações principais
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            // Informações analíticas e de escopo
            $table->longText('analise_publico')->nullable(); // resumo do público e comunicação
            $table->longText('analise_concorrencia')->nullable(); // benchmark
            $table->longText('diretrizes_visuais')->nullable(); // moodboard, identidade visual
            $table->longText('definicao_escopo')->nullable(); // o que será entregue
            $table->longText('observacoes_tecnicas')->nullable(); // integrações, limitações técnicas

            // Controle
            $table->enum('status', ['draft', 'review', 'approved'])->default('draft');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_interpretacoes');
    }
};
