<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutasiStokExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected Request $request;
    protected int $month;
    protected int $year;
    protected $periodStart;
    protected $periodEnd;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->month = $request->input('month', now()->month);
        $this->year = $request->input('year', now()->year);
        $this->periodStart = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $this->periodEnd = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
    }

    public function collection()
    {
        $categoryId = $this->request->category_id;
        
        $productQuery = Product::with(['category', 'unit']);
        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }
        $products = $productQuery->get();

        $inventoryService = app(\App\Services\InventoryService::class);
        $reportData = collect();

        foreach ($products as $product) {
            $productId = $product->id;

            // Opening Balance
            $openingIncoming = InventoryBatch::where('product_id', $productId)
                ->where('date_in', '<', $this->periodStart)
                ->sum('qty_initial');

            $openingOutgoing = 0;
            $openingOutgoing += \App\Models\UsageRawMaterialItem::where('product_id', $productId)
                ->whereHas('usageRawMaterial', fn($q) => $q->where('tanggal', '<', $this->periodStart))
                ->sum('quantity');
            $openingOutgoing += \App\Models\UsageWipItem::where('product_id', $productId)
                ->whereHas('usageWip', fn($q) => $q->where('tanggal', '<', $this->periodStart))
                ->sum('quantity');
            $openingOutgoing += \App\Models\SalesFinishedGoodsItem::where('product_id', $productId)
                ->whereHas('salesFinishedGoods', fn($q) => $q->where('tanggal', '<', $this->periodStart))
                ->sum('quantity');
            $openingOutgoing += \App\Models\FinishedGoodsProductionMaterial::where('product_id', $productId)
                ->whereHas('finishedGoodsProduction', fn($q) => $q->where('tanggal', '<', $this->periodStart))
                ->sum('quantity');

            $openingBalance = $openingIncoming - $openingOutgoing;

            // Incoming
            $incomingBatches = InventoryBatch::where('product_id', $productId)
                ->whereBetween('date_in', [$this->periodStart, $this->periodEnd])
                ->get();
            $incomingQty = $incomingBatches->sum('qty_initial');
            $incomingValue = $incomingBatches->sum(fn($b) => $b->qty_initial * $b->price_per_unit);

            // Outgoing
            $outgoingQty = 0;
            $outgoingValueHPP = 0;
            $outgoingValueJual = 0;

            $usageItems = \App\Models\UsageRawMaterialItem::where('product_id', $productId)
                ->whereHas('usageRawMaterial', fn($q) => $q->whereBetween('tanggal', [$this->periodStart, $this->periodEnd]))
                ->get();
            foreach ($usageItems as $item) {
                $outgoingQty += $item->quantity;
                $outgoingValueHPP += $item->quantity * $item->harga;
                $outgoingValueJual += $item->quantity * $item->harga;
            }

            $wipUsageItems = \App\Models\UsageWipItem::where('product_id', $productId)
                ->whereHas('usageWip', fn($q) => $q->whereBetween('tanggal', [$this->periodStart, $this->periodEnd]))
                ->get();
            foreach ($wipUsageItems as $item) {
                $outgoingQty += $item->quantity;
                $outgoingValueHPP += $item->quantity * $item->harga;
                $outgoingValueJual += $item->quantity * $item->harga;
            }

            $salesItems = \App\Models\SalesFinishedGoodsItem::where('product_id', $productId)
                ->whereHas('salesFinishedGoods', fn($q) => $q->whereBetween('tanggal', [$this->periodStart, $this->periodEnd]))
                ->get();
            foreach ($salesItems as $item) {
                $outgoingQty += $item->quantity;
                $outgoingValueHPP += $item->total_cogs;
                $outgoingValueJual += $item->jumlah;
            }

            $prodMatItems = \App\Models\FinishedGoodsProductionMaterial::where('product_id', $productId)
                ->whereHas('finishedGoodsProduction', fn($q) => $q->whereBetween('tanggal', [$this->periodStart, $this->periodEnd]))
                ->get();
            foreach ($prodMatItems as $item) {
                $outgoingQty += $item->quantity;
                $outgoingValueHPP += $item->quantity * $item->cost_per_unit;
                $outgoingValueJual += $item->quantity * $item->cost_per_unit;
            }

            // Closing Balance
            $closingBalance = $openingBalance + $incomingQty - $outgoingQty;
            $avgPrice = $inventoryService->getAveragePrice($productId);
            $closingValue = $closingBalance * $avgPrice;

            // Skip products with no movement
            if ($openingBalance == 0 && $incomingQty == 0 && $outgoingQty == 0) {
                continue;
            }

            $reportData->push([
                'kode_barang' => $product->kode_barang,
                'nama_barang' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? '-',
                'satuan' => $product->unit->singkatan ?? '-',
                'saldo_awal' => max(0, $openingBalance),
                'masuk_qty' => $incomingQty,
                'masuk_nilai' => $incomingValue,
                'keluar_qty' => $outgoingQty,
                'keluar_nilai_hpp' => $outgoingValueHPP,
                'keluar_nilai_jual' => $outgoingValueJual,
                'saldo_akhir_qty' => max(0, $closingBalance),
                'saldo_akhir_nilai' => max(0, $closingValue),
            ]);
        }

        return $reportData;
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Saldo Awal (Qty)',
            'Masuk (Qty)',
            'Masuk (Nilai HPP)',
            'Keluar (Qty)',
            'Keluar (Nilai HPP)',
            'Keluar (Nilai Jual)',
            'Saldo Akhir (Qty)',
            'Saldo Akhir (Nilai)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A5568'],
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);

        // Number format for currency columns
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('G2:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I2:J' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }

    public function title(): string
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->isoFormat('MMMM YYYY');
    }
}
