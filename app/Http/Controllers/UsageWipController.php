<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\UsageWip;
use App\Models\UsageWipItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UsageWipController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Show the form for creating WIP usage.
     */
    public function create()
    {
        // Get WIP products (Barang Dalam Proses) with current stock
        $wipCategory = Category::where('nama_kategori', 'Barang Dalam Proses')->first();
        
        $products = Product::with(['unit', 'inventoryBatches' => function ($query) {
            $query->where('qty_current', '>', 0);
        }])
            ->when($wipCategory, function ($query) use ($wipCategory) {
                $query->where('category_id', $wipCategory->id);
            })
            ->get()
            ->map(function ($product) {
                $availableStock = $product->inventoryBatches->sum('qty_current');
                
                // Get average HPP from inventory batches (weighted average)
                $totalValue = $product->inventoryBatches->sum(fn($b) => $b->qty_current * $b->price_per_unit);
                $avgHpp = $availableStock > 0 ? $totalValue / $availableStock : 0;
                
                return [
                    'id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'unit' => $product->unit->nama_satuan ?? 'pcs',
                    'available_stock' => $availableStock,
                    'hpp' => round($avgHpp, 2), // HPP dari inventory
                ];
            })
            ->filter(fn($p) => $p['available_stock'] > 0)
            ->values();

        // Generate reference code
        $referenceCode = UsageWip::generateReferenceCode();

        // Get all departments
        $departments = Department::orderBy('nama_departemen')->get(['id', 'nama_departemen', 'kode_departemen']);

        return Inertia::render('InputPemakaianBarangDalamProses', [
            'products' => $products,
            'referenceCode' => $referenceCode,
            'departments' => $departments,
        ]);
    }

    /**
     * Store WIP usage record.
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
            'items.required' => 'Minimal 1 barang harus dipilih.',
            'items.*.product_id.required' => 'Pilih barang untuk setiap baris.',
            'items.*.quantity.required' => 'Quantity wajib diisi.',
            'items.*.quantity.min' => 'Quantity minimal 0.01.',
        ]);

        // Auto-generate unique nomor_bukti if not provided
        $nomorBukti = $validated['nomor_bukti'] ?? $this->generateNomorBukti();
        
        // Check if nomor_bukti already exists
        while (UsageWip::where('nomor_bukti', $nomorBukti)->exists()) {
            $nomorBukti = $this->generateNomorBukti();
        }

        DB::beginTransaction();
        try {
            // Create usage header
            $usage = UsageWip::create([
                'tanggal' => $validated['tanggal'],
                'nomor_bukti' => $nomorBukti,
                'nama_departemen' => $validated['nama_departemen'],
                'department_id' => $validated['department_id'] ?? null,
                'kode_referensi' => UsageWip::generateReferenceCode(),
                'keterangan' => $validated['keterangan'] ?? null,
                'total_nilai' => 0,
            ]);

            // Process each item
            foreach ($validated['items'] as $item) {
                // Check stock availability
                $currentStock = $this->inventoryService->getCurrentStock($item['product_id']);
                if ($currentStock < $item['quantity']) {
                    $product = Product::find($item['product_id']);
                    throw new \Exception("Stok {$product->nama_barang} tidak mencukupi. Tersedia: {$currentStock}, Dibutuhkan: {$item['quantity']}");
                }

                // Consume stock using InventoryService (FIFO/LIFO/Average)
                $consumed = $this->inventoryService->consumeInventory($item['product_id'], $item['quantity']);

                // Get HPP from consumed inventory (FIFO method)
                $hpp = $consumed['cost_per_unit'] ?? $this->inventoryService->getAveragePrice($item['product_id']);
                $totalNilai = $item['quantity'] * $hpp;

                // Create usage item with HPP (bukan harga jual)
                UsageWipItem::create([
                    'usage_wip_id' => $usage->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga' => $hpp,
                    'jumlah' => $totalNilai,
                ]);
            }

            DB::commit();

            return redirect()->route('barang-keluar.pemakaian-barang-dalam-proses')
                ->with('success', 'Pemakaian Barang Dalam Proses berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // User-friendly error messages
            $errorMsg = $e->getMessage();
            
            if (str_contains($errorMsg, 'Stok') && str_contains($errorMsg, 'tidak mencukupi')) {
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
        $count = UsageWip::whereDate('tanggal', now()->toDateString())->count() + 1;
        return 'PWIP-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
