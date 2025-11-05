<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_kickoffs', function (Blueprint $table) {
            $table->id();

            // Relações principais
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            // Conteúdo do kickoff
            $table->string('titulo')->default('Reunião de Kickoff');
            $table->text('objetivo')->nullable(); // objetivo da reunião
            $table->longText('resumo_alinhamento')->nullable(); // resumo geral das decisões
            $table->longText('tarefas_iniciais')->nullable(); // próximos passos definidos
            $table->longText('responsaveis')->nullable(); // quem é responsável por quê
            $table->longText('materiais_apresentados')->nullable(); // links de slides, docs, etc.

            // Controle de status
            $table->enum('status', ['agendado', 'realizado', 'aprovado'])->default('agendado');
            $table->timestamp('data_reuniao')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_kickoffs');
    }
};
