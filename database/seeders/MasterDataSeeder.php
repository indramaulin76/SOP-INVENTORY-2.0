<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the master data seeds.
     * Contains ONLY essential data for the app to function.
     * NO dummy transactions or products.
     */
    public function run(): void
    {
        $this->command->info('👥 Creating users...');
        $this->createUsers();

        $this->command->info('📦 Creating master data...');
        $this->createUnits();
        $this->createCategories();
        $this->createDepartments();

        $this->command->info('⚙️ Creating settings...');
        $this->createSettings();
    }

    /**
     * Create demo users
     */
    private function createUsers(): void
    {
        $now = now();

        DB::table('users')->insertOrIgnore([
            [
                'name' => 'Owner / Pimpinan',
                'email' => 'pimpinan@saebakery.com',
                'password' => Hash::make('password'),
                'role' => 'Pimpinan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Admin Inventory',
                'email' => 'admin@saebakery.com',
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Staff Gudang',
                'email' => 'karyawan@saebakery.com',
                'password' => Hash::make('password'),
                'role' => 'Karyawan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Create units
     */
    private function createUnits(): void
    {
        $now = now();
        $units = [
            ['nama_satuan' => 'Kilogram', 'singkatan' => 'kg'],
            ['nama_satuan' => 'Pieces', 'singkatan' => 'pcs'],
            ['nama_satuan' => 'Liter', 'singkatan' => 'ltr'],
            ['nama_satuan' => 'Zak', 'singkatan' => 'zak'],
            ['nama_satuan' => 'Gram', 'singkatan' => 'gr'],
            ['nama_satuan' => 'Pack', 'singkatan' => 'pack'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insertOrIgnore([
                'nama_satuan' => $unit['nama_satuan'],
                'singkatan' => $unit['singkatan'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Create categories
     */
    private function createCategories(): void
    {
        $now = now();
        $categories = [
            ['kode' => 'BB', 'nama' => 'Bahan Baku'],
            ['kode' => 'BJ', 'nama' => 'Barang Jadi'],
            ['kode' => 'KM', 'nama' => 'Kemasan'],
            ['kode' => 'WIP', 'nama' => 'Barang Dalam Proses'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insertOrIgnore([
                'kode_kategori' => $cat['kode'],
                'nama_kategori' => $cat['nama'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Create departments
     */
    private function createDepartments(): void
    {
        $now = now();
        $departments = [
            ['kode' => 'DEPT-001', 'nama' => 'Kitchen', 'keterangan' => 'Bagian produksi dan pengolahan'],
            ['kode' => 'DEPT-002', 'nama' => 'Warehouse', 'keterangan' => 'Bagian gudang penyimpanan'],
            ['kode' => 'DEPT-003', 'nama' => 'Store Front', 'keterangan' => 'Bagian penjualan toko'],
            ['kode' => 'DEPT-004', 'nama' => 'Packaging', 'keterangan' => 'Bagian pengemasan'],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insertOrIgnore([
                'kode_departemen' => $dept['kode'],
                'nama_departemen' => $dept['nama'],
                'keterangan' => $dept['keterangan'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Create application settings
     */
    private function createSettings(): void
    {
        $now = now();
        $settings = [
            ['key' => 'company_name', 'value' => 'Sae Bakery'],
            ['key' => 'company_address', 'value' => 'Jl. Contoh No. 123, Kota Demo'],
            ['key' => 'company_phone', 'value' => '021-1234567'],
            ['key' => 'hpp_method', 'value' => 'FIFO'],
            ['key' => 'currency', 'value' => 'IDR'],
            ['key' => 'date_format', 'value' => 'd/m/Y'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insertOrIgnore([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
