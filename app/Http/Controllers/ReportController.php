<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Laporan Data Barang
     * 
     * Menampilkan semua produk dengan stok dan harga.
     * Filter: kategori, pencarian nama/kode barang.
     */
    public function dataBarang(Request $request)
    {
        $categories = \App\Models\Category::where('nama_kategori', '!=', 'Kemasan')->orderBy('nama_kategori')->get(['id', 'nama_kategori']);
        
        $query = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as current_stock', 'qty_current');
        
        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }
        
        $products = $query->latest()->paginate(20)->withQueryString();

        // Transform data
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'tanggal_input' => $product->created_at->format('d/m/Y'),
                'kode_barang' => $product->kode_barang,
                'nama_barang' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? '-',
                'satuan' => $product->unit->singkatan ?? '-',
                'current_stock' => $product->current_stock ?? 0,
                'harga_beli' => $product->harga_beli_default,
                'harga_jual' => $product->harga_jual_default,
                'limit_stock' => $product->limit_stock,
                'is_low_stock' => ($product->current_stock ?? 0) <= $product->limit_stock,
            ];
        });

        return Inertia::render('LaporanDataBarang', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    /**
     * Hapus produk (hard delete dengan cascade)
     * 
     * Menghapus produk beserta semua batch inventory terkait.
     * Hati-hati: data tidak bisa dikembalikan!
     */
    public function deleteProduct(Product $product)
    {
        // Hard delete with cascade
        DB::beginTransaction();
        try {
            // Delete related inventory batches
            $product->inventoryBatches()->delete();
            
            // Force delete the product (bypass soft delete)
            $product->forceDelete();

            DB::commit();
            return redirect()->back()->with('success', 'Data barang berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat Transaksi Stok - Stock movement history
     */
    public function riwayatStok(Request $request)
    {
        // Get categories for filter dropdown
        $categories = \App\Models\Category::where('nama_kategori', '!=', 'Kemasan')->orderBy('nama_kategori')->get(['id', 'nama_kategori']);
        
        // Parse date filters
        $dateFrom = $request->dateFrom ? \Carbon\Carbon::parse($request->dateFrom)->startOfDay() : null;
        $dateTo = $request->dateTo ? \Carbon\Carbon::parse($request->dateTo)->endOfDay() : null;
        $categoryId = $request->category_id;

        // Collect all stock movements from various sources
        $movements = collect();

        // 1. Inbound from InventoryBatch (Purchases, Opening Balance, Production Results)
        $batchQuery = InventoryBatch::with(['product.category', 'product.unit']);
        if ($dateFrom) $batchQuery->where('date_in', '>=', $dateFrom);
        if ($dateTo) $batchQuery->where('date_in', '<=', $dateTo);
        if ($categoryId) {
            $batchQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        $batches = $batchQuery->orderBy('created_at', 'desc')->get();

        foreach ($batches as $batch) {
            $movements->push([
                'id' => 'batch-' . $batch->id,
                'tanggal' => $batch->date_in?->format('d/m/Y') ?? $batch->created_at->format('d/m/Y'),
                'sort_date' => $batch->date_in ?? $batch->created_at,
                'product_id' => $batch->product_id,
                'kode_barang' => $batch->product->kode_barang ?? '-',
                'nama_barang' => $batch->product->nama_barang ?? '-',
                'kategori' => $batch->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => $this->getSourceLabel($batch->source),
                'masuk' => $batch->qty_initial,
                'keluar' => 0,
                'satuan' => $batch->product->unit->singkatan ?? '-',
                'harga_satuan' => $batch->price_per_unit,
                'no_referensi' => $batch->batch_no,
            ]);
        }

        // 2. Usage from UsageRawMaterialItem (Pemakaian Bahan Baku)
        $usageQuery = \App\Models\UsageRawMaterialItem::with(['product.category', 'product.unit', 'usageRawMaterial']);
        if ($dateFrom || $dateTo || $categoryId) {
            $usageQuery->whereHas('usageRawMaterial', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('tanggal', '>=', $dateFrom);
                if ($dateTo) $q->where('tanggal', '<=', $dateTo);
            });
            if ($categoryId) {
                $usageQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            }
        }
        $usageItems = $usageQuery->orderBy('created_at', 'desc')->get();

        foreach ($usageItems as $item) {
            $movements->push([
                'id' => 'usage-' . $item->id,
                'tanggal' => $item->usageRawMaterial->tanggal?->format('d/m/Y') ?? $item->created_at->format('d/m/Y'),
                'sort_date' => $item->usageRawMaterial->tanggal ?? $item->created_at,
                'product_id' => $item->product_id,
                'kode_barang' => $item->product->kode_barang ?? '-',
                'nama_barang' => $item->product->nama_barang ?? '-',
                'kategori' => $item->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => 'Pemakaian',
                'masuk' => 0,
                'keluar' => $item->quantity,
                'satuan' => $item->product->unit->singkatan ?? '-',
                'harga_satuan' => $item->harga,
                'no_referensi' => $item->usageRawMaterial->kode_referensi ?? '-',
            ]);
        }

        // 3. WIP Entry from WipEntryItem (Barang Dalam Proses Masuk)
        $wipQuery = \App\Models\WipEntryItem::with(['product.category', 'product.unit', 'wipEntry']);
        if ($dateFrom || $dateTo || $categoryId) {
            $wipQuery->whereHas('wipEntry', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('tanggal', '>=', $dateFrom);
                if ($dateTo) $q->where('tanggal', '<=', $dateTo);
            });
            if ($categoryId) {
                $wipQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            }
        }
        $wipItems = $wipQuery->orderBy('created_at', 'desc')->get();

        foreach ($wipItems as $item) {
            $movements->push([
                'id' => 'wip-' . $item->id,
                'tanggal' => $item->wipEntry->tanggal?->format('d/m/Y') ?? $item->created_at->format('d/m/Y'),
                'sort_date' => $item->wipEntry->tanggal ?? $item->created_at,
                'product_id' => $item->product_id,
                'kode_barang' => $item->product->kode_barang ?? '-',
                'nama_barang' => $item->product->nama_barang ?? '-',
                'kategori' => $item->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => 'WIP Masuk',
                'masuk' => $item->quantity,
                'keluar' => 0,
                'satuan' => $item->product->unit->singkatan ?? '-',
                'harga_satuan' => $item->harga_beli,
                'no_referensi' => $item->wipEntry->nomor_faktur ?? '-',
            ]);
        }

        // 4. WIP Usage from UsageWipItem (Pemakaian Barang Dalam Proses)
        $wipUsageQuery = \App\Models\UsageWipItem::with(['product.category', 'product.unit', 'usageWip']);
        if ($dateFrom || $dateTo || $categoryId) {
            $wipUsageQuery->whereHas('usageWip', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('tanggal', '>=', $dateFrom);
                if ($dateTo) $q->where('tanggal', '<=', $dateTo);
            });
            if ($categoryId) {
                $wipUsageQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            }
        }
        $wipUsageItems = $wipUsageQuery->orderBy('created_at', 'desc')->get();

        foreach ($wipUsageItems as $item) {
            $movements->push([
                'id' => 'wip-usage-' . $item->id,
                'tanggal' => $item->usageWip->tanggal?->format('d/m/Y') ?? $item->created_at->format('d/m/Y'),
                'sort_date' => $item->usageWip->tanggal ?? $item->created_at,
                'product_id' => $item->product_id,
                'kode_barang' => $item->product->kode_barang ?? '-',
                'nama_barang' => $item->product->nama_barang ?? '-',
                'kategori' => $item->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => 'Pemakaian WIP',
                'masuk' => 0,
                'keluar' => $item->quantity,
                'satuan' => $item->product->unit->singkatan ?? '-',
                'harga_satuan' => $item->harga,
                'no_referensi' => $item->usageWip->kode_referensi ?? '-',
            ]);
        }

        // 5. Sales from SalesFinishedGoodsItem (Penjualan Barang Jadi)
        $salesQuery = \App\Models\SalesFinishedGoodsItem::with(['product.category', 'product.unit', 'salesFinishedGoods']);
        if ($dateFrom || $dateTo || $categoryId) {
            $salesQuery->whereHas('salesFinishedGoods', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('tanggal', '>=', $dateFrom);
                if ($dateTo) $q->where('tanggal', '<=', $dateTo);
            });
            if ($categoryId) {
                $salesQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            }
        }
        $salesItems = $salesQuery->orderBy('created_at', 'desc')->get();

        foreach ($salesItems as $item) {
            $movements->push([
                'id' => 'sales-' . $item->id,
                'tanggal' => $item->salesFinishedGoods->tanggal?->format('d/m/Y') ?? $item->created_at->format('d/m/Y'),
                'sort_date' => $item->salesFinishedGoods->tanggal ?? $item->created_at,
                'product_id' => $item->product_id,
                'kode_barang' => $item->product->kode_barang ?? '-',
                'nama_barang' => $item->product->nama_barang ?? '-',
                'kategori' => $item->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => 'Penjualan',
                'masuk' => 0,
                'keluar' => $item->quantity,
                'satuan' => $item->product->unit->singkatan ?? '-',
                'harga_satuan' => $item->harga_jual,
                'no_referensi' => $item->salesFinishedGoods->nomor_bukti ?? '-',
            ]);
        }

        // 6. Production Materials from FinishedGoodsProductionMaterial (Bahan Baku untuk Produksi)
        $prodMatQuery = \App\Models\FinishedGoodsProductionMaterial::with([
            'product.category', 
            'product.unit', 
            'finishedGoodsProduction.product'
        ]);
        if ($dateFrom || $dateTo || $categoryId) {
            $prodMatQuery->whereHas('finishedGoodsProduction', function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('tanggal', '>=', $dateFrom);
                if ($dateTo) $q->where('tanggal', '<=', $dateTo);
            });
            if ($categoryId) {
                $prodMatQuery->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            }
        }
        $productionMaterials = $prodMatQuery->orderBy('created_at', 'desc')->get();

        foreach ($productionMaterials as $item) {
            $production = $item->finishedGoodsProduction;
            $finishedProductName = $production?->product?->nama_barang ?? 'Unknown';
            
            $movements->push([
                'id' => 'prod-mat-' . $item->id,
                'tanggal' => $production?->tanggal?->format('d/m/Y') ?? $item->created_at->format('d/m/Y'),
                'sort_date' => $production?->tanggal ?? $item->created_at,
                'product_id' => $item->product_id,
                'kode_barang' => $item->product->kode_barang ?? '-',
                'nama_barang' => $item->product->nama_barang ?? '-',
                'kategori' => $item->product->category->nama_kategori ?? '-',
                'jenis_transaksi' => 'Pemakaian Produksi',
                'masuk' => 0,
                'keluar' => $item->quantity,
                'satuan' => $item->product->unit->singkatan ?? '-',
                'harga_satuan' => $item->cost_per_unit,
                'no_referensi' => $production?->nomor_produksi ?? '-',
                'keterangan' => "Untuk produksi: {$finishedProductName}",
            ]);
        }

        // Sort by date ASCENDING first (oldest to newest) to calculate running balance
        $sortedAsc = $movements->sortBy('sort_date')->values();
        
        // Calculate running balance per product
        $runningBalances = [];
        $movementsWithSaldo = $sortedAsc->map(function ($movement) use (&$runningBalances) {
            $productId = $movement['product_id'];
            
            // Initialize balance for this product if not exists
            if (!isset($runningBalances[$productId])) {
                $runningBalances[$productId] = 0;
            }
            
            // Update running balance
            $runningBalances[$productId] += $movement['masuk'];
            $runningBalances[$productId] -= $movement['keluar'];
            
            // Add saldo to movement
            $movement['saldo'] = max(0, $runningBalances[$productId]);
            
            return $movement;
        });
        
        // Now sort by date DESCENDING for display (newest first)
        $sortedMovements = $movementsWithSaldo->sortByDesc('sort_date')->values();

        // Calculate totals for summary (based on filtered data)
        $totalMasuk = $movements->sum('masuk');
        $totalKeluar = $movements->sum('keluar');
        $totalNilai = $movements->sum(function ($m) {
            return ($m['masuk'] - $m['keluar']) * $m['harga_satuan'];
        });

        // Paginate manually
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $total = $sortedMovements->count();
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedData = $sortedMovements->slice($offset, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('RiwayatTransaksiStok', [
            'transactions' => $paginated,
            'summary' => [
                'totalMasuk' => $totalMasuk,
                'totalKeluar' => $totalKeluar,
                'totalTransaksi' => $total,
                'totalNilai' => abs($totalNilai),
            ],
            'categories' => $categories,
            'filters' => $request->only(['dateFrom', 'dateTo', 'category_id']),
        ]);
    }

    /**
     * Penjualan & Laba Report - Real P&L Analysis
     */
    public function penjualanLaba(Request $request)
    {
        $query = \App\Models\SalesFinishedGoods::with(['customer', 'items.product.category', 'items.product.unit']);

        // Date filter
        if ($request->dateFrom) {
            $query->whereDate('tanggal', '>=', $request->dateFrom);
        }
        if ($request->dateTo) {
            $query->whereDate('tanggal', '<=', $request->dateTo);
        }

        $salesData = $query->latest('tanggal')->get();

        // Transform sales data for display
        $sales = [];
        foreach ($salesData as $sale) {
            foreach ($sale->items as $item) {
                $sales[] = [
                    'id' => $item->id,
                    'tanggal' => $sale->tanggal?->format('d/m/Y') ?? '-',
                    'nomor_bukti' => $sale->nomor_bukti,
                    'customer' => $sale->customer->nama_customer ?? 'Umum',
                    'kode_barang' => $item->product->kode_barang ?? '-',
                    'nama_barang' => $item->product->nama_barang ?? '-',
                    'kategori' => $item->product->category->nama_kategori ?? '-',
                    'satuan' => $item->product->unit->singkatan ?? 'pcs',
                    'quantity' => $item->quantity,
                    'harga_jual' => $item->harga_jual,
                    'revenue' => $item->jumlah,
                    'harga_pokok' => $item->harga_pokok,
                    'cogs' => $item->total_cogs,
                    'gross_profit' => $item->profit,
                    'profit_margin' => $item->jumlah > 0 ? ($item->profit / $item->jumlah) * 100 : 0,
                ];
            }
        }

        // Calculate totals
        $totalRevenue = collect($sales)->sum('revenue');
        $totalCogs = collect($sales)->sum('cogs');
        $totalProfit = collect($sales)->sum('gross_profit');
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Group by product for analysis
        $productAnalysis = collect($sales)
            ->groupBy('kode_barang')
            ->map(function ($items) {
                return [
                    'kode_barang' => $items->first()['kode_barang'],
                    'nama_barang' => $items->first()['nama_barang'],
                    'total_qty' => $items->sum('quantity'),
                    'total_revenue' => $items->sum('revenue'),
                    'total_cogs' => $items->sum('cogs'),
                    'total_profit' => $items->sum('gross_profit'),
                ];
            })
            ->sortByDesc('total_profit')
            ->values();

        return Inertia::render('AnalisisPenjualanLaba', [
            'sales' => $sales,
            'productAnalysis' => $productAnalysis,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCogs,
            'totalProfit' => $totalProfit,
            'profitMargin' => round($profitMargin, 2),
            'transactionCount' => $salesData->count(),
            'filters' => $request->only(['dateFrom', 'dateTo']),
        ]);
    }

    /**
     * Kartu Stok - Stock card per product
     */
    public function kartuStok(Request $request)
    {
        $products = Product::with('category')
            ->withSum('inventoryBatches as current_stock', 'qty_current')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'kode_barang' => $p->kode_barang,
                'nama_barang' => $p->nama_barang,
                'kategori' => $p->category->nama_kategori ?? '-',
            ]);

        $selectedProduct = null;
        $movements = [];

        if ($request->product_id) {
            $selectedProduct = Product::with(['category', 'unit'])->find($request->product_id);
            $movements = InventoryBatch::where('product_id', $request->product_id)
                ->orderBy('date_in')
                ->get()
                ->map(fn($b) => [
                    'tanggal' => $b->date_in?->format('d/m/Y') ?? '-',
                    'keterangan' => $this->getSourceLabel($b->source) . ' - ' . $b->batch_no,
                    'masuk' => $b->qty_initial,
                    'keluar' => $b->qty_initial - $b->qty_current,
                    'saldo' => $b->qty_current,
                    'harga' => $b->price_per_unit,
                ]);
        }

        return Inertia::render('KartuStok', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'movements' => $movements,
            'filters' => $request->only(['product_id', 'dateFrom', 'dateTo']),
        ]);
    }

    /**
     * Stock Opname
     */
    public function stockOpname(Request $request)
    {
        $inventoryService = app(\App\Services\InventoryService::class);
        
        $products = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as system_stock', 'qty_current')
            ->get()
            ->map(function ($p) use ($inventoryService) {
                $avgPrice = $inventoryService->getAveragePrice($p->id);
                return [
                    'id' => $p->id,
                    'kode_barang' => $p->kode_barang,
                    'nama_barang' => $p->nama_barang,
                    'kategori' => $p->category->nama_kategori ?? '-',
                    'satuan' => $p->unit->singkatan ?? '-',
                    'qty_system' => $p->system_stock ?? 0,
                    'avg_price' => $avgPrice,
                ];
            });

        $opnameNumber = \App\Models\StockOpname::generateOpnameNumber();

        return Inertia::render('HasilStockOpname', [
            'products' => $products,
            'opnameNumber' => $opnameNumber,
        ]);
    }

    /**
     * Status Barang - Current stock levels
     */
    public function statusBarang(Request $request)
    {
        $categories = \App\Models\Category::where('nama_kategori', '!=', 'Kemasan')->orderBy('nama_kategori')->get(['id', 'nama_kategori']);
        
        $query = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as current_stock', 'qty_current');
        
        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }
        
        // Filter by stock status
        if ($request->status === 'low') {
            $query->whereRaw('(SELECT COALESCE(SUM(qty_current), 0) FROM inventory_batches WHERE inventory_batches.product_id = products.id) <= products.limit_stock');
        } elseif ($request->status === 'safe') {
            $query->whereRaw('(SELECT COALESCE(SUM(qty_current), 0) FROM inventory_batches WHERE inventory_batches.product_id = products.id) > products.limit_stock');
        }
        
        $products = $query->get()->map(fn($p) => [
            'id' => $p->id,
            'kode_barang' => $p->kode_barang,
            'nama_barang' => $p->nama_barang,
            'kategori' => $p->category->nama_kategori ?? '-',
            'satuan' => $p->unit->singkatan ?? '-',
            'stok' => $p->current_stock ?? 0,
            'limit' => $p->limit_stock,
            'status' => ($p->current_stock ?? 0) <= $p->limit_stock ? 'Rendah' : 'Aman',
            'nilai' => ($p->current_stock ?? 0) * $p->harga_beli_default,
        ]);

        $totalValue = $products->sum('nilai');
        $lowStockCount = $products->where('status', 'Rendah')->count();

        return Inertia::render('StatusBarang', [
            'products' => $products,
            'categories' => $categories,
            'summary' => [
                'totalProducts' => $products->count(),
                'totalValue' => $totalValue,
                'lowStockCount' => $lowStockCount,
            ],
            'filters' => $request->only(['search', 'category_id', 'status']),
        ]);
    }

    /**
     * Helper to get source label
     */
    private function getSourceLabel(?string $source): string
    {
        return match ($source) {
            'opening_balance' => 'Saldo Awal',
            'purchase' => 'Pembelian',
            'production' => 'Produksi',
            'production_result' => 'Hasil Produksi',
            'sale' => 'Penjualan',
            'usage' => 'Pemakaian',
            'adjustment' => 'Penyesuaian',
            default => ucfirst($source ?? '-'),
        };
    }
}
