<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_pages', function (Blueprint $table) {
            $table->id();

            // 🔗 Projeto dono da página
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            // 🧩 Referência opcional à biblioteca global (página modelo)
            $table->foreignId('global_page_id')
                ->nullable()
                ->constrained('global_pages')
                ->onDelete('set null');

            // 📄 Informações básicas da página
            $table->string('name');
            $table->string('slug')->unique();

            // ⚙️ Controle de status e ordenação
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
