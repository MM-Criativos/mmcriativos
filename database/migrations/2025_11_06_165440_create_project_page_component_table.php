<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_page_component', function (Blueprint $table) {
            $table->id();

            // 🔗 Página do projeto
            $table->foreignId('project_page_id')
                ->constrained('project_pages')
                ->onDelete('cascade');

            // 🔗 Componente narrativo usado no projeto
            $table->foreignId('component_id')
                ->constrained('storytelling_components')
                ->onDelete('cascade');

            // 🧩 Referência opcional à biblioteca global de componentes (modelo base)
            $table->foreignId('global_component_id')
                ->nullable()
                ->constrained('storytelling_components')
                ->onDelete('set null');

            // ⚙️ Ordem narrativa personalizada
            $table->integer('order')->default(0);

            // 🎛️ Configurações específicas do projeto (props customizados, textos, cores etc.)
            $table->json('settings')->nullable();

            // 🧱 Controle de exibição
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            // 🔒 Evita duplicações do mesmo componente na mesma página
            $table->unique(['project_page_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_page_component');
    }
};
