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
        Schema::create('wip_entries', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_faktur', 50)->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('restrict');
            $table->text('keterangan')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0)->comment('Auto-calculated from items');
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('nomor_faktur');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wip_entries');
    }
};
