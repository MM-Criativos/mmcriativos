<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdr_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Script Principal');
            $table->string('version')->default('v1');
            $table->boolean('is_active')->default(true);
            $table->json('flow');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdr_scripts');
    }
};
