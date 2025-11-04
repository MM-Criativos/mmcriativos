<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();

            // Dia da semana ou grupo (ex: "Seg a Sex")
            $table->string('days')->default('Seg a Sex');

            // Horários
            $table->time('open_time')->nullable();   // Ex: 09:00
            $table->time('close_time')->nullable();  // Ex: 18:00

            // Indica se está fechado (ex: domingo)
            $table->boolean('is_closed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
