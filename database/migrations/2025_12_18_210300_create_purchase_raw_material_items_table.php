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
        Schema::create('purchase_raw_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_raw_material_id')->constrained('purchase_raw_materials')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('jumlah', 15, 2)->comment('Auto-calculated: quantity × harga_beli');
            $table->timestamps();
            
            $table->index('purchase_raw_material_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_raw_material_items');
    }
};
