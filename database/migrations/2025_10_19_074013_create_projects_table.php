<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('cover')->nullable(); // imagem de capa do projeto
            $table->string('name');              // nome do projeto
            $table->string('slug')->unique(); // slug amigável (ex: loja-urbanfit)
            $table->text('summary')->nullable(); // resumo do projeto
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('video')->nullable(); // link do vídeo (YouTube, Vimeo, etc.)
            $table->date('finished_at')->nullable(); // data de finalização do projeto
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
