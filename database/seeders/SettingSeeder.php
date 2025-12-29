<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set(
            'inventory_method',
            'FIFO',
            'string',
            'Metode penilaian persediaan: FIFO, LIFO, atau AVERAGE'
        );

        Setting::set(
            'company_name',
            'SAE Bakery',
            'string',
            'Nama perusahaan'
        );

        Setting::set(
            'currency',
            'IDR',
            'string',
            'Mata uang default'
        );

        Setting::set(
            'low_stock_alert',
            '1',
            'boolean',
            'Aktifkan notifikasi stok rendah'
        );
    }
}
