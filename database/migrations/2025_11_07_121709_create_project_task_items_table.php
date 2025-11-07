<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_items', function (Blueprint $table) {
            $table->id();

            // 🔗 Relações principais
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_task_id')->constrained()->onDelete('cascade');

            // 🔗 Relacionamentos técnicos e de equipe
            $table->foreignId('skill_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('skill_competency_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // 📋 Conteúdo
            $table->string('title');
            $table->text('description')->nullable();

            // ⚙️ Status e progresso
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();

            // 📅 Ordenação e datas padrão
            $table->unsignedTinyInteger('order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_items');
    }
};
