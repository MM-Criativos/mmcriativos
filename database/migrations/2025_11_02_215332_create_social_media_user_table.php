<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_user', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para usuários
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Chave estrangeira para redes sociais (ajuste principal)
            $table->foreignId('social_media_id')
                ->constrained('social_medias') // <-- nome da tabela correta
                ->onDelete('cascade');

            // Link do perfil do usuário na rede social
            $table->string('url')->nullable();

            $table->timestamps();

            // Evita duplicação
            $table->unique(['user_id', 'social_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_user');
    }
};
