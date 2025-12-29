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
        Schema::create('finished_goods_productions', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_produksi', 50)->unique()->comment('Production number e.g. PROD-001');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict')->comment('The finished product being produced');
            $table->decimal('quantity_produced', 10, 2)->comment('Amount of finished goods produced');
            $table->text('keterangan')->nullable();
            $table->decimal('total_cost', 15, 2)->default(0)->comment('Total cost of materials used (COGS)');
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('nomor_produksi');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_goods_productions');
    }
};
