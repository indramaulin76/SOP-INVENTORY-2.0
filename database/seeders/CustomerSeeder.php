<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'kode_customer' => 'CUST-001',
            'nama_customer' => 'Toko Roti Makmur',
            'alamat' => 'Jl. Merdeka No. 12, Jakarta',
            'telepon' => '021-1234567',
            'email' => 'owner@rotimakmur.com',
            'tipe_customer' => 'reseller',
        ]);

        Customer::create([
            'kode_customer' => 'CUST-002',
            'nama_customer' => 'Sari Roti Mart',
            'alamat' => 'Jl. Ahmad Yani No. 78, Bekasi',
            'telepon' => '021-9876543',
            'email' => 'purchase@sarirotimart.co.id',
            'tipe_customer' => 'reseller',
        ]);

        Customer::create([
            'kode_customer' => 'CUST-003',
            'nama_customer' => 'Coffee Shop Kenangan',
            'alamat' => 'Jl. Sudirman No. 45, Jakarta Selatan',
            'telepon' => '021-5554321',
            'email' => 'order@coffeeshopkenangan.com',
            'tipe_customer' => 'corporate',
        ]);

        Customer::create([
            'kode_customer' => 'CUST-004',
            'nama_customer' => 'Hotel Santika',
            'alamat' => 'Jl. Gatot Subroto No. 123, Jakarta',
            'telepon' => '021-7771234',
            'email' => 'fnb@santika.com',
            'tipe_customer' => 'corporate',
        ]);

        Customer::create([
            'kode_customer' => 'CUST-999',
            'nama_customer' => 'Walk-in Customer',
            'alamat' => '-',
            'telepon' => '-',
            'email' => null,
            'tipe_customer' => 'retail',
        ]);

        Customer::create([
            'kode_customer' => 'CUST-005',
            'nama_customer' => 'Budi Santoso',
            'alamat' => 'Jl. Raya Bogor KM 12, Cibinong',
            'telepon' => '0812-3456-7890',
            'email' => 'budi.santoso@gmail.com',
            'tipe_customer' => 'retail',
        ]);
    }
}
