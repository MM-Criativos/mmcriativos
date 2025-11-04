<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->integer('hierarquia')->default(1); // 1 = primária, 2 = secundária, 3 = final
            $table->string('classe'); // nome da classe (ex: Construtor de Interfaces)
            $table->text('description')->nullable(); // descrição institucional
            $table->json('skills')->nullable(); // lista de competências relacionadas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
