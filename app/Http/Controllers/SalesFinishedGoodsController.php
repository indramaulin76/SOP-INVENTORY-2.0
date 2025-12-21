<?php

namespace App\Http\Controllers;

use App\Models\SalesFinishedGoods;
use App\Models\SalesFinishedGoodsItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SalesFinishedGoodsController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display listing of sales.
     */
    public function index()
    {
        $sales = SalesFinishedGoods::with(['customer', 'items.product'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        return Inertia::render('SalesIndex', [
            'sales' => $sales,
        ]);
    }

    /**
     * Show form for creating a new sale.
     */
    public function create()
    {
        // Get finished goods products (kategori "Barang Jadi")
        $finishedGoodsCategory = Category::where('nama_kategori', Category::BARANG_JADI)->first();
        
        $products = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as available_stock', 'qty_current')
            ->when($finishedGoodsCategory, function ($query) use ($finishedGoodsCategory) {
                $query->where('category_id', $finishedGoodsCategory->id);
            })
            ->get()
            ->filter(fn($p) => ($p->available_stock ?? 0) > 0) // Only show products with stock
            ->map(fn($p) => [
                'id' => $p->id,
                'kode_barang' => $p->kode_barang,
                'nama_barang' => $p->nama_barang,
                'kategori' => $p->category?->nama_kategori ?? '-',
                'satuan' => $p->unit?->singkatan ?? '-',
                'available_stock' => $p->available_stock ?? 0,
                'harga_jual' => $p->harga_jual_default,
            ])
            ->values();

        $customers = Customer::select('id', 'nama_customer', 'kode_customer')
            ->orderBy('nama_customer')
            ->get();

        $receiptNumber = SalesFinishedGoods::generateReceiptNumber();

        return Inertia::render('InputPenjualanBarangJadi', [
            'products' => $products,
            'customers' => $customers,
            'receiptNumber' => $receiptNumber,
        ]);
    }

    /**
     * Store a new sale.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.harga_jual' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Minimal harus ada 1 barang untuk dijual.',
            'items.*.product_id.required' => 'Produk harus dipilih.',
            'items.*.quantity.min' => 'Kuantitas harus lebih dari 0.',
        ]);

        DB::beginTransaction();

        try {
            // Create sales header
            $sale = SalesFinishedGoods::create([
                'tanggal' => $validated['tanggal'],
                'nomor_bukti' => SalesFinishedGoods::generateReceiptNumber(),
                'customer_id' => $validated['customer_id'],
                'keterangan' => $validated['keterangan'] ?? 'Penjualan Barang Jadi',
                'total_nilai' => 0,
                'total_cogs' => 0,
                'total_profit' => 0,
            ]);

            // Process each item
            foreach ($validated['items'] as $itemData) {
                // Consume stock using inventory service (FIFO/LIFO/Average)
                $consumption = $this->inventoryService->consumeInventory(
                    $itemData['product_id'],
                    $itemData['quantity']
                );

                // Get COGS from consumption result
                // consumeInventory returns: ['total_cost' => x, 'average_cost' => y, 'consumed_batches' => [...]]
                $totalCogs = $consumption['total_cost'] ?? 0;
                $avgCostPerUnit = $consumption['average_cost'] ?? 0;

                // Create sales item
                SalesFinishedGoodsItem::create([
                    'sales_finished_goods_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'harga_jual' => $itemData['harga_jual'],
                    'harga_pokok' => $avgCostPerUnit,
                    // jumlah, total_cogs, profit calculated automatically by model
                ]);
            }

            $sale->refresh();

            DB::commit();

            return redirect()
                ->route('barang-keluar.penjualan-barang-jadi')
                ->with('success', 'Penjualan berhasil disimpan! No. Bukti: ' . $sale->nomor_bukti . ', Total: Rp ' . number_format($sale->total_nilai, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific sale.
     */
    public function show(SalesFinishedGoods $salesFinishedGood)
    {
        $salesFinishedGood->load(['customer', 'items.product.category', 'items.product.unit']);

        return Inertia::render('SalesShow', [
            'sale' => $salesFinishedGood,
        ]);
    }

    /**
     * Delete a sale (with stock restoration).
     */
    public function destroy(SalesFinishedGoods $salesFinishedGood)
    {
        DB::beginTransaction();

        try {
            // Restore inventory for each item sold
            foreach ($salesFinishedGood->items as $item) {
                $this->inventoryService->restoreInventory(
                    $item->product_id,
                    $item->quantity,
                    $item->harga_pokok,
                    'sale_reversal'
                );
            }
            
            $salesFinishedGood->items()->delete();
            $salesFinishedGood->delete();

            DB::commit();

            return redirect()
                ->route('barang-keluar.penjualan-barang-jadi')
                ->with('success', 'Penjualan berhasil dihapus dan stok dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus penjualan: ' . $e->getMessage());
        }
    }
}
