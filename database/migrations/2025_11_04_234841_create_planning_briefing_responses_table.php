<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_briefing_responses', function (Blueprint $table) {
            $table->id();

            // 🔗 Relacionamentos principais
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('briefing_regua_id')->constrained('planning_briefing_reguas')->onDelete('cascade');

            // 🧭 Resposta do cliente
            $table->integer('value')->nullable(); // valor 0 a 10
            $table->text('comment')->nullable();  // justificativa opcional
            $table->string('attachment')->nullable(); // upload opcional (ex: print de referência)

            // 📅 Controle de atualização
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_briefing_responses');
    }
};
