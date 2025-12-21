<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Show form for creating stock opname.
     */
    public function create()
    {
        // Get all products with current stock
        $products = Product::with(['category', 'unit'])
            ->get()
            ->map(function ($product) {
                $currentStock = $this->inventoryService->getCurrentStock($product->id);
                $avgPrice = $this->inventoryService->getAveragePrice($product->id);
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'kategori' => $product->category->nama_kategori ?? '-',
                    'satuan' => $product->unit->singkatan ?? '-',
                    'qty_system' => $currentStock,
                    'avg_price' => $avgPrice,
                ];
            });

        $opnameNumber = StockOpname::generateOpnameNumber();

        return Inertia::render('HasilStockOpname', [
            'products' => $products,
            'opnameNumber' => $opnameNumber,
        ]);
    }

    /**
     * Store stock opname.
     * - Karyawan: saves as DRAFT (no inventory adjustment)
     * - Admin/Pimpinan: saves as FINALIZED (with inventory adjustment)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_system' => 'required|numeric|min:0',
            'items.*.qty_physical' => 'required|numeric|min:0',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Minimal 1 produk harus dicek.',
            'items.*.qty_physical.required' => 'Quantity fisik wajib diisi.',
        ]);

        $user = Auth::user();
        $isKaryawan = $user->isKaryawan();
        
        // Karyawan: draft, Admin/Pimpinan: finalized
        $status = $isKaryawan ? 'draft' : 'finalized';

        DB::beginTransaction();

        try {
            // Create stock opname header
            $opname = StockOpname::create([
                'tanggal' => $validated['tanggal'],
                'nomor_opname' => StockOpname::generateOpnameNumber(),
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => $status,
                'created_by' => Auth::id(),
            ]);

            // Process each item
            foreach ($validated['items'] as $itemData) {
                $qtySystem = (float) $itemData['qty_system'];
                $qtyPhysical = (float) $itemData['qty_physical'];
                $pricePerUnit = (float) $itemData['price_per_unit'];

                // Create opname item record
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $itemData['product_id'],
                    'qty_system' => $qtySystem,
                    'qty_physical' => $qtyPhysical,
                    'price_per_unit' => $pricePerUnit,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Only perform adjustments if finalized (not draft)
            if ($status === 'finalized') {
                $this->performAdjustments($opname, $validated['items'], $validated['tanggal']);
            }

            DB::commit();

            $message = $isKaryawan 
                ? 'Stock Opname disimpan sebagai DRAFT. Menunggu persetujuan Admin/Pimpinan. No. ' . $opname->nomor_opname
                : 'Stock Opname berhasil Finalize dan stok telah disesuaikan! No. ' . $opname->nomor_opname;

            return redirect()
                ->route('laporan.stock-opname')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Stock Opname: ' . $e->getMessage());
        }
    }

    /**
     * Finalize a draft stock opname (Admin/Pimpinan only).
     */
    public function finalize(StockOpname $stockOpname)
    {
        // Only Admin/Pimpinan can finalize
        if (Auth::user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk finalize stock opname.');
        }

        // Already finalized
        if ($stockOpname->status === 'finalized') {
            return redirect()->back()->with('error', 'Stock Opname ini sudah di-finalize sebelumnya.');
        }

        DB::beginTransaction();

        try {
            // Get items for adjustment
            $items = $stockOpname->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'qty_system' => $item->qty_system,
                    'qty_physical' => $item->qty_physical,
                    'price_per_unit' => $item->price_per_unit,
                ];
            })->toArray();

            // Perform inventory adjustments
            $this->performAdjustments($stockOpname, $items, $stockOpname->tanggal->format('Y-m-d'));

            // Update status to finalized
            $stockOpname->update(['status' => 'finalized']);

            DB::commit();

            return redirect()
                ->route('laporan.stock-opname')
                ->with('success', 'Stock Opname berhasil di-finalize dan stok telah disesuaikan! No. ' . $stockOpname->nomor_opname);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal finalize Stock Opname: ' . $e->getMessage());
        }
    }

    /**
     * Edit draft stock opname (Admin/Pimpinan only).
     */
    public function edit(StockOpname $stockOpname)
    {
        // Only Admin/Pimpinan can edit drafts
        if (Auth::user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit stock opname.');
        }

        // Can only edit drafts
        if ($stockOpname->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya draft yang bisa diedit.');
        }

        $stockOpname->load(['items.product.unit', 'items.product.category']);

        // Get all products with current stock
        $products = Product::with(['category', 'unit'])
            ->get()
            ->map(function ($product) {
                $currentStock = $this->inventoryService->getCurrentStock($product->id);
                $avgPrice = $this->inventoryService->getAveragePrice($product->id);
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'kategori' => $product->category->nama_kategori ?? '-',
                    'satuan' => $product->unit->singkatan ?? '-',
                    'qty_system' => $currentStock,
                    'avg_price' => $avgPrice,
                ];
            });

        return Inertia::render('HasilStockOpname', [
            'products' => $products,
            'opnameNumber' => $stockOpname->nomor_opname,
            'editMode' => true,
            'existingOpname' => $stockOpname,
        ]);
    }

    /**
     * Perform inventory adjustments based on opname items.
     */
    private function performAdjustments(StockOpname $opname, array $items, string $date): void
    {
        foreach ($items as $itemData) {
            $qtySystem = (float) $itemData['qty_system'];
            $qtyPhysical = (float) $itemData['qty_physical'];
            $difference = $qtyPhysical - $qtySystem;
            $pricePerUnit = (float) $itemData['price_per_unit'];

            // Skip if no difference
            if ($difference == 0) {
                continue;
            }

            if ($difference > 0) {
                // === SURPLUS: Create new inventory batch ===
                InventoryBatch::create([
                    'product_id' => $itemData['product_id'],
                    'batch_no' => 'ADJ-' . $opname->nomor_opname,
                    'source' => InventoryBatch::SOURCE_ADJUSTMENT,
                    'date_in' => $date,
                    'qty_initial' => $difference,
                    'qty_current' => $difference,
                    'price_per_unit' => $pricePerUnit,
                ]);
            } else {
                // === LOSS: Consume from existing batches using FIFO ===
                $lossQty = abs($difference);
                
                $availableStock = $this->inventoryService->getCurrentStock($itemData['product_id']);
                
                if ($availableStock >= $lossQty) {
                    $this->inventoryService->consumeInventory(
                        $itemData['product_id'],
                        $lossQty
                    );
                } else {
                    // Zero out all existing batches
                    $currentBatches = InventoryBatch::where('product_id', $itemData['product_id'])
                        ->where('qty_current', '>', 0)
                        ->get();
                    
                    foreach ($currentBatches as $batch) {
                        $batch->qty_current = 0;
                        $batch->save();
                    }
                }
            }
        }
    }

    /**
     * Display stock opname history.
     */
    public function index()
    {
        $opnames = StockOpname::with(['creator', 'items.product'])
            ->latest('tanggal')
            ->paginate(20);

        return Inertia::render('RiwayatStockOpname', [
            'opnames' => $opnames,
        ]);
    }

    /**
     * Show details of a specific stock opname.
     */
    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['creator', 'items.product.unit', 'items.product.category']);

        return Inertia::render('DetailStockOpname', [
            'opname' => $stockOpname,
        ]);
    }
}

