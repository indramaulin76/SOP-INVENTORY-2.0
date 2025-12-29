<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\PurchaseRawMaterial;
use App\Models\PurchaseRawMaterialItem;
use App\Models\FinishedGoodsProduction;
use App\Models\FinishedGoodsProductionMaterial;
use App\Models\SalesFinishedGoods;
use App\Models\SalesFinishedGoodsItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Simulation Seeder - 2 Weeks of Realistic Bakery Operations
 * 
 * This seeder creates a complete simulation of Sae Bakery running for 14 days.
 * It populates the dashboard with charts, logs, and financial reports.
 * 
 * Story: Day -14 to Day 0 (Today)
 * - Week 1: Building stock, low sales (5-10/day)
 * - Week 2: High traffic, restocking (20-40 sales/day)
 */
class SimulationSeeder extends Seeder
{
    protected InventoryService $inventoryService;
    protected array $products = [];
    protected array $suppliers = [];
    protected array $customers = [];
    protected int $purchaseCounter = 0;
    protected int $productionCounter = 0;
    protected int $salesCounter = 0;

    public function __construct()
    {
        $this->inventoryService = app(InventoryService::class);
    }

    public function run(): void
    {
        $this->command->info('🍞 SAE BAKERY SIMULATION SEEDER');
        $this->command->info('================================');
        $this->command->info('Simulating 14 days of bakery operations...');
        $this->command->newLine();

        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $this->truncateTables();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Step 1: Create Users
        $this->createUsers();

        // Step 2: Create Master Data
        $this->createMasterData();

        // Step 3: Simulate 14 Days
        $this->simulate14Days();

        // Summary
        $this->showSummary();
    }

    protected function truncateTables(): void
    {
        $this->command->info('🗑️  Truncating tables...');
        
        $tables = [
            'stock_opname_items',
            'stock_opnames',
            'sales_finished_goods_items',
            'sales_finished_goods',
            'finished_goods_production_materials',
            'finished_goods_productions',
            'purchase_raw_material_items',
            'purchase_raw_materials',
            'usage_raw_material_items',
            'usage_raw_materials',
            'wip_entry_items',
            'wip_entries',
            'usage_wip_items',
            'usage_wip',
            'inventory_batches',
            'products',
            'customers',
            'suppliers',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
                // Table might not exist
            }
        }
        
        $this->command->info('  ✓ Tables truncated');
    }

    protected function createUsers(): void
    {
        $this->command->info('👥 Creating users...');
        
        $users = [
            ['name' => 'Owner Sae Bakery', 'email' => 'pimpinan@saebakery.com', 'role' => 'Pimpinan'],
            ['name' => 'Admin Toko', 'email' => 'admin@saebakery.com', 'role' => 'Admin'],
            ['name' => 'Karyawan Kasir', 'email' => 'karyawan@saebakery.com', 'role' => 'Karyawan'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                ]
            );
        }

        $this->command->info('  ✓ 3 users created (pimpinan, admin, karyawan)');
    }

    protected function createMasterData(): void
    {
        $this->command->info('📦 Creating master data...');

        // Get or create categories
        $bahanBaku = Category::firstOrCreate(['kode_kategori' => 'BB'], ['nama_kategori' => 'Bahan Baku']);
        $barangJadi = Category::firstOrCreate(['kode_kategori' => 'BJ'], ['nama_kategori' => 'Barang Jadi']);
        
        // Get or create units
        $kg = Unit::firstOrCreate(['singkatan' => 'Kg'], ['nama_satuan' => 'Kilogram', 'singkatan' => 'Kg']);
        $pcs = Unit::firstOrCreate(['singkatan' => 'Pcs'], ['nama_satuan' => 'Pieces', 'singkatan' => 'Pcs']);
        $ltr = Unit::firstOrCreate(['singkatan' => 'Ltr'], ['nama_satuan' => 'Liter', 'singkatan' => 'Ltr']);
        $butir = Unit::firstOrCreate(['singkatan' => 'Btr'], ['nama_satuan' => 'Butir', 'singkatan' => 'Btr']);

        // === SUPPLIERS ===
        $this->suppliers = [
            'bahan_kue' => Supplier::create([
                'kode_supplier' => 'SUP-001',
                'nama_supplier' => 'Toko Bahan Kue Jaya',
                'alamat' => 'Jl. Pasar Anyar No. 15',
                'telepon' => '021-5551234',
            ]),
            'telur' => Supplier::create([
                'kode_supplier' => 'SUP-002',
                'nama_supplier' => 'Peternakan Telur Sejahtera',
                'alamat' => 'Jl. Pertanian Km. 5',
                'telepon' => '021-5559999',
            ]),
        ];

        // === CUSTOMERS ===
        $this->customers = [
            'walkin' => Customer::create([
                'kode_customer' => 'CUST-001',
                'nama_customer' => 'Walk-in Customer',
                'alamat' => '-',
                'telepon' => '-',
            ]),
            'gofood' => Customer::create([
                'kode_customer' => 'CUST-002',
                'nama_customer' => 'GoFood',
                'alamat' => 'Platform Online',
                'telepon' => '-',
            ]),
            'grabfood' => Customer::create([
                'kode_customer' => 'CUST-003',
                'nama_customer' => 'GrabFood',
                'alamat' => 'Platform Online',
                'telepon' => '-',
            ]),
            'corporate' => Customer::create([
                'kode_customer' => 'CUST-004',
                'nama_customer' => 'Corporate Order',
                'alamat' => 'Various',
                'telepon' => '-',
            ]),
        ];

        // === INGREDIENTS (Bahan Baku) ===
        $this->products = [
            'tepung_cakra' => Product::create([
                'kode_barang' => 'BB-001',
                'nama_barang' => 'Tepung Cakra (High Protein)',
                'category_id' => $bahanBaku->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 11000,
                'harga_jual_default' => 13000,
                'limit_stock' => 20,
            ]),
            'tepung_segitiga' => Product::create([
                'kode_barang' => 'BB-002',
                'nama_barang' => 'Tepung Segitiga Biru',
                'category_id' => $bahanBaku->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 9500,
                'harga_jual_default' => 11000,
                'limit_stock' => 15,
            ]),
            'gula' => Product::create([
                'kode_barang' => 'BB-003',
                'nama_barang' => 'Gula Pasir',
                'category_id' => $bahanBaku->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 14500,
                'harga_jual_default' => 16000,
                'limit_stock' => 10,
            ]),
            'telur' => Product::create([
                'kode_barang' => 'BB-004',
                'nama_barang' => 'Telur Ayam',
                'category_id' => $bahanBaku->id,
                'unit_id' => $butir->id,
                'harga_beli_default' => 2200,
                'harga_jual_default' => 2500,
                'limit_stock' => 60,
            ]),
            'mentega' => Product::create([
                'kode_barang' => 'BB-005',
                'nama_barang' => 'Mentega Wijsman',
                'category_id' => $bahanBaku->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 85000,
                'harga_jual_default' => 95000,
                'limit_stock' => 5,
            ]),
            'ragi' => Product::create([
                'kode_barang' => 'BB-006',
                'nama_barang' => 'Ragi Instan',
                'category_id' => $bahanBaku->id,
                'unit_id' => $kg->id,
                'harga_beli_default' => 45000,
                'harga_jual_default' => 50000,
                'limit_stock' => 2,
            ]),
            'susu' => Product::create([
                'kode_barang' => 'BB-007',
                'nama_barang' => 'Susu UHT Full Cream',
                'category_id' => $bahanBaku->id,
                'unit_id' => $ltr->id,
                'harga_beli_default' => 18000,
                'harga_jual_default' => 20000,
                'limit_stock' => 10,
            ]),
        ];

        // === FINISHED GOODS (Barang Jadi) ===
        $this->products['roti_manis'] = Product::create([
            'kode_barang' => 'BJ-001',
            'nama_barang' => 'Roti Manis',
            'category_id' => $barangJadi->id,
            'unit_id' => $pcs->id,
            'harga_beli_default' => 3500,
            'harga_jual_default' => 6000,
            'limit_stock' => 50,
        ]);

        $this->products['roti_tawar'] = Product::create([
            'kode_barang' => 'BJ-002',
            'nama_barang' => 'Roti Tawar Premium',
            'category_id' => $barangJadi->id,
            'unit_id' => $pcs->id,
            'harga_beli_default' => 8000,
            'harga_jual_default' => 15000,
            'limit_stock' => 30,
        ]);

        $this->products['donat'] = Product::create([
            'kode_barang' => 'BJ-003',
            'nama_barang' => 'Donat Kentang',
            'category_id' => $barangJadi->id,
            'unit_id' => $pcs->id,
            'harga_beli_default' => 2500,
            'harga_jual_default' => 5000,
            'limit_stock' => 60,
        ]);

        $this->products['bomboloni'] = Product::create([
            'kode_barang' => 'BJ-004',
            'nama_barang' => 'Bomboloni Coklat',
            'category_id' => $barangJadi->id,
            'unit_id' => $pcs->id,
            'harga_beli_default' => 4000,
            'harga_jual_default' => 8000,
            'limit_stock' => 40,
        ]);

        $this->command->info('  ✓ 2 suppliers, 4 customers, 11 products created');
    }

    protected function simulate14Days(): void
    {
        $this->command->info('📅 Simulating 14 days of operations...');
        $this->command->newLine();

        $today = Carbon::today();

        for ($day = -14; $day <= 0; $day++) {
            $date = $today->copy()->addDays($day);
            $isWeek1 = $day <= -7;
            $dayNum = abs($day);

            $this->command->info("📆 Day {$day} ({$date->format('d M Y')}):");

            // === PURCHASES ===
            if ($day == -14) {
                // Initial bulk purchase - Tepung @ Rp 10,000 (for FIFO testing)
                $this->createPurchase($date, 'tepung_cakra', 50, 10000, 'bahan_kue', 'Initial Stock - Low Price');
                $this->createPurchase($date, 'tepung_segitiga', 30, 9000, 'bahan_kue', 'Initial Stock');
                $this->createPurchase($date, 'gula', 25, 14000, 'bahan_kue', 'Initial Stock');
                $this->createPurchase($date, 'mentega', 10, 80000, 'bahan_kue', 'Initial Stock');
                $this->createPurchase($date, 'ragi', 5, 42000, 'bahan_kue', 'Initial Stock');
                $this->createPurchase($date, 'telur', 120, 2100, 'telur', 'Initial Stock');
                $this->createPurchase($date, 'susu', 20, 17500, 'bahan_kue', 'Initial Stock');
            } elseif ($day == -7) {
                // Restock Tepung @ Rp 12,000 (for FIFO testing - higher price)
                $this->createPurchase($date, 'tepung_cakra', 30, 12000, 'bahan_kue', 'Restock - Price Increased');
                $this->createPurchase($date, 'telur', 90, 2200, 'telur', 'Weekly Restock');
                $this->createPurchase($date, 'susu', 15, 18000, 'bahan_kue', 'Weekly Restock');
            } elseif ($day == -3) {
                // Week 2 restocking with price fluctuation
                $this->createPurchase($date, 'gula', 15, 15000, 'bahan_kue', 'Urgent Restock - Price Up');
                $this->createPurchase($date, 'telur', 60, 2300, 'telur', 'Restock - Price Fluctuation');
            }

            // === PRODUCTION ===
            $productionQty = $isWeek1 ? rand(15, 25) : rand(40, 80);
            $this->createProduction($date, 'roti_manis', (int)($productionQty * 0.4));
            $this->createProduction($date, 'roti_tawar', (int)($productionQty * 0.2));
            $this->createProduction($date, 'donat', (int)($productionQty * 0.25));
            $this->createProduction($date, 'bomboloni', (int)($productionQty * 0.15));

            // === SALES ===
            $salesCount = $isWeek1 ? rand(5, 10) : rand(20, 40);
            for ($s = 0; $s < $salesCount; $s++) {
                $this->createSale($date);
            }

            // === STOCK OPNAME (Day -3) ===
            if ($day == -3) {
                $this->createStockOpname($date);
            }

            $this->command->info("   ├─ Production: ~{$productionQty} pcs | Sales: {$salesCount} transactions");
        }
    }

    protected function createPurchase(Carbon $date, string $productKey, float $qty, float $price, string $supplierKey, string $note): void
    {
        $this->purchaseCounter++;
        $product = $this->products[$productKey];
        $supplier = $this->suppliers[$supplierKey];

        // Create purchase record
        $purchase = PurchaseRawMaterial::create([
            'nomor_faktur' => 'PUR-' . str_pad($this->purchaseCounter, 4, '0', STR_PAD_LEFT),
            'tanggal' => $date,
            'supplier_id' => $supplier->id,
            'total_nilai' => $qty * $price,
            'keterangan' => $note,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        // Create purchase item - InventoryBatch is created automatically by model's booted() method
        PurchaseRawMaterialItem::create([
            'purchase_raw_material_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'harga_beli' => $price,
            'jumlah' => $qty * $price,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    protected function createProduction(Carbon $date, string $productKey, int $qty): void
    {
        if ($qty <= 0) return;

        $this->productionCounter++;
        $product = $this->products[$productKey];

        // Calculate cost based on recipes (simplified)
        $costPerUnit = $this->calculateProductionCost($productKey);
        $totalCost = $costPerUnit * $qty;

        $production = FinishedGoodsProduction::create([
            'nomor_produksi' => 'PROD-' . str_pad($this->productionCounter, 4, '0', STR_PAD_LEFT),
            'tanggal' => $date,
            'product_id' => $product->id,
            'quantity_produced' => $qty,
            'total_cost' => $totalCost,
            'keterangan' => 'Daily Production',
        ]);

        // Create finished goods batch
        InventoryBatch::create([
            'product_id' => $product->id,
            'batch_no' => 'BATCH-' . $production->nomor_produksi,
            'source' => InventoryBatch::SOURCE_PRODUCTION,
            'date_in' => $date,
            'qty_initial' => $qty,
            'qty_current' => $qty,
            'price_per_unit' => $costPerUnit,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    protected function calculateProductionCost(string $productKey): float
    {
        // Simplified cost calculation based on product type
        return match($productKey) {
            'roti_manis' => 3500,
            'roti_tawar' => 8000,
            'donat' => 2500,
            'bomboloni' => 4000,
            default => 3000,
        };
    }

    protected function createSale(Carbon $date): void
    {
        $this->salesCounter++;
        
        // Random customer
        $customerKeys = array_keys($this->customers);
        $customerKey = $customerKeys[array_rand($customerKeys)];
        $customer = $this->customers[$customerKey];

        // Random product selection (1-3 items per sale)
        $finishedGoods = ['roti_manis', 'roti_tawar', 'donat', 'bomboloni'];
        $itemCount = rand(1, 3);
        $selectedProducts = array_rand(array_flip($finishedGoods), $itemCount);
        if (!is_array($selectedProducts)) {
            $selectedProducts = [$selectedProducts];
        }

        $sale = SalesFinishedGoods::create([
            'nomor_bukti' => 'INV-' . str_pad($this->salesCounter, 5, '0', STR_PAD_LEFT),
            'tanggal' => $date,
            'customer_id' => $customer->id,
            'total_nilai' => 0,
            'keterangan' => 'Sales Transaction',
        ]);

        $totalPenjualan = 0;
        $totalCogsAll = 0;

        foreach ($selectedProducts as $productKey) {
            $product = $this->products[$productKey];
            $qty = rand(1, 5);
            $hargaJual = $product->harga_jual_default;
            
            // Check available stock
            $availableStock = $this->inventoryService->getCurrentStock($product->id);
            if ($availableStock < $qty) {
                $qty = (int) $availableStock;
            }
            
            if ($qty <= 0) continue;

            // Consume inventory using FIFO
            try {
                $costData = $this->inventoryService->consumeInventory($product->id, $qty);
                $totalCogs = $costData['total_cost'] ?? 0;
                $avgCost = $qty > 0 ? $totalCogs / $qty : 0;
            } catch (\Exception $e) {
                $totalCogs = 0;
                $avgCost = $product->harga_beli_default;
            }

            $subtotal = $qty * $hargaJual;
            $profit = $subtotal - $totalCogs;

            SalesFinishedGoodsItem::create([
                'sales_finished_goods_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'harga_jual' => $hargaJual,
                'jumlah' => $subtotal,
                'harga_pokok' => $avgCost,
                'total_cogs' => $totalCogs,
                'profit' => $profit,
            ]);

            $totalPenjualan += $subtotal;
            $totalCogsAll += $totalCogs;
        }

        $sale->update([
            'total_nilai' => $totalPenjualan,
            'total_cogs' => $totalCogsAll,
            'total_profit' => $totalPenjualan - $totalCogsAll,
        ]);
    }

    protected function createStockOpname(Carbon $date): void
    {
        $this->command->info("   └─ 📋 Stock Opname: Found discrepancies!");

        $opname = StockOpname::create([
            'nomor_opname' => 'OPN-' . $date->format('Ymd'),
            'tanggal' => $date,
            'keterangan' => 'Monthly Stock Opname - Found discrepancies',
            'status' => 'finalized',
            'created_by' => User::where('email', 'admin@saebakery.com')->first()?->id ?? 1,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        // Simulated discrepancies
        // Loss: 2kg Gula (system shows more than physical)
        $gula = $this->products['gula'];
        $gulaSystemQty = $this->inventoryService->getCurrentStock($gula->id);
        $gulaPhysicalQty = max(0, $gulaSystemQty - 2); // 2kg missing
        $gulaPrice = $this->inventoryService->getAveragePrice($gula->id);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $gula->id,
            'qty_system' => $gulaSystemQty,
            'qty_physical' => $gulaPhysicalQty,
            'price_per_unit' => $gulaPrice,
            'notes' => 'Loss: 2kg tidak ditemukan',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        // Surplus: 1kg Tepung (physical shows more than system)
        $tepung = $this->products['tepung_cakra'];
        $tepungSystemQty = $this->inventoryService->getCurrentStock($tepung->id);
        $tepungPhysicalQty = $tepungSystemQty + 1; // 1kg surplus
        $tepungPrice = $this->inventoryService->getAveragePrice($tepung->id);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $tepung->id,
            'qty_system' => $tepungSystemQty,
            'qty_physical' => $tepungPhysicalQty,
            'price_per_unit' => $tepungPrice,
            'notes' => 'Surplus: 1kg ditemukan lebih',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        // Apply adjustments
        // Loss - consume 2kg
        if ($gulaSystemQty >= 2) {
            try {
                $this->inventoryService->consumeInventory($gula->id, 2);
            } catch (\Exception $e) {
                // Ignore if not enough stock
            }
        }

        // Surplus - add batch
        InventoryBatch::create([
            'product_id' => $tepung->id,
            'batch_no' => 'ADJ-' . $opname->nomor_opname,
            'source' => InventoryBatch::SOURCE_ADJUSTMENT,
            'date_in' => $date,
            'qty_initial' => 1,
            'qty_current' => 1,
            'price_per_unit' => $tepungPrice,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    protected function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('=' . str_repeat('=', 60));
        $this->command->info('📊 SIMULATION COMPLETE - SUMMARY');
        $this->command->info('=' . str_repeat('=', 60));

        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Users', User::count()],
                ['Suppliers', Supplier::count()],
                ['Customers', Customer::count()],
                ['Products', Product::count()],
                ['Purchase Records', PurchaseRawMaterial::count()],
                ['Production Records', FinishedGoodsProduction::count()],
                ['Sales Transactions', SalesFinishedGoods::count()],
                ['Stock Opname', StockOpname::count()],
                ['Inventory Batches', InventoryBatch::count()],
            ]
        );

        // Financial Summary
        $totalRevenue = SalesFinishedGoodsItem::sum('jumlah');
        $totalCogs = SalesFinishedGoodsItem::sum('total_cogs');
        $totalProfit = SalesFinishedGoodsItem::sum('profit');
        $totalPurchases = PurchaseRawMaterial::sum('total_nilai');

        $this->command->newLine();
        $this->command->info('💰 Financial Summary (14 Days):');
        $this->command->table(
            ['Metric', 'Amount'],
            [
                ['Total Purchases', 'Rp ' . number_format($totalPurchases, 0, ',', '.')],
                ['Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.')],
                ['Total COGS', 'Rp ' . number_format($totalCogs, 0, ',', '.')],
                ['Gross Profit', 'Rp ' . number_format($totalProfit, 0, ',', '.')],
                ['Profit Margin', $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) . '%' : '0%'],
            ]
        );

        $this->command->newLine();
        $this->command->info('🎯 FIFO Testing Data:');
        $this->command->info('   Tepung Cakra has 2 batches with different prices:');
        $this->command->info('   - Day -14: 50kg @ Rp 10,000');
        $this->command->info('   - Day -7:  30kg @ Rp 12,000');
        $this->command->info('   Toggle FIFO/LIFO/AVERAGE to test different costing!');

        $this->command->newLine();
        $this->command->info('✅ Simulation complete! Dashboard should now have data.');
        $this->command->info('   Login: pimpinan@saebakery.com / password');
    }
}
