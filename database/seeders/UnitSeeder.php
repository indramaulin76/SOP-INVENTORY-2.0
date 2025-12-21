<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['nama_satuan' => 'Kilogram', 'singkatan' => 'kg'],
            ['nama_satuan' => 'Gram', 'singkatan' => 'g'],
            ['nama_satuan' => 'Pieces', 'singkatan' => 'pcs'],
            ['nama_satuan' => 'Box', 'singkatan' => 'box'],
            ['nama_satuan' => 'Liter', 'singkatan' => 'L'],
            ['nama_satuan' => 'Milliliter', 'singkatan' => 'ml'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
