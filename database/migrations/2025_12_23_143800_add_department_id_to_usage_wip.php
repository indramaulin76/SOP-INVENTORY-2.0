<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan department_id ke tabel usage_wip.
     */
    public function up(): void
    {
        Schema::table('usage_wip', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('nama_departemen')
                  ->constrained('departments')
                  ->onDelete('set null');
            
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usage_wip', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
