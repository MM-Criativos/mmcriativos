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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();

            // Identificador interno
            $table->string('key')->unique();
            // Ex: budget_sent, budget_accepted, budget_declined, budget_expired

            // Informações básicas
            $table->string('name'); // nome legível ex: "Envio de orçamento"
            $table->string('subject'); // assunto do e-mail

            // Corpo e layout
            $table->text('body'); // corpo do e-mail com variáveis {{client_name}}, {{budget_link}}, etc.
            $table->text('footer')->nullable(); // assinatura ou observações padrão

            // Controle
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
