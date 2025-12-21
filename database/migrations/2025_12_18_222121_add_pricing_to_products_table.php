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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('harga_beli_default', 15, 2)->default(0)->after('limit_stock');
            $table->decimal('harga_jual_default', 15, 2)->default(0)->after('harga_beli_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['harga_beli_default', 'harga_jual_default']);
        });
    }
};
