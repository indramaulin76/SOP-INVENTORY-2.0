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
        Schema::create('sales_finished_goods_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_finished_goods_id')->constrained('sales_finished_goods')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->decimal('harga_jual', 15, 2)->comment('Selling price per unit');
            $table->decimal('harga_pokok', 15, 2)->comment('COGS per unit from inventory batch');
            $table->decimal('jumlah', 15, 2)->comment('Auto-calculated: quantity × harga_jual');
            $table->decimal('total_cogs', 15, 2)->comment('Auto-calculated: quantity × harga_pokok');
            $table->decimal('profit', 15, 2)->comment('Auto-calculated: jumlah - total_cogs');
            $table->timestamps();
            
            $table->index('sales_finished_goods_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_finished_goods_items');
    }
};
