<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataBarangExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
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

        // Filter by category
        if ($this->request->category_id) {
            $query->where('category_id', $this->request->category_id);
        }

        // Filter by search
        if ($this->request->search) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_barang')->get()->map(function ($product) {
            return [
                'tanggal_input' => $product->created_at->format('d/m/Y'),
                'kode_barang' => $product->kode_barang,
                'nama_barang' => $product->nama_barang,
                'kategori' => $product->category->nama_kategori ?? '-',
                'satuan' => $product->unit->singkatan ?? '-',
                'stok_saat_ini' => $product->current_stock ?? 0,
                'limit_stok' => $product->limit_stock,
                'harga_beli' => $product->harga_beli_default,
                'harga_jual' => $product->harga_jual_default,
                'nilai_aset' => ($product->current_stock ?? 0) * $product->harga_beli_default,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tgl Input',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Stok Saat Ini',
            'Limit Stok',
            'Harga Beli',
            'Harga Jual',
            'Nilai Aset',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Format currency columns (H, I, J)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("H2:J{$lastRow}")->getNumberFormat()
            ->setFormatCode('#,##0');

        return [];
    }

    public function title(): string
    {
        return 'Data Barang';
    }
}
