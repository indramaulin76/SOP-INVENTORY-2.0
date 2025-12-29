<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Comprehensive seeder untuk demo aplikasi Sae Bakery Inventory System.
     * 
     * Scenario:
     * - Master Data: Users, Units, Categories, Departments, Settings (via MasterDataSeeder)
     * - Dummy Data: Products & Transactions (Optional, for Demo Only)
     */
    public function run(): void
    {
        $this->info('🧹 Cleaning database...');
        $this->cleanDatabase();

        // 1. Jalankan Master Data (WAJIB ADA)
        // Ini memastikan dropdown user, satuan, dan kategori TERISI
        // meskipun belum ada barang/transaksi.
        $this->call(MasterDataSeeder::class);

        // 2. Dummy Data (OPSIONAL - Uncomment jika butuh data demo)
        $this->seedDummyData();
        
        $this->info('✅ Seeding complete!');
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * Clean all relevant tables
     */
    private function cleanDatabase(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        $tables = [
            'sales_finished_goods_items', 'sales_finished_goods',
            'finished_goods_production_materials', 'finished_goods_productions',
            'usage_wip_items', 'usage_wip',
            'wip_entry_items', 'wip_entries',
            'usage_raw_material_items', 'usage_raw_materials',
            'purchase_raw_material_items', 'purchase_raw_materials',
            'opening_balance_items', 'opening_balances',
            'inventory_batches', 'stock_opname_items', 'stock_opnames',
            'products',
            'departments', 'categories', 'units', 'users', 'settings',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }

    /**
     * Helper to load dummy data (Products & Transactions)
     * Requires Master Data to be present first.
     */
    private function seedDummyData(): void
    {
        $this->info('Creating dummy data...');
        
        // Ambil ID dari Master Data yang sudah di-seed
        $units = DB::table('units')->pluck('id', 'singkatan')->toArray();
        $categories = DB::table('categories')->pluck('id', 'kode_kategori')->toArray();

        $this->info('🛒 Creating products (Dummy)...');
        $products = $this->createProducts($units, $categories);

        $this->info('📊 Creating transactions (Dummy)...');
        $this->createOpeningBalance($products);
        $this->createUsageTransactions($products);
        $this->createProductionResults($products);
        $this->createSalesTransactions($products);
    }

    /**
     * Create products (Dummy)
     */
    private function createProducts(array $units, array $categories): array
    {
        $now = now();
        $products = [];

        // Raw Materials (Bahan Baku)
        $rawMaterials = [
            ['kode' => 'BB-0001', 'nama' => 'Tepung Terigu Segitiga Biru', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 50, 'harga_beli' => 12000],
            ['kode' => 'BB-0002', 'nama' => 'Telur Ayam Segar', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 20, 'harga_beli' => 28000],
            ['kode' => 'BB-0003', 'nama' => 'Gula Pasir', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 30, 'harga_beli' => 16000],
            ['kode' => 'BB-0004', 'nama' => 'Mentega Wijsman', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 10, 'harga_beli' => 145000],
            ['kode' => 'BB-0005', 'nama' => 'Susu Bubuk Full Cream', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 10, 'harga_beli' => 95000],
            ['kode' => 'BB-0006', 'nama' => 'Ragi Instan Fermipan', 'unit' => 'gr', 'cat' => 'BB', 'limit' => 500, 'harga_beli' => 150],
            ['kode' => 'BB-0007', 'nama' => 'Cokelat Bubuk', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 5, 'harga_beli' => 85000],
            ['kode' => 'BB-0008', 'nama' => 'Keju Cheddar', 'unit' => 'kg', 'cat' => 'BB', 'limit' => 5, 'harga_beli' => 120000],
        ];

        foreach ($rawMaterials as $item) {
            $id = DB::table('products')->insertGetId([
                'kode_barang' => $item['kode'],
                'nama_barang' => $item['nama'],
                'unit_id' => $units[$item['unit']],
                'category_id' => $categories[$item['cat']],
                'limit_stock' => $item['limit'],
                'harga_beli_default' => $item['harga_beli'],
                'harga_jual_default' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $products[$item['kode']] = ['id' => $id, 'harga_beli' => $item['harga_beli']];
        }

        // Finished Goods (Barang Jadi)
        $finishedGoods = [
            ['kode' => 'BJ-0001', 'nama' => 'Roti Tawar Spesial', 'unit' => 'pcs', 'cat' => 'BJ', 'limit' => 20, 'hpp' => 8000, 'harga_jual' => 15000],
            ['kode' => 'BJ-0002', 'nama' => 'Roti Cokelat', 'unit' => 'pcs', 'cat' => 'BJ', 'limit' => 30, 'hpp' => 5000, 'harga_jual' => 10000],
            ['kode' => 'BJ-0003', 'nama' => 'Donat Gula', 'unit' => 'pcs', 'cat' => 'BJ', 'limit' => 50, 'hpp' => 3500, 'harga_jual' => 7000],
            ['kode' => 'BJ-0004', 'nama' => 'Croissant Butter', 'unit' => 'pcs', 'cat' => 'BJ', 'limit' => 20, 'hpp' => 12000, 'harga_jual' => 25000],
            ['kode' => 'BJ-0005', 'nama' => 'Cheese Cake Slice', 'unit' => 'pcs', 'cat' => 'BJ', 'limit' => 15, 'hpp' => 18000, 'harga_jual' => 35000],
        ];

        foreach ($finishedGoods as $item) {
            $id = DB::table('products')->insertGetId([
                'kode_barang' => $item['kode'],
                'nama_barang' => $item['nama'],
                'unit_id' => $units[$item['unit']],
                'category_id' => $categories[$item['cat']],
                'limit_stock' => $item['limit'],
                'harga_beli_default' => $item['hpp'],
                'harga_jual_default' => $item['harga_jual'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $products[$item['kode']] = ['id' => $id, 'harga_beli' => $item['hpp'], 'harga_jual' => $item['harga_jual']];
        }

        // Packaging (Kemasan)
        $packaging = [
            ['kode' => 'KM-0001', 'nama' => 'Plastik Roti Tawar', 'unit' => 'pack', 'cat' => 'KM', 'limit' => 100, 'harga_beli' => 500],
            ['kode' => 'KM-0002', 'nama' => 'Box Kue', 'unit' => 'pcs', 'cat' => 'KM', 'limit' => 50, 'harga_beli' => 2500],
        ];

        foreach ($packaging as $item) {
            $id = DB::table('products')->insertGetId([
                'kode_barang' => $item['kode'],
                'nama_barang' => $item['nama'],
                'unit_id' => $units[$item['unit']],
                'category_id' => $categories[$item['cat']],
                'limit_stock' => $item['limit'],
                'harga_beli_default' => $item['harga_beli'],
                'harga_jual_default' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $products[$item['kode']] = ['id' => $id, 'harga_beli' => $item['harga_beli']];
        }

        return $products;
    }

    /**
     * Create Opening Balance (Saldo Awal) - Date: 2025-12-01
     */
    private function createOpeningBalance(array $products): void
    {
        $date = Carbon::parse('2025-12-01');
        $now = now();

        // Create Opening Balance record
        $obId = DB::table('opening_balances')->insertGetId([
            'tanggal' => $date,
            'keterangan' => 'Saldo Awal Bulan Desember 2025',
            'total_nilai' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $totalNilai = 0;
        $obItems = [
            ['kode' => 'BB-0001', 'qty' => 100, 'harga' => 12000], // 100kg Tepung
            ['kode' => 'BB-0002', 'qty' => 50, 'harga' => 28000],  // 50kg Telur
            ['kode' => 'BB-0003', 'qty' => 80, 'harga' => 16000],  // 80kg Gula
            ['kode' => 'BB-0004', 'qty' => 25, 'harga' => 145000], // 25kg Mentega
            ['kode' => 'BB-0005', 'qty' => 15, 'harga' => 95000],  // 15kg Susu Bubuk
            ['kode' => 'BB-0006', 'qty' => 2000, 'harga' => 150],  // 2000gr Ragi
            ['kode' => 'BB-0007', 'qty' => 10, 'harga' => 85000],  // 10kg Cokelat
            ['kode' => 'BB-0008', 'qty' => 8, 'harga' => 120000],  // 8kg Keju
            ['kode' => 'KM-0001', 'qty' => 500, 'harga' => 500],   // 500 pack Plastik
            ['kode' => 'KM-0002', 'qty' => 100, 'harga' => 2500],  // 100 pcs Box
        ];

        foreach ($obItems as $item) {
            $productId = $products[$item['kode']]['id'];
            $subtotal = $item['qty'] * $item['harga'];
            $totalNilai += $subtotal;

            // Create OB Item
            $itemId = DB::table('opening_balance_items')->insertGetId([
                'opening_balance_id' => $obId,
                'product_id' => $productId,
                'jumlah_unit' => $item['qty'],
                'harga_beli' => $item['harga'],
                'harga_jual' => 0,
                'total' => $subtotal,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create Inventory Batch (CRITICAL for HPP calculation!)
            DB::table('inventory_batches')->insert([
                'product_id' => $productId,
                'source' => 'opening_balance',
                'batch_no' => 'OB-' . $obId . '-' . $itemId,
                'date_in' => $date,
                'qty_initial' => $item['qty'],
                'qty_current' => $item['qty'],
                'price_per_unit' => $item['harga'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Update total nilai
        DB::table('opening_balances')->where('id', $obId)->update(['total_nilai' => $totalNilai]);
    }

    /**
     * Create Usage Transactions (Pemakaian Bahan Baku) - Multiple dates
     */
    private function createUsageTransactions(array $products): void
    {
        $now = now();

        $usages = [
            ['date' => '2025-12-05', 'ref' => 'USE-001', 'note' => 'Produksi Roti Tawar Batch 1', 'items' => [
                ['kode' => 'BB-0001', 'qty' => 20],  // 20kg Tepung
                ['kode' => 'BB-0002', 'qty' => 5],   // 5kg Telur
                ['kode' => 'BB-0004', 'qty' => 3],   // 3kg Mentega
                ['kode' => 'BB-0006', 'qty' => 200], // 200gr Ragi
            ]],
            ['date' => '2025-12-10', 'ref' => 'USE-002', 'note' => 'Produksi Roti Cokelat', 'items' => [
                ['kode' => 'BB-0001', 'qty' => 15],  // 15kg Tepung
                ['kode' => 'BB-0002', 'qty' => 3],   // 3kg Telur
                ['kode' => 'BB-0007', 'qty' => 2],   // 2kg Cokelat
            ]],
            ['date' => '2025-12-15', 'ref' => 'USE-003', 'note' => 'Produksi Donat', 'items' => [
                ['kode' => 'BB-0001', 'qty' => 10],  // 10kg Tepung
                ['kode' => 'BB-0003', 'qty' => 5],   // 5kg Gula
                ['kode' => 'BB-0002', 'qty' => 2],   // 2kg Telur
            ]],
            ['date' => '2025-12-20', 'ref' => 'USE-004', 'note' => 'Produksi Croissant', 'items' => [
                ['kode' => 'BB-0001', 'qty' => 8],   // 8kg Tepung
                ['kode' => 'BB-0004', 'qty' => 5],   // 5kg Mentega
                ['kode' => 'BB-0002', 'qty' => 2],   // 2kg Telur
            ]],
        ];

        foreach ($usages as $usage) {
            $date = Carbon::parse($usage['date']);
            
            $usageId = DB::table('usage_raw_materials')->insertGetId([
                'tanggal' => $date,
                'nomor_bukti' => $usage['ref'],
                'nama_departemen' => 'Kitchen',
                'kode_referensi' => $usage['ref'],
                'keterangan' => $usage['note'],
                'total_nilai' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($usage['items'] as $item) {
                $productId = $products[$item['kode']]['id'];
                $harga = $products[$item['kode']]['harga_beli'];
                $jumlah = $item['qty'] * $harga;

                // Create usage item
                DB::table('usage_raw_material_items')->insert([
                    'usage_raw_material_id' => $usageId,
                    'product_id' => $productId,
                    'quantity' => $item['qty'],
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Reduce stock from inventory batch (FIFO)
                $this->reduceStock($productId, $item['qty']);
            }
        }
    }

    /**
     * Create Production Results (Barang Jadi Masuk)
     */
    private function createProductionResults(array $products): void
    {
        $now = now();

        $productions = [
            ['date' => '2025-12-06', 'kode' => 'BJ-0001', 'qty' => 50, 'hpp' => 8000],  // 50 Roti Tawar
            ['date' => '2025-12-11', 'kode' => 'BJ-0002', 'qty' => 80, 'hpp' => 5000],  // 80 Roti Cokelat
            ['date' => '2025-12-16', 'kode' => 'BJ-0003', 'qty' => 100, 'hpp' => 3500], // 100 Donat
            ['date' => '2025-12-21', 'kode' => 'BJ-0004', 'qty' => 30, 'hpp' => 12000], // 30 Croissant
        ];

        foreach ($productions as $prod) {
            $date = Carbon::parse($prod['date']);
            $productId = $products[$prod['kode']]['id'];

            // Create inventory batch for finished goods
            DB::table('inventory_batches')->insert([
                'product_id' => $productId,
                'source' => 'production_result',
                'batch_no' => 'PROD-' . $prod['kode'] . '-' . $date->format('Ymd'),
                'date_in' => $date,
                'qty_initial' => $prod['qty'],
                'qty_current' => $prod['qty'],
                'price_per_unit' => $prod['hpp'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Create Sales Transactions (Penjualan Barang Jadi)
     */
    private function createSalesTransactions(array $products): void
    {
        $now = now();

        $sales = [
            ['date' => '2025-12-08', 'ref' => 'INV-001', 'note' => 'Penjualan Reguler', 'items' => [
                ['kode' => 'BJ-0001', 'qty' => 10, 'harga' => 15000], // 10 Roti Tawar
            ]],
            ['date' => '2025-12-12', 'ref' => 'INV-002', 'note' => 'Penjualan Cafe Partner', 'items' => [
                ['kode' => 'BJ-0001', 'qty' => 15, 'harga' => 15000], // 15 Roti Tawar
                ['kode' => 'BJ-0002', 'qty' => 20, 'harga' => 10000], // 20 Roti Cokelat
            ]],
            ['date' => '2025-12-18', 'ref' => 'INV-003', 'note' => 'Order Catering', 'items' => [
                ['kode' => 'BJ-0003', 'qty' => 50, 'harga' => 7000],  // 50 Donat
                ['kode' => 'BJ-0002', 'qty' => 30, 'harga' => 10000], // 30 Roti Cokelat
            ]],
            ['date' => '2025-12-22', 'ref' => 'INV-004', 'note' => 'Penjualan Weekend', 'items' => [
                ['kode' => 'BJ-0004', 'qty' => 15, 'harga' => 25000], // 15 Croissant
                ['kode' => 'BJ-0001', 'qty' => 10, 'harga' => 15000], // 10 Roti Tawar
                ['kode' => 'BJ-0003', 'qty' => 25, 'harga' => 7000],  // 25 Donat
            ]],
        ];

        foreach ($sales as $sale) {
            $date = Carbon::parse($sale['date']);
            $totalPenjualan = 0;
            $totalHpp = 0;

            // Calculate totals first
            foreach ($sale['items'] as $item) {
                $hpp = $products[$item['kode']]['harga_beli'];
                $totalPenjualan += $item['qty'] * $item['harga'];
                $totalHpp += $item['qty'] * $hpp;
            }

            $salesId = DB::table('sales_finished_goods')->insertGetId([
                'tanggal' => $date,
                'nomor_bukti' => $sale['ref'],
                'keterangan' => $sale['note'],
                'total_nilai' => $totalPenjualan,
                'total_cogs' => $totalHpp,
                'total_profit' => $totalPenjualan - $totalHpp,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($sale['items'] as $item) {
                $productId = $products[$item['kode']]['id'];
                $hpp = $products[$item['kode']]['harga_beli'];
                $jumlah = $item['qty'] * $item['harga'];
                $totalCogs = $item['qty'] * $hpp;
                $profit = $jumlah - $totalCogs;

                DB::table('sales_finished_goods_items')->insert([
                    'sales_finished_goods_id' => $salesId,
                    'product_id' => $productId,
                    'quantity' => $item['qty'],
                    'harga_jual' => $item['harga'],
                    'harga_pokok' => $hpp,
                    'jumlah' => $jumlah,
                    'total_cogs' => $totalCogs,
                    'profit' => $profit,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Reduce stock from inventory batch
                $this->reduceStock($productId, $item['qty']);
            }
        }
    }

    /**
     * Reduce stock from inventory batches (FIFO method)
     */
    private function reduceStock(int $productId, float $quantity): void
    {
        $remaining = $quantity;

        $batches = DB::table('inventory_batches')
            ->where('product_id', $productId)
            ->where('qty_current', '>', 0)
            ->orderBy('date_in', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $available = $batch->qty_current;
            $toDeduct = min($available, $remaining);

            DB::table('inventory_batches')
                ->where('id', $batch->id)
                ->update(['qty_current' => $available - $toDeduct]);

            $remaining -= $toDeduct;
        }
    }
}
