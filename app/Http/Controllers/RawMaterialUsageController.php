<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\UsageRawMaterial;
use App\Models\UsageRawMaterialItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RawMaterialUsageController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Show the form for creating a new usage record.
     */
    public function create()
    {
        // Get raw materials (Bahan Baku) category
        $rawMaterialCategory = Category::where('nama_kategori', Category::BAHAN_BAKU)->first();
        
        // Get raw material products with available stock
        $products = Product::with(['unit', 'inventoryBatches' => function ($query) {
            $query->where('qty_current', '>', 0);
        }])
            ->when($rawMaterialCategory, function ($query) use ($rawMaterialCategory) {
                $query->where('category_id', $rawMaterialCategory->id);
            })
            ->get()
            ->map(function ($product) {
                $availableStock = $product->inventoryBatches->sum('qty_current');
                $avgPrice = $product->inventoryBatches->count() > 0
                    ? $product->inventoryBatches->avg('price_per_unit')
                    : $product->harga_beli_default ?? 0;
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit->nama_satuan ?? 'pcs',
                    'available_stock' => $availableStock,
                    'harga_jual' => $product->harga_jual_default ?? 0,
                ];
            })
            ->filter(fn($p) => $p['available_stock'] > 0)
            ->values();

        // Generate reference code
        $referenceCode = UsageRawMaterial::generateReferenceCode();

        // Get all departments
        $departments = Department::orderBy('nama_departemen')->get(['id', 'nama_departemen', 'kode_departemen']);

        return Inertia::render('InputPemakaianBahanBaku', [
            'products' => $products,
            'referenceCode' => $referenceCode,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created usage record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nomor_bukti' => 'nullable|string|max:100',
            'nama_departemen' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'keterangan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'nama_departemen.required' => 'Nama departemen/produksi wajib diisi.',
            'items.required' => 'Minimal 1 bahan baku harus dipilih.',
            'items.*.product_id.required' => 'Pilih bahan baku untuk setiap baris.',
            'items.*.quantity.required' => 'Quantity wajib diisi.',
            'items.*.quantity.min' => 'Quantity minimal 0.01.',
        ]);

        // Auto-generate unique nomor_bukti if not provided or duplicate
        $nomorBukti = $validated['nomor_bukti'] ?? $this->generateNomorBukti();
        
        // Check if nomor_bukti already exists, generate new one if so
        while (UsageRawMaterial::where('nomor_bukti', $nomorBukti)->exists()) {
            $nomorBukti = $this->generateNomorBukti();
        }

        DB::beginTransaction();
        try {
            // Create usage header
            $usage = UsageRawMaterial::create([
                'tanggal' => $validated['tanggal'],
                'nomor_bukti' => $nomorBukti,
                'nama_departemen' => $validated['nama_departemen'],
                'department_id' => $validated['department_id'] ?? null,
                'kode_referensi' => UsageRawMaterial::generateReferenceCode(),
                'keterangan' => $validated['keterangan'] ?? null,
                'total_nilai' => 0,
            ]);

            // Process each item
            foreach ($validated['items'] as $item) {
                // Check stock availability first
                $currentStock = $this->inventoryService->getCurrentStock($item['product_id']);
                if ($currentStock < $item['quantity']) {
                    $product = \App\Models\Product::find($item['product_id']);
                    throw new \Exception("Stok {$product->nama_barang} tidak mencukupi. Tersedia: {$currentStock}, Dibutuhkan: {$item['quantity']}");
                }

                // Consume stock using InventoryService (FIFO/LIFO/Average)
                $consumed = $this->inventoryService->consumeInventory($item['product_id'], $item['quantity']);

                // Get selling price from product (harga jual)
                $product = \App\Models\Product::find($item['product_id']);
                $hargaJual = $product->harga_jual_default ?? 0;
                $totalNilai = $item['quantity'] * $hargaJual;

                // Create usage item with selling price
                UsageRawMaterialItem::create([
                    'usage_raw_material_id' => $usage->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga' => $hargaJual,
                    'jumlah' => $totalNilai,
                ]);
            }

            DB::commit();

            return redirect()->route('barang-keluar.pemakaian-bahan-baku')
                ->with('success', 'Pemakaian bahan baku berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // User-friendly error messages
            $errorMsg = $e->getMessage();
            
            if (str_contains($errorMsg, 'Stok') && str_contains($errorMsg, 'tidak mencukupi')) {
                // Keep the stock error as is (already user-friendly)
                $friendlyError = $errorMsg;
            } elseif (str_contains($errorMsg, 'Duplicate entry')) {
                $friendlyError = 'Nomor bukti sudah pernah digunakan. Silakan gunakan nomor bukti lain atau kosongkan untuk auto-generate.';
            } elseif (str_contains($errorMsg, 'foreign key')) {
                $friendlyError = 'Data produk tidak valid. Silakan refresh halaman dan coba lagi.';
            } else {
                $friendlyError = 'Gagal menyimpan pemakaian. Pastikan semua data sudah benar dan stok mencukupi.';
            }
            
            return redirect()->back()
                ->withErrors(['error' => $friendlyError])
                ->withInput();
        }
    }

    /**
     * Generate unique nomor bukti.
     */
    private function generateNomorBukti(): string
    {
        $date = now()->format('Ymd');
        $count = UsageRawMaterial::whereDate('tanggal', now()->toDateString())->count() + 1;
        return 'PBB-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Display a listing of usage records.
     */
    public function index()
    {
        $usages = UsageRawMaterial::with('items.product')
            ->latest('tanggal')
            ->paginate(20);

        return Inertia::render('Reports/RiwayatPemakaianBahanBaku', [
            'usages' => $usages,
        ]);
    }

    /**
     * Remove the specified usage record.
     */
    public function destroy(UsageRawMaterial $usageRawMaterial)
    {
        // Note: Should restore stock, but for now just delete the record
        // Stock restoration would need to create new inventory batches
        $usageRawMaterial->items()->delete();
        $usageRawMaterial->delete();

        return redirect()->back()->with('success', 'Data pemakaian berhasil dihapus!');
    }
}
