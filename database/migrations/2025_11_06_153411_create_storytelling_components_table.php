<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('storytelling_components', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Hero Header, Proof Grid, CTA Block
            $table->string('slug')->unique(); // Ex: hero-header, proof-grid, cta-block
            $table->enum('layer', ['identidade', 'proposito', 'prova_de_valor', 'validacao', 'conversao']); // Camada narrativa
            $table->string('component_type')->nullable(); // blade, vue, partial, etc
            $table->text('description')->nullable(); // descrição conceitual
            $table->json('props')->nullable(); // dados dinâmicos (ex: título, subtítulo, imagem)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storytelling_components');
    }
};
