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
        Schema::create('extra_service', function (Blueprint $table) {
            $table->id();

            // 🔗 Liga um extra a um serviço
            $table->foreignId('extra_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            // Opcional: prioridade ou visibilidade
            $table->boolean('is_default')->default(false); // se o extra já vem sugerido
            $table->integer('sort')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_service');
    }
};
