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
        Schema::create('sales_finished_goods', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_bukti', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->text('keterangan')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0)->comment('Auto-calculated total sales amount');
            $table->decimal('total_cogs', 15, 2)->default(0)->comment('Total cost of goods sold');
            $table->decimal('total_profit', 15, 2)->default(0)->comment('Auto-calculated: total_nilai - total_cogs');
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('nomor_bukti');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_finished_goods');
    }
};
