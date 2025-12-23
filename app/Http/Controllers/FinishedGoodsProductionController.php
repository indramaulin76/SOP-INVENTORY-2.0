<?php

namespace App\Http\Controllers;

use App\Models\FinishedGoodsProduction;
use App\Models\FinishedGoodsProductionMaterial;
use App\Models\FinishedGoodsProductionWip;
use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Setting;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FinishedGoodsProductionController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Daftar semua produksi barang jadi
     */
    public function index()
    {
        $productions = FinishedGoodsProduction::with(['product', 'materials.product'])
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return Inertia::render('FinishedGoodsProduction/Index', [
            'productions' => $productions,
        ]);
    }

    /**
     * Form input produksi barang jadi baru
     * 
     * Menyiapkan data dropdown untuk produk output (Barang Jadi)
     * dan input materials (Bahan Baku) dan WIP (Barang Dalam Proses).
     */
    public function create()
    {
        // Get Barang Jadi category for output product
        $barangJadiCategory = Category::where('nama_kategori', Category::BARANG_JADI)->first();
        
        // Get Bahan Baku category for input materials
        $bahanBakuCategory = Category::where('nama_kategori', Category::BAHAN_BAKU)->first();
        
        // Get WIP category for WIP input
        $wipCategory = Category::where('nama_kategori', Category::BARANG_DALAM_PROSES)->first();
        
        // Finished goods products (output)
        $finishedProducts = Product::with(['category', 'unit'])
            ->where('category_id', $barangJadiCategory?->id)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit?->nama_satuan ?? '-',
                ];
            })
            ->values()
            ->toArray();

        // Raw materials only (Bahan Baku)
        $rawMaterials = Product::with(['category', 'unit', 'inventoryBatches'])
            ->where('category_id', $bahanBakuCategory?->id)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($product) {
                $currentStock = $product->inventoryBatches->sum('qty_current');
                $avgPrice = $this->inventoryService->getAveragePrice($product->id);
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit?->nama_satuan ?? '-',
                    'current_stock' => $currentStock,
                    'avg_price' => $avgPrice,
                ];
            })
            ->values()
            ->toArray();

        // WIP products (Barang Dalam Proses) - SEPARATE from raw materials
        $wipProducts = Product::with(['category', 'unit', 'inventoryBatches'])
            ->where('category_id', $wipCategory?->id)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($product) {
                $currentStock = $product->inventoryBatches->sum('qty_current');
                $avgPrice = $this->inventoryService->getAveragePrice($product->id);
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit?->nama_satuan ?? '-',
                    'current_stock' => $currentStock,
                    'avg_price' => $avgPrice,
                ];
            })
            ->filter(fn($p) => $p['current_stock'] > 0) // Only show WIP with stock
            ->values()
            ->toArray();

        // Generate next production number
        $nextProductionNo = FinishedGoodsProduction::generateProductionNumber();
        
        // Get current inventory method
        $inventoryMethod = Setting::get('inventory_method', 'FIFO');

        return Inertia::render('InputBarangJadi', [
            'finishedProducts' => $finishedProducts ?: [],
            'rawMaterials' => $rawMaterials ?: [],
            'wipProducts' => $wipProducts ?: [],
            'nextProductionNo' => $nextProductionNo,
            'inventoryMethod' => $inventoryMethod,
        ]);
    }

    /**
     * Simpan data produksi barang jadi
     * 
     * LOGIKA MANUFACTURING (DUAL-SOURCE):
     * 1. Validasi input (produk output, qty, bahan baku, WIP)
     * 2. Loop setiap bahan baku: konsumsi stok pakai InventoryService
     * 3. Loop setiap WIP: konsumsi stok WIP pakai InventoryService
     * 4. Akumulasi total biaya produksi (Raw Materials + WIP)
     * 5. Hitung HPP per unit = Total Biaya / Qty Produksi
     * 6. Buat batch baru untuk barang jadi dengan HPP yang sudah dihitung
     * 
     * Note: Sistem otomatis pakai metode akuntansi aktif (FIFO/LIFO/AVERAGE)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:500',
            'materials' => 'nullable|array',
            'materials.*.product_id' => 'required|exists:products,id',
            'materials.*.quantity' => 'required|numeric|min:0.01',
            'wip_items' => 'nullable|array',
            'wip_items.*.product_id' => 'required|exists:products,id',
            'wip_items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'product_id.required' => 'Produk barang jadi harus dipilih.',
            'quantity_produced.min' => 'Quantity produksi harus lebih dari 0.',
            'materials.*.quantity.min' => 'Quantity bahan baku harus lebih dari 0.',
            'wip_items.*.quantity.min' => 'Quantity WIP harus lebih dari 0.',
        ]);

        // Validation: at least one material or WIP must be provided
        $hasMaterials = !empty($validated['materials']) && count(array_filter($validated['materials'], fn($m) => !empty($m['product_id']))) > 0;
        $hasWipItems = !empty($validated['wip_items']) && count(array_filter($validated['wip_items'], fn($w) => !empty($w['product_id']))) > 0;
        
        if (!$hasMaterials && !$hasWipItems) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimal harus ada 1 bahan baku atau 1 WIP untuk produksi.');
        }

        // Validation: quantity_produced must be > 0
        if ($validated['quantity_produced'] <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jumlah produksi harus lebih dari 0.');
        }

        DB::beginTransaction();
        
        try {
            // Step 1: Get active accounting method
            $inventoryMethod = Setting::get('inventory_method', 'FIFO');
            
            // Step 2: Process raw materials (consume materials and accumulate cost)
            $totalProductionCost = 0;
            $materialRecords = [];
            $wipRecords = [];

            // Process Raw Materials
            if ($hasMaterials) {
                foreach ($validated['materials'] as $materialData) {
                    if (empty($materialData['product_id'])) continue;
                    
                    $productId = $materialData['product_id'];
                    $qtyNeeded = $materialData['quantity'];

                    // Consume inventory using configured method
                    $consumption = $this->inventoryService->consumeInventory($productId, $qtyNeeded);

                    // Accumulate total production cost
                    $totalProductionCost += $consumption['total_cost'];

                    // Store material record for later insertion
                    $materialRecords[] = [
                        'product_id' => $productId,
                        'quantity' => $qtyNeeded,
                        'cost_per_unit' => $consumption['average_cost'],
                        'total_cost' => $consumption['total_cost'],
                    ];
                }
            }

            // Step 3: Process WIP items (consume WIP and accumulate cost)
            if ($hasWipItems) {
                foreach ($validated['wip_items'] as $wipData) {
                    if (empty($wipData['product_id'])) continue;
                    
                    $productId = $wipData['product_id'];
                    $qtyNeeded = $wipData['quantity'];

                    // Consume WIP inventory using configured method
                    $consumption = $this->inventoryService->consumeInventory($productId, $qtyNeeded);

                    // Accumulate total production cost
                    $totalProductionCost += $consumption['total_cost'];

                    // Store WIP record for later insertion
                    $wipRecords[] = [
                        'product_id' => $productId,
                        'quantity' => $qtyNeeded,
                        'cost_per_unit' => $consumption['average_cost'],
                        'total_cost' => $consumption['total_cost'],
                    ];
                }
            }

            // Step 4: Calculate HPP (Cost of Goods Manufactured)
            $hppPerUnit = $totalProductionCost / $validated['quantity_produced'];

            // Step 5: Create production header
            $productionNumber = FinishedGoodsProduction::generateProductionNumber();

            $production = FinishedGoodsProduction::create([
                'tanggal' => $validated['tanggal'],
                'nomor_produksi' => $productionNumber,
                'product_id' => $validated['product_id'],
                'quantity_produced' => $validated['quantity_produced'],
                'keterangan' => $validated['keterangan'] ?? "Produksi dengan metode {$inventoryMethod}",
                'total_cost' => $totalProductionCost,
            ]);

            // Step 6: Create material usage records
            foreach ($materialRecords as $material) {
                FinishedGoodsProductionMaterial::create([
                    'finished_goods_production_id' => $production->id,
                    'product_id' => $material['product_id'],
                    'quantity' => $material['quantity'],
                    'cost_per_unit' => $material['cost_per_unit'],
                    'total_cost' => $material['total_cost'],
                ]);
            }

            // Step 7: Create WIP usage records
            foreach ($wipRecords as $wip) {
                FinishedGoodsProductionWip::create([
                    'finished_goods_production_id' => $production->id,
                    'product_id' => $wip['product_id'],
                    'quantity' => $wip['quantity'],
                    'cost_per_unit' => $wip['cost_per_unit'],
                    'total_cost' => $wip['total_cost'],
                ]);
            }

            // Step 8: Create inventory batch for finished goods with calculated HPP
            InventoryBatch::create([
                'product_id' => $validated['product_id'],
                'batch_no' => $productionNumber,
                'source' => InventoryBatch::SOURCE_PRODUCTION,
                'date_in' => $validated['tanggal'],
                'qty_initial' => $validated['quantity_produced'],
                'qty_current' => $validated['quantity_produced'],
                'price_per_unit' => $hppPerUnit, // CRITICAL: Use calculated HPP!
            ]);

            DB::commit();

            $finishedProduct = Product::find($validated['product_id']);
            
            return redirect()
                ->route('barang-masuk.barang-jadi')
                ->with('success', sprintf(
                    "Produksi berhasil! [%s] %s | Qty: %s | HPP/unit: Rp %s | Total Cost: Rp %s | Method: %s",
                    $productionNumber,
                    $finishedProduct->nama_barang ?? '',
                    number_format($validated['quantity_produced'], 2),
                    number_format($hppPerUnit, 0, ',', '.'),
                    number_format($totalProductionCost, 0, ',', '.'),
                    $inventoryMethod
                ));

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan produksi: ' . $e->getMessage());
        }
    }

    /**
     * Detail produksi barang jadi
     */
    public function show(FinishedGoodsProduction $finishedGoodsProduction)
    {
        $finishedGoodsProduction->load('product', 'materials.product.unit');

        return Inertia::render('FinishedGoodsProduction/Show', [
            'production' => $finishedGoodsProduction,
        ]);
    }

    /**
     * Hapus produksi barang jadi
     * 
     * Catatan: Hapus batch barang jadi + record bahan baku yang terpakai.
     * Stok bahan baku TIDAK dikembalikan otomatis (perlu review bisnis logic).
     */
    public function destroy(FinishedGoodsProduction $finishedGoodsProduction)
    {
        DB::beginTransaction();
        
        try {
            // Delete inventory batch for finished goods
            InventoryBatch::where('batch_no', $finishedGoodsProduction->nomor_produksi)
                ->where('source', InventoryBatch::SOURCE_PRODUCTION)
                ->delete();

            // Delete materials
            $finishedGoodsProduction->materials()->delete();

            // Delete production header
            $finishedGoodsProduction->delete();

            DB::commit();

            return redirect()
                ->route('barang-masuk.barang-jadi')
                ->with('success', 'Produksi berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus produksi: ' . $e->getMessage());
        }
    }
}
