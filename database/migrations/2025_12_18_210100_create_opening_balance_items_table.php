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
        Schema::create('opening_balance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opening_balance_id')->constrained('opening_balances')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('jumlah_unit', 10, 2);
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_jual', 15, 2)->default(0)->comment('Can be 0 for raw materials');
            $table->decimal('total', 15, 2)->comment('Auto-calculated: jumlah × harga_beli');
            $table->timestamps();
            
            $table->index('opening_balance_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_balance_items');
    }
};
