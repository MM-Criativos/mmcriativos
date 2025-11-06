<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_pages', function (Blueprint $table) {
            $table->id();

            // 🔗 Relaciona com o projeto principal
            $table->foreignId('project_id')
                ->constrained('projects') // se já tiver tabela de projetos
                ->onDelete('cascade');

            // 🔗 Referência ao blueprint global
            $table->foreignId('global_page_id')
                ->nullable()
                ->constrained('global_pages')
                ->onDelete('set null');

            $table->string('name');
            $table->string('slug')->unique();

            // Campos de controle e customização
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pages');
    }
};
