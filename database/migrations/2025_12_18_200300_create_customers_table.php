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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer', 50)->unique();
            $table->string('nama_customer');
            $table->text('alamat');
            $table->string('telepon', 20);
            $table->string('email')->nullable()->unique();
            $table->enum('tipe_customer', ['retail', 'reseller', 'corporate'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('kode_customer');
            $table->index('nama_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
