<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit'])
            ->latest()
            ->paginate(10);

        return response()->json($products); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'nama_kategori', 'kode_kategori')->get();
        $units = Unit::select('id', 'nama_satuan', 'singkatan')->get();

        return Inertia::render('InputBarang', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * NOTE: This is MASTER DATA only - no stock or inventory batches created here.
     * Stock should be added via "Input Barang Masuk" or "Saldo Awal" menus.
     */
    public function store(Request $request)
    {
        // Block karyawan from adding products
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data barang.');
        }

        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'limit_stock' => 'required|integer|min:0',
            'harga_jual_default' => 'nullable|numeric|min:0',
        ]);

        // Auto-generate product code
        $validated['kode_barang'] = Product::generateCode($validated['category_id']);
        
        // Set defaults for pricing - will be updated via purchase transactions
        $validated['harga_beli_default'] = 0;
        $validated['harga_jual_default'] = $validated['harga_jual_default'] ?? 0;

        Product::create($validated);

        return redirect()->back()->with('success', 'Data barang berhasil disimpan! Tambahkan stok melalui menu Input Barang Masuk atau Saldo Awal.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->back()->with('success', 'Data barang berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                // Kirim sinyal ke Frontend untuk memunculkan Pilihan
                return redirect()->back()->with('delete_confirmation_needed', [
                    'id' => $product->id,
                    'name' => $product->nama_barang,
                    'message' => 'Produk ini tidak bisa dihapus karena sudah dipakai dalam transaksi (Saldo Awal, Produksi, atau Pembelian). Pilih tindakan:'
                ]);
            }
            // Re-throw other errors so we don't silence unexpected issues
            throw $e;
        }
    }

    /**
     * PILIHAN 1: ARSIPKAN (Sembunyikan dari daftar baru) - REKOMENDASI
     * 
     * Set active = false. Product will be hidden from new transaction dropdowns
     * but historical data remains intact for reporting.
     */
    public function archive($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['active' => false]);

        return redirect()->back()->with('success', 'Produk berhasil diarsipkan. Data lama tetap aman, tapi tidak akan muncul di input baru.');
    }

    /**
     * PILIHAN 2: HAPUS PAKSA (Hapus Produk + Semua Sejarahnya) - BAHAYA
     * 
     * Cascade delete the product and ALL related transactions.
     * WARNING: This will affect financial reports and audit trails.
     */
    public function forceDestroy($id)
    {
        DB::beginTransaction();
        try {
            // 1. Hapus semua jejak transaksi anak-anaknya dulu
            \App\Models\InventoryBatch::where('product_id', $id)->delete();
            \App\Models\OpeningBalanceItem::where('product_id', $id)->delete();
            
            // Purchase & Usage Raw Materials
            if (class_exists('\App\Models\PurchaseRawMaterialItem')) {
                \App\Models\PurchaseRawMaterialItem::where('product_id', $id)->delete();
            }
            if (class_exists('\App\Models\UsageRawMaterialItem')) {
                \App\Models\UsageRawMaterialItem::where('product_id', $id)->delete();
            }
            
            // WIP Entries & Usage
            if (class_exists('\App\Models\WipEntryItem')) {
                \App\Models\WipEntryItem::where('product_id', $id)->delete();
            }
            \App\Models\UsageWipItem::where('product_id', $id)->delete();
            
            // Finished Goods Sales
            if (class_exists('\App\Models\SalesFinishedGoodsItem')) {
                \App\Models\SalesFinishedGoodsItem::where('product_id', $id)->delete();
            }

            // 2. Baru hapus induknya
            $product = Product::findOrFail($id);
            $product->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Produk dan SELURUH riwayat transaksinya berhasil dihapus permanen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal hapus paksa: ' . $e->getMessage()]);
        }
    }
}
