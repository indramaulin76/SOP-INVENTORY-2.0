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

class StatusBarangExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Product::with(['category', 'unit'])
            ->withSum('inventoryBatches as current_stock', 'qty_current');

        // Apply filters
        if ($this->request->search) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        if ($this->request->category_id) {
            $query->where('category_id', $this->request->category_id);
        }

        $products = $query->orderBy('nama_barang')->get();

        // Filter by status if needed
        if ($this->request->status) {
            $products = $products->filter(function ($product) {
                $currentStock = $product->current_stock ?? 0;
                $isLow = $currentStock <= $product->limit_stock;
                
                if ($this->request->status === 'low') {
                    return $isLow;
                }
                return !$isLow;
            });
        }

        return $products->map(function ($product) {
            $currentStock = $product->current_stock ?? 0;
            $status = $currentStock <= $product->limit_stock ? 'Stok Rendah' : 'Stok Aman';
            
            // Calculate average price
            $avgPrice = InventoryBatch::where('product_id', $product->id)
                ->where('qty_current', '>', 0)
                ->avg('price_per_unit') ?? 0;
            
            $totalValue = $currentStock * $avgPrice;

            return [
                'kode_barang' => $product->kode_barang,
                'nama_barang' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? '-',
                'satuan' => $product->unit->singkatan ?? '-',
                'stok' => $currentStock,
                'limit_stok' => $product->limit_stock,
                'status' => $status,
                'hpp_satuan' => $avgPrice,
                'nilai_aset' => $totalValue,
            ];
        })->values();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Stok',
            'Limit Stok',
            'Status',
            'HPP/Satuan',
            'Nilai Aset',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add title
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'LAPORAN STATUS & MONITORING STOK');
        $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        // Style title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
        ]);

        // Style header row (now row 3)
        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Status Stok';
    }
}
