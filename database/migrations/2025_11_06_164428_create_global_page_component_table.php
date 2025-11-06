<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('global_page_component', function (Blueprint $table) {
            $table->id();

            // 🔗 Liga a página global
            $table->foreignId('page_id')
                ->constrained('global_pages')
                ->onDelete('cascade');

            // 🔗 Liga o componente narrativo
            $table->foreignId('component_id')
                ->constrained('storytelling_components')
                ->onDelete('cascade');

            // ⚙️ Ordem narrativa dentro da página
            $table->integer('order')->default(0);

            // 🧩 Customizações (opcional — variações, layouts, etc.)
            $table->json('settings')->nullable();

            $table->timestamps();

            // Evita duplicidade de componentes na mesma página
            $table->unique(['page_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_page_component');
    }
};
