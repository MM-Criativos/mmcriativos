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
        Schema::create('budget_events', function (Blueprint $table) {
            $table->id();

            // 🔗 Relacionamento principal
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();

            // Tipo de evento
            $table->string('event');
            // exemplos: created, updated, sent, opened, accepted, declined, expired, comment_added

            // Informações complementares
            $table->json('meta')->nullable();
            // pode guardar { "ip": "192.168...", "user_id": 5, "message": "Enviado por email" }

            // Origem (opcional, se quiser registrar quem fez)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable(); // se quiser suportar polymorphic (admin, client, etc.)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_events');
    }
};
