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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // ex: fa-light fa-browser, fa-light fa-code
            $table->string('thumb')->nullable(); // miniatura (ex: imagem de card ou listagem)
            $table->string('cover')->nullable(); // imagem de capa (ex: usada no header da página do serviço)
            $table->text('description')->nullable(); // descrição do serviço
            $table->integer('order')->default(0); // ordem de exibição
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
