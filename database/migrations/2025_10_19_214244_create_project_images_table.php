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
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_process_id')->constrained('project_process')->onDelete('cascade');
            $table->string('image'); // caminho da imagem no storage
            $table->string('title')->nullable(); // título opcional da imagem
            $table->text('description')->nullable(); // descrição da imagem ou contexto
            $table->text('solution')->nullable(); // explicação do resultado/solução da etapa
            $table->integer('order')->default(0); // ordem de exibição
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_images');
    }
};
