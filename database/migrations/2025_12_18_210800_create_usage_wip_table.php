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
        Schema::create('usage_wip', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nomor_bukti', 50)->unique();
            $table->string('nama_departemen');
            $table->string('kode_referensi', 50)->unique()->comment('Auto-generated reference code');
            $table->text('keterangan')->nullable();
            $table->decimal('total_nilai', 15, 2)->default(0)->comment('Auto-calculated from items');
            $table->timestamps();
            
            $table->index('tanggal');
            $table->index('nomor_bukti');
            $table->index('kode_referensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_wip');
    }
};
