<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_briefing_qualitative_responses', function (Blueprint $table) {
            $table->id();

            // Relações principais
            $table->unsignedBigInteger('briefing_id');
            $table->foreign('briefing_id', 'pbq_briefing_fk')
                ->references('id')
                ->on('planning_briefing_qualitatives')
                ->cascadeOnDelete();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('template_id')
                ->constrained('qualitative_templates')
                ->cascadeOnDelete();

            // Tipo e valor da resposta
            $table->string('type')->default('text'); // text, textarea, choice, multi_choice, file
            $table->longText('answer')->nullable(); // pode armazenar texto, JSON ou array
            $table->string('file_path')->nullable(); // se a resposta for arquivo

            // Controle
            $table->boolean('is_completed')->default(false);
            $table->timestamp('answered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_briefing_qualitative_responses');
    }
};
