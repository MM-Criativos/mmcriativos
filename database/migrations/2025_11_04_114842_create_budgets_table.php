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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            // Produto base
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();

            // 🔹 Cliente vinculado
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot do preço base no momento da criação
            $table->decimal('base_price_snapshot', 10, 2)->default(0);

            // 🔹 Snapshot do contato (pra manter histórico)
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();

            // 🔹 Moeda e ajustes
            $table->string('currency', 3)->default('BRL');

            // Descontos e impostos
            $table->decimal('discount_amount', 10, 2)->default(0); // desconto global manual
            $table->decimal('total_discount_amount', 10, 2)->default(0); // soma dos descontos dos itens
            $table->decimal('tax_percent', 5, 2)->nullable();

            // Subtotais por período
            $table->decimal('subtotal_one_time', 10, 2)->default(0);
            $table->decimal('subtotal_monthly', 10, 2)->default(0);
            $table->decimal('subtotal_yearly', 10, 2)->default(0);

            // Totais por período
            $table->decimal('total_one_time', 10, 2)->default(0);
            $table->decimal('total_monthly', 10, 2)->default(0);
            $table->decimal('total_yearly', 10, 2)->default(0);

            // Status e validade
            $table->string('status', 20)->default('draft');
            $table->date('valid_until')->nullable();

            // Link público para visualização/aceite
            $table->string('public_token', 64)->unique();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
