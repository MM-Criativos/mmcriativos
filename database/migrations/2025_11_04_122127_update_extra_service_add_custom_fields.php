<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extra_service', function (Blueprint $table) {
            // 🔹 adiciona campos opcionais de personalização de preço e desconto
            $table->decimal('custom_price', 10, 2)->nullable()->after('service_id');
            $table->decimal('custom_discount', 10, 2)->nullable()->after('custom_price');
        });
    }

    public function down(): void
    {
        Schema::table('extra_service', function (Blueprint $table) {
            $table->dropColumn(['custom_price', 'custom_discount']);
        });
    }
};
