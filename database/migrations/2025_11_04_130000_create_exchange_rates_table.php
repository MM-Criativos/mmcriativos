<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3); // USD, EUR, etc. (para BRL => 1.0)
            $table->decimal('rate_to_brl', 12, 6); // quanto 1 unidade da moeda vale em BRL
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index(['currency', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};

