<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create([
            'kode_supplier' => 'SUP-001',
            'nama_supplier' => 'PT Terigu Jaya',
            'alamat' => 'Jl. Raya Industri No. 123, Jakarta Pusat',
            'telepon' => '021-5551234',
            'email' => 'sales@terigujaya.com',
        ]);

        Supplier::create([
            'kode_supplier' => 'SUP-002',
            'nama_supplier' => 'CV Susu Segar',
            'alamat' => 'Jl. Peternakan No. 45, Bogor',
            'telepon' => '0251-8765432',
            'email' => 'order@sususegar.com',
        ]);

        Supplier::create([
            'kode_supplier' => 'SUP-003',
            'nama_supplier' => 'Aneka Rasa Food Supplier',
            'alamat' => 'Jl. Gatot Subroto No. 789, Bandung',
            'telepon' => '022-7654321',
            'email' => 'info@anekarasa.co.id',
        ]);

        Supplier::create([
            'kode_supplier' => 'SUP-004',
            'nama_supplier' => 'Toko Coklat Indonesia',
            'alamat' => 'Jl. Soekarno Hatta No. 234, Jakarta Selatan',
            'telepon' => '021-7778888',
            'email' => 'sales@coklatindonesia.com',
        ]);

        Supplier::create([
            'kode_supplier' => 'SUP-005',
            'nama_supplier' => 'Packaging Solutions',
            'alamat' => 'Jl. Industri Raya No. 56, Tangerang',
            'telepon' => '021-5559999',
            'email' => 'order@packagingsolutions.id',
        ]);
    }
}
