<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->unique()->after('service_id')->constrained('budgets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
        });
    }
};
