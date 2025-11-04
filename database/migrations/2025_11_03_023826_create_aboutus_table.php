<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aboutus', function (Blueprint $table) {
            $table->id();
            $table->string('photo')->nullable();       // imagem principal ou avatar institucional
            $table->string('title');                   // título principal da seção
            $table->string('subtitle')->nullable();    // subtítulo explicativo
            $table->text('description')->nullable();   // texto descritivo sobre a empresa/pessoa
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aboutus');
    }
};
