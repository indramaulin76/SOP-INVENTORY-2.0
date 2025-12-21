<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Comprehensive Test Data Seeder for FIFO/LIFO/AVERAGE Testing
 * 
 * Creates:
 * - 3 Suppliers
 * - 3 Customers  
 * - 10 Products (across all categories)
 * - Multiple Inventory Batches with varying prices for testing
 * 
 * Test Scenarios:
 * Product "Tepung Terigu Premium" (BB-001):
 *   Batch 1: 100 kg @ Rp 10,000 (oldest)
 *   Batch 2: 100 kg @ Rp 12,000 (middle)
 *   Batch 3: 100 kg @ Rp 15,000 (newest)
 *   TOTAL: 300 kg, Value: Rp 3,700,000
 *   
 *   Expected COGS for 150 kg:
 *   - FIFO: Rp 1,600,000 (100×10K + 50×12K)
 *   - LIFO: Rp 2,100,000 (100×15K + 50×12K)
 *   - AVG:  Rp 1,850,000 (150×12,333)
 */
class ComprehensiveTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating Comprehensive Test Data...');
        $this->command->newLine();
        
        // ===== SUPPLIERS =====
        $this->command->info('📦 Creating Suppliers...');
        $suppliers = [
            ['kode_supplier' => 'SUP-001', 'nama_supplier' => 'PT Bogasari Flour Mills', 'alamat' => 'Jakarta', 'telepon' => '021-1234567'],
            ['kode_supplier' => 'SUP-002', 'nama_supplier' => 'CV Gula Manis Jaya', 'alamat' => 'Surabaya', 'telepon' => '031-7654321'],
            ['kode_supplier' => 'SUP-003', 'nama_supplier' => 'UD Mentega Berkah', 'alamat' => 'Bandung', 'telepon' => '022-9876543'],
        ];
        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['kode_supplier' => $s['kode_supplier']], $s);
        }
        $this->command->info('  ✓ 3 suppliers created');
        
        // ===== CUSTOMERS =====
        $this->command->info('👥 Creating Customers...');
        $customers = [
            ['kode_customer' => 'CUST-001', 'nama_customer' => 'Toko Roti Makmur', 'alamat' => 'Jl. Sudirman No. 10', 'telepon' => '0812-1111-2222'],
            ['kode_customer' => 'CUST-002', 'nama_customer' => 'Cafe Kopi Kenangan', 'alamat' => 'Jl. Gatot Subroto No. 5', 'telepon' => '0813-3333-4444'],
            ['kode_customer' => 'CUST-003', 'nama_customer' => 'Hotel Santika Catering', 'alamat' => 'Jl. Thamrin No. 100', 'telepon' => '0814-5555-6666'],
        ];
        foreach ($customers as $c) {
            Customer::firstOrCreate(['kode_customer' => $c['kode_customer']], $c);
        }
        $this->command->info('  ✓ 3 customers created');
        
        // ===== GET CATEGORIES & UNITS =====
        $bahanBaku = Category::where('kode_kategori', 'BB')->first();
        $barangJadi = Category::where('kode_kategori', 'BJ')->first();
        $wip = Category::where('kode_kategori', 'WIP')->first();
        
        $kg = Unit::where('singkatan', 'Kg')->orWhere('singkatan', 'kg')->first();
        $pcs = Unit::where('singkatan', 'Pcs')->orWhere('singkatan', 'pcs')->first();
        $ltr = Unit::where('singkatan', 'Ltr')->orWhere('singkatan', 'L')->first();
        
        // ===== PRODUCTS =====
        $this->command->info('📋 Creating Products...');
        
        $products = [];
        
        // Bahan Baku
        $products['tepung'] = Product::create([
            'kode_barang' => 'BB-001',
            'nama_barang' => 'Tepung Terigu Premium',
            'category_id' => $bahanBaku->id,
            'unit_id' => $kg->id,
            'harga_beli_default' => 12333,
            'harga_jual_default' => 15000,
            'limit_stock' => 50,
        ]);
        
        $products['gula'] = Product::create([
            'kode_barang' => 'BB-002',
            'nama_barang' => 'Gula Pasir Kristal',
            'category_id' => $bahanBaku->id,
            'unit_id' => $kg->id,
            'harga_beli_default' => 14000,
            'harga_jual_default' => 16000,
            'limit_stock' => 30,
        ]);
        
        $products['mentega'] = Product::create([
            'kode_barang' => 'BB-003',
            'nama_barang' => 'Mentega Wisman',
            'category_id' => $bahanBaku->id,
            'unit_id' => $kg->id,
            'harga_beli_default' => 45000,
            'harga_jual_default' => 50000,
            'limit_stock' => 10,
        ]);
        
        $products['susu'] = Product::create([
            'kode_barang' => 'BB-004',
            'nama_barang' => 'Susu UHT Full Cream',
            'category_id' => $bahanBaku->id,
            'unit_id' => $ltr ? $ltr->id : $kg->id,
            'harga_beli_default' => 18000,
            'harga_jual_default' => 22000,
            'limit_stock' => 20,
        ]);
        
        // WIP
        if ($wip) {
            $products['adonan_roti'] = Product::create([
                'kode_barang' => 'WIP-001',
                'nama_barang' => 'Adonan Roti Tawar',
                'category_id' => $wip->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 25000,
                'harga_jual_default' => 30000,
                'limit_stock' => 20,
            ]);
            
            $products['adonan_donat'] = Product::create([
                'kode_barang' => 'WIP-002',
                'nama_barang' => 'Adonan Donat',
                'category_id' => $wip->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 22000,
                'harga_jual_default' => 28000,
                'limit_stock' => 15,
            ]);
        }
        
        // Barang Jadi
        if ($barangJadi) {
            $products['roti_tawar'] = Product::create([
                'kode_barang' => 'BJ-001',
                'nama_barang' => 'Roti Tawar Premium',
                'category_id' => $barangJadi->id,
                'unit_id' => $pcs ? $pcs->id : $kg->id,
                'harga_beli_default' => 8000,
                'harga_jual_default' => 12000,
                'limit_stock' => 50,
            ]);
            
            $products['roti_coklat'] = Product::create([
                'kode_barang' => 'BJ-002',
                'nama_barang' => 'Roti Coklat Manis',
                'category_id' => $barangJadi->id,
                'unit_id' => $pcs ? $pcs->id : $kg->id,
                'harga_beli_default' => 5000,
                'harga_jual_default' => 8000,
                'limit_stock' => 100,
            ]);
            
            $products['donat'] = Product::create([
                'kode_barang' => 'BJ-003',
                'nama_barang' => 'Donat Gula Halus',
                'category_id' => $barangJadi->id,
                'unit_id' => $pcs ? $pcs->id : $kg->id,
                'harga_beli_default' => 3000,
                'harga_jual_default' => 5000,
                'limit_stock' => 100,
            ]);
        }
        
        $this->command->info('  ✓ ' . count($products) . ' products created');
        
        // ===== INVENTORY BATCHES (FOR FIFO/LIFO/AVG TESTING) =====
        $this->command->info('📊 Creating Inventory Batches for Testing...');
        
        // TEPUNG - 3 batches dengan harga berbeda (TEST FIFO/LIFO/AVG)
        $tepungBatches = [
            ['date' => '2025-01-01', 'qty' => 100, 'price' => 10000, 'batch' => 'BATCH-TP-001'],
            ['date' => '2025-01-15', 'qty' => 100, 'price' => 12000, 'batch' => 'BATCH-TP-002'],
            ['date' => '2025-02-01', 'qty' => 100, 'price' => 15000, 'batch' => 'BATCH-TP-003'],
        ];
        foreach ($tepungBatches as $b) {
            InventoryBatch::create([
                'product_id' => $products['tepung']->id,
                'batch_no' => $b['batch'],
                'source' => InventoryBatch::SOURCE_OPENING_BALANCE,
                'date_in' => Carbon::parse($b['date']),
                'qty_initial' => $b['qty'],
                'qty_current' => $b['qty'],
                'price_per_unit' => $b['price'],
            ]);
        }
        $this->command->info('  ✓ Tepung: 3 batches (100kg×3 = 300kg)');
        
        // GULA - 2 batches
        $gulaBatches = [
            ['date' => '2025-01-10', 'qty' => 50, 'price' => 13000, 'batch' => 'BATCH-GL-001'],
            ['date' => '2025-02-05', 'qty' => 50, 'price' => 15000, 'batch' => 'BATCH-GL-002'],
        ];
        foreach ($gulaBatches as $b) {
            InventoryBatch::create([
                'product_id' => $products['gula']->id,
                'batch_no' => $b['batch'],
                'source' => InventoryBatch::SOURCE_OPENING_BALANCE,
                'date_in' => Carbon::parse($b['date']),
                'qty_initial' => $b['qty'],
                'qty_current' => $b['qty'],
                'price_per_unit' => $b['price'],
            ]);
        }
        $this->command->info('  ✓ Gula: 2 batches (50kg×2 = 100kg)');
        
        // MENTEGA - 1 batch
        InventoryBatch::create([
            'product_id' => $products['mentega']->id,
            'batch_no' => 'BATCH-MT-001',
            'source' => InventoryBatch::SOURCE_OPENING_BALANCE,
            'date_in' => Carbon::parse('2025-01-20'),
            'qty_initial' => 25,
            'qty_current' => 25,
            'price_per_unit' => 45000,
        ]);
        $this->command->info('  ✓ Mentega: 1 batch (25kg)');
        
        // SUSU - 2 batches
        $susuBatches = [
            ['date' => '2025-01-05', 'qty' => 30, 'price' => 17000, 'batch' => 'BATCH-SS-001'],
            ['date' => '2025-02-10', 'qty' => 30, 'price' => 19000, 'batch' => 'BATCH-SS-002'],
        ];
        foreach ($susuBatches as $b) {
            InventoryBatch::create([
                'product_id' => $products['susu']->id,
                'batch_no' => $b['batch'],
                'source' => InventoryBatch::SOURCE_OPENING_BALANCE,
                'date_in' => Carbon::parse($b['date']),
                'qty_initial' => $b['qty'],
                'qty_current' => $b['qty'],
                'price_per_unit' => $b['price'],
            ]);
        }
        $this->command->info('  ✓ Susu: 2 batches (30L×2 = 60L)');
        
        // ===== SUMMARY =====
        $this->command->newLine();
        $this->command->info('=' . str_repeat('=', 60));
        $this->command->info('📊 FIFO/LIFO/AVG Test Scenario - Tepung Terigu Premium');
        $this->command->info('=' . str_repeat('=', 60));
        $this->command->table(
            ['Batch', 'Tanggal', 'Qty', 'Harga/Unit', 'Total Nilai'],
            [
                ['BATCH-TP-001', '01-Jan-2025', '100 Kg', 'Rp 10,000', 'Rp 1,000,000'],
                ['BATCH-TP-002', '15-Jan-2025', '100 Kg', 'Rp 12,000', 'Rp 1,200,000'],
                ['BATCH-TP-003', '01-Feb-2025', '100 Kg', 'Rp 15,000', 'Rp 1,500,000'],
                ['TOTAL', '', '300 Kg', 'Avg: Rp 12,333', 'Rp 3,700,000'],
            ]
        );
        
        $this->command->newLine();
        $this->command->info('🧮 Expected COGS untuk konsumsi 150 Kg:');
        $this->command->table(
            ['Method', 'Calculation', 'COGS', 'Sisa Stok'],
            [
                ['FIFO', '(100×10K) + (50×12K)', 'Rp 1,600,000', '150 Kg @ Rp 14,000 avg'],
                ['LIFO', '(100×15K) + (50×12K)', 'Rp 2,100,000', '150 Kg @ Rp 10,667 avg'],
                ['AVERAGE', '150 × 12,333', 'Rp 1,850,000', '150 Kg @ Rp 12,333'],
            ]
        );
        
        $this->command->newLine();
        $this->command->info('✅ Test data ready! Gunakan menu:');
        $this->command->info('   - Input Barang Keluar → Pemakaian Bahan Baku');
        $this->command->info('   - Toggle FIFO/LIFO/AVERAGE di header');
        $this->command->info('   - Consume 150 Kg Tepung dan verifikasi HPP!');
    }
}
