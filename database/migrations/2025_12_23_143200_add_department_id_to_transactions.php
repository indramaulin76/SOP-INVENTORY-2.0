<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan department_id sebagai foreign key ke tabel
     * usage_raw_materials dan wip_entries untuk integrasi departemen.
     */
    public function up(): void
    {
        // Add department_id to usage_raw_materials
        Schema::table('usage_raw_materials', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('nama_departemen')
                  ->constrained('departments')
                  ->onDelete('set null');
            
            $table->index('department_id');
        });

        // Add department_id to wip_entries
        Schema::table('wip_entries', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('supplier_id')
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
        Schema::table('usage_raw_materials', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('wip_entries', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
