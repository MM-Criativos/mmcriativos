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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // 🔗 Relacionamento com cliente (empresa)
            $table->foreignId('client_id')->nullable()
                ->constrained('clients')
                ->onDelete('cascade'); // se o cliente for apagado, remove os contatos também

            // 👤 Informações pessoais do contato
            $table->string('name');                     // Nome completo
            $table->string('role')->nullable();         // Cargo (ex: CEO, Diretor de Marketing)
            $table->string('photo')->nullable();        // Caminho da foto
            $table->string('email')->nullable();        // Email direto
            $table->string('phone')->nullable();        // Telefone direto
            $table->string('linkedin')->nullable();     // Perfil opcional
            $table->string('website')->nullable();      // Site pessoal ou portfólio, se existir

            // ⚙️ Controle interno
            $table->boolean('is_primary')->default(false); // Define se é o responsável principal da empresa
            $table->boolean('is_active')->default(true);   // Se o contato ainda está ativo na empresa

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
