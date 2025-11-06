<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_page_component', function (Blueprint $table) {
            $table->id();

            // 🔗 Página do projeto (baseada em global_pages)
            $table->foreignId('project_page_id')
                ->constrained('project_pages')
                ->onDelete('cascade');

            // 🔗 Componente narrativo
            $table->foreignId('component_id')
                ->constrained('storytelling_components')
                ->onDelete('cascade');

            // ⚙️ Ordem narrativa personalizada
            $table->integer('order')->default(0);

            // 🎛️ Configurações específicas do projeto
            $table->json('settings')->nullable();

            // 🧱 Controle de exibição
            $table->boolean('is_visible')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_page_component');
    }
};
