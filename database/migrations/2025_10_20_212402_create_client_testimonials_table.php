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
        Schema::create('client_testimonials', function (Blueprint $table) {
            $table->id();

            // 🔗 Relacionamentos
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade'); // se o cliente for apagado, apaga os depoimentos também

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete(); // se o contato for apagado, zera o campo mas mantém o depoimento

            // 💬 Conteúdo do depoimento
            $table->string('title')->nullable();        // opcional — ex: “Experiência incrível”
            $table->text('testimonial');                // o texto do depoimento em si
            $table->unsignedTinyInteger('rating')->nullable(); // nota opcional (1–5 estrelas)
            $table->string('photo')->nullable();        // foto do autor (pode sobrescrever a do contato)
            $table->string('position')->nullable();     // cargo exibido no depoimento, se quiser sobrescrever o contato
            $table->string('company')->nullable();      // empresa exibida (pode ser redundante, mas útil em vitrine)

            // ⚙️ Controle interno
            $table->boolean('is_featured')->default(false); // destaque no site, se aplicável
            $table->boolean('is_visible')->default(true);   // exibir ou ocultar no site

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_testimonials');
    }
};
