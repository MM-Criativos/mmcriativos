<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_leads', function (Blueprint $table) {
            $table->id();

            $table->string('empresa');

            $table->enum('status', [
                'nao_iniciado',
                'em_andamento',
                'concluido',
            ])->default('nao_iniciado');

            $table->enum('etapa', [
                'novo_lead',
                'iniciado',
                'respondeu',
                'engajado',
                'dor_identificado',
                'qualificado',
                'reuniao_agendada',
                'follow_up',
                'perdido',
            ])->default('novo_lead');

            $table->foreignId('responsavel_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('contato')->nullable();
            $table->string('seguidores', 50)->nullable();
            $table->string('tomador_de_decisao')->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->text('descricao')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_leads');
    }
};
