<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdr_call_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('sdr_name');
            $table->string('company');
            $table->string('contact')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('path')->nullable();
            $table->enum('outcome', [
                'reuniao_agendada',
                'retorno_agendado',
                'sem_interesse',
                'encerrado_manualmente',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdr_call_sessions');
    }
};
