<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kode_kategori' => 'BB',
                'nama_kategori' => 'Bahan Baku',
                'keterangan' => 'Bahan mentah untuk produksi (tepung, gula, telur, dll)',
            ],
            [
                'kode_kategori' => 'BJ',
                'nama_kategori' => 'Barang Jadi',
                'keterangan' => 'Produk jadi siap jual (roti, kue, pastry)',
            ],
            [
                'kode_kategori' => 'KMS',
                'nama_kategori' => 'Kemasan',
                'keterangan' => 'Material packaging (box, plastik, label)',
            ],
            [
                'kode_kategori' => 'BDP',
                'nama_kategori' => 'Barang Dalam Proses',
                'keterangan' => 'Barang setengah jadi (adonan, filling)',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
