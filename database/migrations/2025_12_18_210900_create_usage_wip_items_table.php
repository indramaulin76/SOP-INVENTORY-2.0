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
        Schema::create('usage_wip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usage_wip_id')->constrained('usage_wip')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->decimal('harga', 15, 2)->comment('Cost per unit from inventory batch');
            $table->decimal('jumlah', 15, 2)->comment('Auto-calculated: quantity × harga');
            $table->timestamps();
            
            $table->index('usage_wip_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_wip_items');
    }
};
