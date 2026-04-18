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
        Schema::create('vee_mcp_calls', function (Blueprint $table) {
            $table->id();
            $table->uuid('call_id')->index();
            $table->string('tool_name', 160)->index();
            $table->string('status', 32)->index();
            $table->string('permission_level', 40)->nullable()->index();
            $table->boolean('is_write')->default(false)->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vee_mcp_calls');
    }
};
