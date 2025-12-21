<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\SalesFinishedGoods;
use App\Models\SalesFinishedGoodsItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockHistoryExport;
use App\Exports\ProfitLossExport;

class ReportExportController extends Controller
{
    /**
     * Export Stock History to PDF
     */
    public function stockHistoryPdf(Request $request)
    {
        $movements = $this->getStockHistoryData($request);
        
        $pdf = Pdf::loadView('exports.stock-history-pdf', [
            'movements' => $movements,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'riwayat-stok-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Stock History to Excel
     */
    public function stockHistoryExcel(Request $request)
    {
        $filename = 'riwayat-stok-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StockHistoryExport($request), $filename);
    }

    /**
     * Export Profit Loss Report to PDF
     */
    public function profitLossPdf(Request $request)
    {
        $data = $this->getProfitLossData($request);
        
        $pdf = Pdf::loadView('exports.profit-loss-pdf', [
            'salesData' => $data['salesData'],
            'summary' => $data['summary'],
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'penjualan-laba-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Profit Loss Report to Excel
     */
    public function profitLossExcel(Request $request)
    {
        $filename = 'penjualan-laba-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ProfitLossExport($request), $filename);
    }

    /**
     * Get stock history data with filters
     */
    private function getStockHistoryData(Request $request)
    {
        $movements = collect();

        // Build date filter
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;

        // 1. Inventory Batches (Inbound)
        $batchQuery = InventoryBatch::with(['product.category', 'product.unit']);
        if ($startDate) $batchQuery->where('date_in', '>=', $startDate);
        if ($endDate) $batchQuery->where('date_in', '<=', $endDate);
        
        foreach ($batchQuery->orderBy('date_in', 'desc')->get() as $batch) {
            $movements->push([
                'tanggal' => $batch->date_in?->format('d/m/Y') ?? '-',
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

        // 2. Usage Raw Material Items (Outbound)
        $usageQuery = \App\Models\UsageRawMaterialItem::with(['product.category', 'product.unit', 'usageRawMaterial']);
        if ($startDate || $endDate) {
            $usageQuery->whereHas('usageRawMaterial', function($q) use ($startDate, $endDate) {
                if ($startDate) $q->where('tanggal', '>=', $startDate);
                if ($endDate) $q->where('tanggal', '<=', $endDate);
            });
        }
        
        foreach ($usageQuery->get() as $item) {
            $movements->push([
                'tanggal' => $item->usageRawMaterial->tanggal?->format('d/m/Y') ?? '-',
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

        // 3. Sales Items (Outbound)
        $salesQuery = SalesFinishedGoodsItem::with(['product.category', 'product.unit', 'salesFinishedGoods']);
        if ($startDate || $endDate) {
            $salesQuery->whereHas('salesFinishedGoods', function($q) use ($startDate, $endDate) {
                if ($startDate) $q->where('tanggal', '>=', $startDate);
                if ($endDate) $q->where('tanggal', '<=', $endDate);
            });
        }
        
        foreach ($salesQuery->get() as $item) {
            $movements->push([
                'tanggal' => $item->salesFinishedGoods->tanggal?->format('d/m/Y') ?? '-',
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

        return $movements->sortByDesc('tanggal')->values();
    }

    /**
     * Get profit loss data with filters
     */
    private function getProfitLossData(Request $request)
    {
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;

        $query = SalesFinishedGoods::with(['items.product', 'customer']);
        if ($startDate) $query->where('tanggal', '>=', $startDate);
        if ($endDate) $query->where('tanggal', '<=', $endDate);

        $sales = $query->orderBy('tanggal', 'desc')->get();

        $salesData = [];
        $totalPenjualan = 0;
        $totalHPP = 0;
        $totalProfit = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $subtotal = $item->quantity * $item->harga_jual;
                $hpp = $item->total_cogs ?? 0;
                $profit = $subtotal - $hpp;

                $salesData[] = [
                    'tanggal' => $sale->tanggal->format('d/m/Y'),
                    'nomor_bukti' => $sale->nomor_bukti,
                    'customer' => $sale->customer->nama_customer ?? 'Umum',
                    'nama_barang' => $item->product->nama_barang ?? '-',
                    'qty' => $item->quantity,
                    'harga_jual' => $item->harga_jual,
                    'subtotal' => $subtotal,
                    'hpp' => $hpp,
                    'profit' => $profit,
                ];

                $totalPenjualan += $subtotal;
                $totalHPP += $hpp;
                $totalProfit += $profit;
            }
        }

        return [
            'salesData' => $salesData,
            'summary' => [
                'totalPenjualan' => $totalPenjualan,
                'totalHPP' => $totalHPP,
                'totalProfit' => $totalProfit,
                'marginPercentage' => $totalPenjualan > 0 ? round(($totalProfit / $totalPenjualan) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get human-readable source label
     */
    private function getSourceLabel(string $source): string
    {
        return match($source) {
            'purchase' => 'Pembelian',
            'opening_balance' => 'Saldo Awal',
            'production' => 'Hasil Produksi',
            'adjustment' => 'Penyesuaian',
            'wip' => 'WIP Masuk',
            default => ucfirst($source),
        };
    }
}
