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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();

            $table->enum('line_type', ['base', 'extra'])->default('extra');

            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('ref_type')->nullable(); // 'plan' ou 'extra'

            // Snapshot dos dados
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('qty')->default(1);

            // Preço original e tipo
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->enum('price_type', ['fixed', 'percent'])->default('fixed');
            $table->enum('billing_period', ['one_time', 'monthly', 'yearly'])->default('one_time');

            // 🔹 Novo campo — desconto aplicado nesse item
            $table->decimal('discount_amount', 10, 2)->default(0);

            // 🔹 Valor total calculado (já considerando desconto)
            $table->decimal('total', 10, 2)->default(0);

            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
