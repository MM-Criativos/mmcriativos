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
        Schema::create('budget_payments', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o orçamento principal
            $table->foreignId('budget_id')
                ->constrained()
                ->cascadeOnDelete();

            // Número de parcelas
            $table->unsignedTinyInteger('installments')->default(1);

            // Juros aplicados (%)
            $table->decimal('interest_rate', 5, 2)->default(0);

            // Total final com juros (valor total do orçamento parcelado)
            $table->decimal('total_with_interest', 10, 2)->default(0);

            // Valor individual de cada parcela
            $table->decimal('installment_value', 10, 2)->default(0);

            // Indica se esta opção foi a escolhida no orçamento
            $table->boolean('is_selected')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_payments');
    }
};
