<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // 🔸 Adiciona o campo planned_at
            $table->timestamp('planned_at')->nullable()->after('status');

            // 🔸 Remove o campo progress_notes
            if (Schema::hasColumn('project_tasks', 'progress_notes')) {
                $table->dropColumn('progress_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // Reverte as alterações
            $table->text('progress_notes')->nullable()->after('assigned_to');
            $table->dropColumn('planned_at');
        });
    }
};
