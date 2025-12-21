<?php

namespace App\Http\Controllers;

use App\Models\OpeningBalance;
use App\Models\OpeningBalanceItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class OpeningBalanceController extends Controller
{
    /**
     * Display a listing of opening balances.
     */
    public function index()
    {
        $openingBalances = OpeningBalance::with('items.product')
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return Inertia::render('InputSaldoAwal', [
            'openingBalances' => $openingBalances,
        ]);
    }

    /**
     * Show the form for creating a new opening balance.
     * Also shows existing products with their stock levels.
     */
    public function create()
    {
        // Get all products with their categories, units, and current stock
        $products = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as current_stock', 'qty_current')
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'category' => $product->category?->nama_kategori ?? '-',
                    'unit' => $product->unit?->singkatan ?? '-',
                    'harga_beli' => $product->harga_beli_default,
                    'harga_jual' => $product->harga_jual_default,
                    'stok' => $product->current_stock ?? 0,
                    'limit_stock' => $product->limit_stock,
                ];
            })
            ->values()
            ->toArray();

        $categories = Category::all()->toArray();
        $units = Unit::all()->toArray();

        return Inertia::render('InputSaldoAwal', [
            'products' => $products ?: [],
            'categories' => $categories ?: [],
            'units' => $units ?: [],
        ]);
    }

    /**
     * Store a newly created opening balance in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah_unit' => 'required|numeric|min:0.01',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.harga_jual' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Minimal harus ada 1 barang untuk saldo awal.',
            'items.*.product_id.required' => 'Produk harus dipilih.',
            'items.*.jumlah_unit.min' => 'Jumlah unit harus lebih dari 0.',
        ]);

        DB::beginTransaction();
        
        try {
            // Create opening balance header
            $openingBalance = OpeningBalance::create([
                'tanggal' => $validated['tanggal'],
                'total_nilai' => 0, // Will be recalculated
                'keterangan' => $validated['keterangan'] ?? 'Saldo Awal',
            ]);

            // Create items and inventory batches
            foreach ($validated['items'] as $itemData) {
                // Create opening balance item
                $item = OpeningBalanceItem::create([
                    'opening_balance_id' => $openingBalance->id,
                    'product_id' => $itemData['product_id'],
                    'jumlah_unit' => $itemData['jumlah_unit'],
                    'harga_beli' => $itemData['harga_beli'],
                    'harga_jual' => $itemData['harga_jual'],
                    // total will be auto-calculated by model
                ]);

                // Create inventory batch for tracking stock
                InventoryBatch::create([
                    'product_id' => $itemData['product_id'],
                    'reference_type' => 'opening_balance',
                    'reference_id' => $openingBalance->id,
                    'batch_number' => 'OB-' . $openingBalance->id . '-' . $item->id,
                    'tanggal_masuk' => $validated['tanggal'],
                    'qty_awal' => $itemData['jumlah_unit'],
                    'qty_current' => $itemData['jumlah_unit'],
                    'unit_cost' => $itemData['harga_beli'],
                    'unit_price' => $itemData['harga_jual'],
                ]);
            }

            // Recalculate total (will be done automatically by model events)
            $openingBalance->refresh();

            DB::commit();

            return redirect()
                ->route('input-data.saldo-awal')
                ->with('success', 'Saldo awal berhasil disimpan! Total nilai: Rp ' . number_format($openingBalance->total_nilai, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan saldo awal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified opening balance.
     */
    public function show(OpeningBalance $openingBalance)
    {
        $openingBalance->load('items.product.category', 'items.product.unit');

        return Inertia::render('OpeningBalance/Show', [
            'openingBalance' => $openingBalance,
        ]);
    }

    /**
     * Remove the specified opening balance from storage.
     */
    public function destroy(OpeningBalance $openingBalance)
    {
        DB::beginTransaction();
        
        try {
            // Delete related inventory batches
            InventoryBatch::where('reference_type', 'opening_balance')
                ->where('reference_id', $openingBalance->id)
                ->delete();

            // Delete items
            $openingBalance->items()->delete();

            // Delete header
            $openingBalance->delete();

            DB::commit();

            return redirect()
                ->route('input-data.saldo-awal')
                ->with('success', 'Saldo awal berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus saldo awal: ' . $e->getMessage());
        }
    }
}
