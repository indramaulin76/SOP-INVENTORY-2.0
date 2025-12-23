<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk tracking WIP (Barang Dalam Proses) yang digunakan dalam produksi barang jadi.
     * Ini memungkinkan Dual-Source Production: Raw Material + WIP.
     */
    public function up(): void
    {
        Schema::create('finished_goods_production_wip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_goods_production_id')
                ->constrained('finished_goods_productions')
                ->onDelete('cascade')
                ->name('fk_fg_prod_wip_fg_prod_id');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict')
                ->comment('The WIP product used');
            $table->decimal('quantity', 10, 2)->comment('Quantity of WIP consumed');
            $table->decimal('cost_per_unit', 15, 2)->comment('Cost from inventory batch (FIFO/LIFO)');
            $table->decimal('total_cost', 15, 2)->comment('Auto-calculated: quantity × cost_per_unit');
            $table->timestamps();
            
            $table->index('finished_goods_production_id', 'idx_fg_prod_wip_fg_prod_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_goods_production_wip');
    }
};
