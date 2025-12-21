<?php

namespace App\Exports;

use App\Models\InventoryBatch;
use App\Models\SalesFinishedGoodsItem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockHistoryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $movements = collect();

        $startDate = $this->request->start_date ? \Carbon\Carbon::parse($this->request->start_date)->startOfDay() : null;
        $endDate = $this->request->end_date ? \Carbon\Carbon::parse($this->request->end_date)->endOfDay() : null;

        // 1. Inventory Batches
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

        // 2. Usage Items
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

        // 3. Sales Items
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

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Jenis Transaksi',
            'Masuk',
            'Keluar',
            'Satuan',
            'Harga Satuan',
            'No. Referensi',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

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
