<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vee_execution_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('note_id')->unique();
            $table->string('note_type', 60)->index(); // diagnosis, fix_attempt, summary, analysis
            $table->string('tool_name', 160)->nullable()->index();
            $table->string('session_id', 120)->nullable()->index();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title', 255);
            $table->text('content');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vee_execution_notes');
    }
};
