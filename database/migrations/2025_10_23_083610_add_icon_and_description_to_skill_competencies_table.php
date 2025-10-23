<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skill_competencies', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('competency');
            $table->text('description')->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('skill_competencies', function (Blueprint $table) {
            $table->dropColumn(['icon', 'description']);
        });
    }
};
