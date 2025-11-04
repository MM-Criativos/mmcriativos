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
        Schema::create('extras', function (Blueprint $table) {
            $table->id();

            // Nome e descrição
            $table->string('name');
            $table->text('description')->nullable();

            // 🔹 Preço base
            $table->decimal('price', 10, 2)->default(0);

            // 🔹 Tipo de preço e período
            $table->enum('price_type', ['fixed', 'percent'])->default('fixed');
            $table->enum('billing_period', ['one_time', 'monthly', 'yearly'])->default('one_time');

            // 🔹 Desconto padrão (opcional)
            $table->decimal('default_discount', 10, 2)->default(0);

            // 🔹 Categoria visual
            $table->string('category')->nullable(); // Infraestrutura, Integrações, SEO, Suporte, etc.

            // Status e ordenação
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extras');
    }
};
