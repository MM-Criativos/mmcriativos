<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vee_incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('incident_id')->unique();
            $table->string('title', 255);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
            $table->enum('status', ['open', 'investigating', 'resolved'])->default('open')->index();
            $table->string('affected_service', 120)->nullable()->index();
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vee_incidents');
    }
};
