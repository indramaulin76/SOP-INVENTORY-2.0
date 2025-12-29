<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table tracks inventory batches for FIFO/LIFO/AVERAGE costing methods.
     * Each purchase, production, or opening balance creates a new batch.
     * When stock is used, batches are consumed based on the selected method.
     */
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_no')->comment('Invoice/Production number');
            $table->enum('source', ['purchase', 'opening_balance', 'production_result', 'adjustment'])->comment('Origin of this batch');
            $table->date('date_in')->comment('Critical for FIFO/LIFO sorting');
            $table->decimal('qty_initial', 10, 2)->comment('Original quantity entered');
            $table->decimal('qty_current', 10, 2)->comment('Remaining stock in this batch');
            $table->decimal('price_per_unit', 15, 2)->comment('Cost/HPP for this specific batch');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['product_id', 'date_in']);
            $table->index(['product_id', 'qty_current']);
            $table->index('batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
