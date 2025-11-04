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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('video')->nullable(); // Caminho do vídeo
            $table->string('text_1')->nullable(); // Primeira linha de texto
            $table->string('text_2')->nullable(); // Segunda linha de texto
            $table->string('text_3')->nullable(); // Terceira linha de texto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
