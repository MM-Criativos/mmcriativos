<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_briefing_reguas', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // ex: "Personalidade da Marca", "Público e Comunicação"
            $table->string('question'); // texto da pergunta
            $table->string('label_left');
            $table->string('label_right');
            $table->string('emoji_left', 10)->nullable();
            $table->string('emoji_right', 10)->nullable();
            $table->integer('min')->default(0);
            $table->integer('max')->default(10);
            $table->integer('step')->default(1);
            $table->integer('default_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_briefing_reguas');
    }
};
