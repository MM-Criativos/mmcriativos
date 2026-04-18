<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vee_action_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('approval_id')->unique();
            $table->string('action_name', 120)->index();
            $table->string('tool_name', 160)->index();
            $table->string('status', 32)->default('pending')->index();
            $table->text('summary')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('meta')->nullable();
            $table->string('approved_by', 120)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_by', 120)->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vee_action_approvals');
    }
};
