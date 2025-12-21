<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\WipEntry;
use App\Models\WipEntryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WipEntryController extends Controller
{
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

        // Generate invoice number
        $invoiceNumber = WipEntry::generateInvoiceNumber();

        return Inertia::render('InputBarangDalamProses', [
            'suppliers' => $suppliers,
            'products' => $products,
            'invoiceNumber' => $invoiceNumber,
        ]);
    }

    /**
     * Store a newly created WIP entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nomor_faktur' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.harga_beli' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Minimal 1 barang harus dipilih.',
            'items.*.product_id.required' => 'Pilih barang untuk setiap baris.',
            'items.*.quantity.required' => 'Quantity wajib diisi.',
            'items.*.quantity.min' => 'Quantity minimal 0.01.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi.',
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
                'supplier_id' => $validated['supplier_id'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'total_nilai' => 0,
            ]);

            // Create WIP items (will auto-create inventory batches via model event)
            foreach ($validated['items'] as $item) {
                WipEntryItem::create([
                    'wip_entry_id' => $wipEntry->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga_beli' => $item['harga_beli'],
                ]);
            }

            DB::commit();

            return redirect()->route('barang-masuk.barang-dalam-proses')
                ->with('success', 'Barang Dalam Proses berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // User-friendly error messages
            $errorMsg = $e->getMessage();
            
            if (str_contains($errorMsg, 'Duplicate entry')) {
                $friendlyError = 'Nomor faktur sudah pernah digunakan. Silakan gunakan nomor faktur lain atau kosongkan untuk auto-generate.';
            } elseif (str_contains($errorMsg, 'foreign key')) {
                $friendlyError = 'Data produk atau supplier tidak valid. Silakan refresh halaman dan coba lagi.';
            } else {
                $friendlyError = 'Gagal menyimpan. Pastikan semua data sudah benar.';
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
