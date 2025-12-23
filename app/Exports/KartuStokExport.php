<?php

namespace App\Exports;

use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KartuStokExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected Request $request;
    protected ?Product $product = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
        if ($request->product_id) {
            $this->product = Product::with(['category', 'unit'])->find($request->product_id);
        }
    }

    public function collection()
    {
        if (!$this->product) {
            return collect([]);
        }

        $query = InventoryBatch::where('product_id', $this->product->id)
            ->orderBy('date_in', 'asc');

        // Date filter
        if ($this->request->start_date) {
            $query->where('date_in', '>=', $this->request->start_date);
        }
        if ($this->request->end_date) {
            $query->where('date_in', '<=', $this->request->end_date);
        }

        $batches = $query->get();
        $runningBalance = 0;

        return $batches->map(function ($batch) use (&$runningBalance) {
            $masuk = $batch->qty_initial;
            $keluar = $batch->qty_initial - $batch->qty_current;
            $runningBalance += ($masuk - $keluar);

            return [
                'tanggal' => $batch->date_in?->format('d/m/Y') ?? '-',
                'keterangan' => $this->getSourceLabel($batch->source) . ' - ' . $batch->batch_no,
                'masuk' => $masuk > 0 ? $masuk : '',
                'keluar' => $keluar > 0 ? $keluar : '',
                'saldo' => $runningBalance,
                'harga' => $batch->price_per_unit,
            ];
        });
    }

    public function headings(): array
    {
        $productInfo = $this->product 
            ? $this->product->nama_barang . ' (' . $this->product->kode_barang . ')'
            : 'Tidak ada produk dipilih';

        return [
            'Tanggal',
            'Keterangan',
            'Masuk',
            'Keluar',
            'Saldo',
            'Harga Satuan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add product info as title
        if ($this->product) {
            $sheet->insertNewRowBefore(1, 2);
            $sheet->setCellValue('A1', 'KARTU STOK: ' . $this->product->nama_barang);
            $sheet->setCellValue('A2', 'Kode: ' . $this->product->kode_barang . ' | Kategori: ' . ($this->product->category->nama_kategori ?? '-'));
            $sheet->mergeCells('A1:F1');
            $sheet->mergeCells('A2:F2');
        }

        // Style header row (now row 3)
        $headerRow = $this->product ? 3 : 1;
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Title style
        if ($this->product) {
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
            ]);
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 10],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return $this->product ? substr($this->product->nama_barang, 0, 31) : 'Kartu Stok';
    }

    private function getSourceLabel(string $source): string
    {
        return match ($source) {
            'purchase' => 'Pembelian',
            'opening_balance' => 'Saldo Awal',
            'production_result' => 'Hasil Produksi',
            'adjustment' => 'Penyesuaian',
            default => ucfirst($source),
        };
    }
}
