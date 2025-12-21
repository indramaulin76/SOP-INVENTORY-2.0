<?php

namespace App\Exports;

use App\Models\SalesFinishedGoods;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $startDate = $this->request->start_date ? \Carbon\Carbon::parse($this->request->start_date)->startOfDay() : null;
        $endDate = $this->request->end_date ? \Carbon\Carbon::parse($this->request->end_date)->endOfDay() : null;

        $query = SalesFinishedGoods::with(['items.product', 'customer']);
        if ($startDate) $query->where('tanggal', '>=', $startDate);
        if ($endDate) $query->where('tanggal', '<=', $endDate);

        $sales = $query->orderBy('tanggal', 'desc')->get();

        $data = collect();

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $subtotal = $item->quantity * $item->harga_jual;
                $hpp = $item->total_cogs ?? 0;
                $profit = $subtotal - $hpp;

                $data->push([
                    'tanggal' => $sale->tanggal->format('d/m/Y'),
                    'nomor_bukti' => $sale->nomor_bukti,
                    'customer' => $sale->customer->nama_customer ?? 'Umum',
                    'nama_barang' => $item->product->nama_barang ?? '-',
                    'qty' => $item->quantity,
                    'harga_jual' => $item->harga_jual,
                    'subtotal' => $subtotal,
                    'hpp' => $hpp,
                    'profit' => $profit,
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Bukti',
            'Customer',
            'Nama Barang',
            'Qty',
            'Harga Jual',
            'Subtotal',
            'HPP',
            'Profit',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
