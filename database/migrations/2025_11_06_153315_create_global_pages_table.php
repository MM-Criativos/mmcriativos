<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('global_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Home, Sobre, Serviços
            $table->string('slug')->unique(); // Ex: home, sobre, servicos
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade'); // 🔗 relação direta
            $table->text('description')->nullable();
            $table->json('meta')->nullable(); // layouts, camada narrativa etc
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_pages');
    }
};
