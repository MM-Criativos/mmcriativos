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
        Schema::create('social_medias', function (Blueprint $table) {
            $table->id();
            $table->string('name');   // Nome da rede (ex: Instagram, LinkedIn)
            $table->string('slug')->unique(); // ex: instagram, linkedin
            $table->string('icon');   // Classe do ícone Font Awesome (ex: fa-brands fa-instagram)
            $table->timestamps();     // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_medias');
    }
};
