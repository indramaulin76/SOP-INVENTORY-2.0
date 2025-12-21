<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRawMaterial;
use App\Models\PurchaseRawMaterialItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PurchaseRawMaterialController extends Controller
{
    /**
     * Display a listing of purchases.
     */
    public function index()
    {
        $purchases = PurchaseRawMaterial::with(['supplier', 'items.product'])
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return Inertia::render('PurchaseRawMaterial/Index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        // Get Bahan Baku category
        $bahanBakuCategory = Category::where('nama_kategori', Category::BAHAN_BAKU)->first();
        
        // Only get Bahan Baku products for raw material purchase
        $products = Product::with(['category', 'unit'])
            ->where('category_id', $bahanBakuCategory->id)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'category' => $product->category?->nama_kategori ?? '-',
                    'unit' => $product->unit?->nama_satuan ?? '-',
                ];
            })
            ->values()
            ->toArray();

        $suppliers = Supplier::orderBy('nama_supplier')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'kode_supplier' => $supplier->kode_supplier,
                    'nama_supplier' => $supplier->nama_supplier,
                    'alamat' => $supplier->alamat,
                    'telepon' => $supplier->telepon,
                ];
            })
            ->values()
            ->toArray();

        // Generate next invoice number
        $nextInvoice = PurchaseRawMaterial::generateInvoiceNumber();

        return Inertia::render('InputPembelianBahanBaku', [
            'products' => $products ?: [],
            'suppliers' => $suppliers ?: [],
            'nextInvoice' => $nextInvoice,
        ]);
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.harga_beli' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Minimal harus ada 1 barang untuk pembelian.',
            'supplier_id.required' => 'Supplier harus dipilih.',
            'items.*.product_id.required' => 'Produk harus dipilih.',
            'items.*.quantity.min' => 'Quantity harus lebih dari 0.',
        ]);

        DB::beginTransaction();
        
        try {
            // Generate invoice number
            $invoiceNumber = PurchaseRawMaterial::generateInvoiceNumber();

            // Create purchase header
            $purchase = PurchaseRawMaterial::create([
                'tanggal' => $validated['tanggal'],
                'nomor_faktur' => $invoiceNumber,
                'supplier_id' => $validated['supplier_id'],
                'keterangan' => $validated['keterangan'] ?? 'Pembelian bahan baku',
                'total_nilai' => 0, // Will be recalculated
            ]);

            // Create items (inventory batches will be created automatically by model events)
            foreach ($validated['items'] as $itemData) {
                PurchaseRawMaterialItem::create([
                    'purchase_raw_material_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'harga_beli' => $itemData['harga_beli'],
                    // jumlah will be auto-calculated by model
                ]);
            }

            // Refresh to get updated total
            $purchase->refresh();

            DB::commit();

            return redirect()
                ->route('barang-masuk.pembelian-bahan-baku')
                ->with('success', "Pembelian berhasil disimpan! Invoice: {$invoiceNumber} | Total: Rp " . number_format($purchase->total_nilai, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pembelian: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(PurchaseRawMaterial $purchaseRawMaterial)
    {
        $purchaseRawMaterial->load('supplier', 'items.product.unit');

        return Inertia::render('PurchaseRawMaterial/Show', [
            'purchase' => $purchaseRawMaterial,
        ]);
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(PurchaseRawMaterial $purchaseRawMaterial)
    {
        DB::beginTransaction();
        
        try {
            // Delete related inventory batches
            \App\Models\InventoryBatch::where('reference_type', 'purchase')
                ->where('reference_id', $purchaseRawMaterial->id)
                ->delete();

            // Delete items
            $purchaseRawMaterial->items()->delete();

            // Delete header
            $purchaseRawMaterial->delete();

            DB::commit();

            return redirect()
                ->route('barang-masuk.pembelian-bahan-baku')
                ->with('success', 'Pembelian berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }
}
