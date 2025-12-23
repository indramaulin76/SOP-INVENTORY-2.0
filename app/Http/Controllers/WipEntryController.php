<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\WipEntry;
use App\Models\WipEntryItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WipEntryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Show the form for creating a new WIP entry.
     */
    public function create()
    {
        // Get all suppliers (optional)
        $suppliers = Supplier::latest()->get()->map(fn($s) => [
            'id' => $s->id,
            'nama_supplier' => $s->nama_supplier,
            'kode_supplier' => 'SUP-' . str_pad($s->id, 3, '0', STR_PAD_LEFT),
        ]);

        // Get WIP products (Barang Dalam Proses category)
        $wipCategory = Category::where('nama_kategori', 'Barang Dalam Proses')->first();
        
        $products = Product::with('unit')
            ->when($wipCategory, function ($query) use ($wipCategory) {
                $query->where('category_id', $wipCategory->id);
            })
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit->nama_satuan ?? 'pcs',
                    'harga_beli_default' => $product->harga_beli_default ?? 0,
                ];
            });

        // Get Raw Materials for Production mode
        $bahanBakuCategory = Category::where('nama_kategori', Category::BAHAN_BAKU)->first();
        
        $rawMaterials = Product::with(['unit', 'inventoryBatches'])
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
            ->filter(fn($p) => $p['current_stock'] > 0)
            ->values()
            ->toArray();

        // Generate invoice number
        $invoiceNumber = WipEntry::generateInvoiceNumber();

        // Get all departments
        $departments = Department::orderBy('nama_departemen')->get(['id', 'nama_departemen', 'kode_departemen']);

        return Inertia::render('InputBarangDalamProses', [
            'suppliers' => $suppliers,
            'products' => $products,
            'rawMaterials' => $rawMaterials ?: [],
            'invoiceNumber' => $invoiceNumber,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created WIP entry.
     * 
     * DUAL-MODE: Beli dari Vendor OR Produksi Sendiri
     * 
     * Mode A: Beli dari Vendor (source_mode = 'purchase')
     *   - Uses supplier_id and manual harga_beli
     *   - Stock created with manual price
     * 
     * Mode B: Produksi Sendiri (source_mode = 'production')
     *   - Uses materials (raw materials) to calculate cost
     *   - Deducts raw material stock
     *   - HPP calculated from raw material costs (FIFO/LIFO/AVG)
     */
    public function store(Request $request)
    {
        $sourceMode = $request->input('source_mode', 'purchase');
        
        // Base validation
        $baseRules = [
            'tanggal' => 'required|date',
            'nomor_faktur' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'keterangan' => 'nullable|string|max:500',
            'source_mode' => 'required|in:purchase,production',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];

        // Mode-specific validation
        if ($sourceMode === 'purchase') {
            $baseRules['supplier_id'] = 'nullable|exists:suppliers,id';
            $baseRules['items.*.harga_beli'] = 'required|numeric|min:0';
        } else {
            // Production mode - requires materials
            $baseRules['materials'] = 'required|array|min:1';
            $baseRules['materials.*.product_id'] = 'required|exists:products,id';
            $baseRules['materials.*.quantity'] = 'required|numeric|min:0.01';
        }

        $validated = $request->validate($baseRules, [
            'items.required' => 'Minimal 1 barang WIP harus dipilih.',
            'items.*.product_id.required' => 'Pilih barang untuk setiap baris.',
            'items.*.quantity.required' => 'Quantity wajib diisi.',
            'items.*.quantity.min' => 'Quantity minimal 0.01.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi untuk mode pembelian.',
            'materials.required' => 'Minimal 1 bahan baku harus dipilih untuk mode produksi.',
            'materials.*.product_id.required' => 'Pilih bahan baku untuk setiap baris.',
            'materials.*.quantity.min' => 'Quantity bahan baku minimal 0.01.',
        ]);

        // Auto-generate unique nomor_faktur if not provided
        $nomorFaktur = $validated['nomor_faktur'] ?? $this->generateNomorFaktur();
        
        // Check if nomor_faktur already exists, generate new one if so
        while (WipEntry::where('nomor_faktur', $nomorFaktur)->exists()) {
            $nomorFaktur = $this->generateNomorFaktur();
        }

        DB::beginTransaction();
        try {
            // Create WIP entry header
            $wipEntry = WipEntry::create([
                'tanggal' => $validated['tanggal'],
                'nomor_faktur' => $nomorFaktur,
                'supplier_id' => $sourceMode === 'purchase' ? ($validated['supplier_id'] ?? null) : null,
                'department_id' => $validated['department_id'] ?? null,
                'keterangan' => $validated['keterangan'] ?? ($sourceMode === 'production' ? 'Produksi Internal' : null),
                'total_nilai' => 0,
            ]);

            $totalNilai = 0;

            if ($sourceMode === 'purchase') {
                // MODE A: Purchase from Vendor
                foreach ($validated['items'] as $item) {
                    $jumlah = $item['quantity'] * $item['harga_beli'];
                    $totalNilai += $jumlah;
                    
                    WipEntryItem::create([
                        'wip_entry_id' => $wipEntry->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'harga_beli' => $item['harga_beli'],
                    ]);

                    // Create inventory batch for WIP
                    InventoryBatch::create([
                        'product_id' => $item['product_id'],
                        'batch_no' => $nomorFaktur . '-' . $item['product_id'],
                        'source' => 'purchase',
                        'date_in' => $validated['tanggal'],
                        'qty_initial' => $item['quantity'],
                        'qty_current' => $item['quantity'],
                        'price_per_unit' => $item['harga_beli'],
                    ]);
                }
            } else {
                // MODE B: Internal Production
                // Step 1: Consume raw materials and calculate total cost
                $totalProductionCost = 0;
                
                foreach ($validated['materials'] as $materialData) {
                    if (empty($materialData['product_id'])) continue;
                    
                    $productId = $materialData['product_id'];
                    $qtyNeeded = $materialData['quantity'];

                    // Check stock availability
                    $currentStock = $this->inventoryService->getCurrentStock($productId);
                    if ($currentStock < $qtyNeeded) {
                        $product = Product::find($productId);
                        throw new \Exception("Stok {$product->nama_barang} tidak mencukupi. Tersedia: {$currentStock}, Dibutuhkan: {$qtyNeeded}");
                    }

                    // Consume inventory using configured method (FIFO/LIFO/AVERAGE)
                    $consumption = $this->inventoryService->consumeInventory($productId, $qtyNeeded);
                    $totalProductionCost += $consumption['total_cost'];
                }

                // Step 2: Calculate HPP per unit for each WIP item
                $totalWipQty = array_sum(array_column($validated['items'], 'quantity'));
                $hppPerUnit = $totalWipQty > 0 ? $totalProductionCost / $totalWipQty : 0;

                // Step 3: Create WIP items with calculated HPP
                foreach ($validated['items'] as $item) {
                    $jumlah = $item['quantity'] * $hppPerUnit;
                    $totalNilai += $jumlah;
                    
                    WipEntryItem::create([
                        'wip_entry_id' => $wipEntry->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'harga_beli' => $hppPerUnit, // Auto-calculated HPP
                    ]);

                    // Create inventory batch for WIP with calculated HPP
                    InventoryBatch::create([
                        'product_id' => $item['product_id'],
                        'batch_no' => $nomorFaktur . '-' . $item['product_id'],
                        'source' => 'production_result',
                        'date_in' => $validated['tanggal'],
                        'qty_initial' => $item['quantity'],
                        'qty_current' => $item['quantity'],
                        'price_per_unit' => $hppPerUnit,
                    ]);
                }
            }

            // Update total nilai
            $wipEntry->update(['total_nilai' => $totalNilai]);

            DB::commit();

            $modeLabel = $sourceMode === 'purchase' ? 'Pembelian' : 'Produksi';
            return redirect()->route('barang-masuk.barang-dalam-proses')
                ->with('success', "Barang Dalam Proses ({$modeLabel}) berhasil disimpan!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            // User-friendly error messages
            $errorMsg = $e->getMessage();
            
            if (str_contains($errorMsg, 'Stok') && str_contains($errorMsg, 'tidak mencukupi')) {
                $friendlyError = $errorMsg;
            } elseif (str_contains($errorMsg, 'Duplicate entry')) {
                $friendlyError = 'Nomor faktur sudah pernah digunakan. Silakan gunakan nomor faktur lain atau kosongkan untuk auto-generate.';
            } elseif (str_contains($errorMsg, 'foreign key')) {
                $friendlyError = 'Data produk atau supplier tidak valid. Silakan refresh halaman dan coba lagi.';
            } else {
                $friendlyError = 'Gagal menyimpan: ' . $errorMsg;
            }
            
            return redirect()->back()
                ->withErrors(['error' => $friendlyError])
                ->withInput();
        }
    }

    /**
     * Generate unique nomor faktur.
     */
    private function generateNomorFaktur(): string
    {
        $date = now()->format('Ymd');
        $count = WipEntry::whereDate('tanggal', now()->toDateString())->count() + 1;
        return 'WIP-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Display a listing of WIP entries.
     */
    public function index()
    {
        $entries = WipEntry::with(['supplier', 'items.product.unit'])
            ->latest('tanggal')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return Inertia::render('RiwayatWipEntry', [
            'entries' => $entries,
        ]);
    }

    /**
     * Remove the specified WIP entry.
     */
    public function destroy(WipEntry $wipEntry)
    {
        // Note: Deleting WIP entry will not remove inventory batches
        // Consider adding inventory batch cleanup if needed
        $wipEntry->items()->delete();
        $wipEntry->delete();

        return redirect()->back()->with('success', 'Data WIP berhasil dihapus!');
    }
}
