<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Status & Monitoring Stok</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #333;
            margin: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4F46E5;
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 9px;
            color: #666;
            margin: 3px 0;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-around;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 8px;
            color: #666;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #4F46E5;
        }
        .summary-value.danger {
            color: #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #4F46E5;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        th.right {
            text-align: right;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        td.right {
            text-align: right;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-low {
            color: #dc2626;
            font-weight: bold;
        }
        .status-safe {
            color: #059669;
        }
        .low-stock-row {
            background-color: #fef2f2 !important;
        }
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN STATUS & MONITORING STOK</h1>
        <p>Sae Bakery Inventory System | Dicetak: {{ $generatedAt }}</p>
    </div>

    <table style="margin-bottom: 15px; width: 100%;">
        <tr>
            <td style="width: 33%; text-align: center; background: #f0f9ff; padding: 8px; border: 1px solid #bfdbfe;">
                <div style="font-size: 8px; color: #666;">Total Produk</div>
                <div style="font-size: 14px; font-weight: bold; color: #4F46E5;">{{ $summary['totalProducts'] }}</div>
            </td>
            <td style="width: 33%; text-align: center; background: #fef2f2; padding: 8px; border: 1px solid #fecaca;">
                <div style="font-size: 8px; color: #666;">Stok Rendah</div>
                <div style="font-size: 14px; font-weight: bold; color: #dc2626;">{{ $summary['lowStockCount'] }}</div>
            </td>
            <td style="width: 33%; text-align: center; background: #f0fdf4; padding: 8px; border: 1px solid #bbf7d0;">
                <div style="font-size: 8px; color: #666;">Total Nilai Aset</div>
                <div style="font-size: 12px; font-weight: bold; color: #059669;">Rp {{ number_format($summary['totalValue'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th class="right">Stok</th>
                <th class="right">Limit</th>
                <th>Status</th>
                <th class="right">HPP/Unit</th>
                <th class="right">Nilai Aset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr class="{{ $product['status'] === 'Rendah' ? 'low-stock-row' : '' }}">
                    <td>{{ $product['kode_barang'] }}</td>
                    <td>{{ $product['nama_barang'] }}</td>
                    <td>{{ $product['kategori'] }}</td>
                    <td class="right">{{ number_format($product['stok'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($product['limit'], 0, ',', '.') }}</td>
                    <td class="{{ $product['status'] === 'Rendah' ? 'status-low' : 'status-safe' }}">
                        {{ $product['status'] }}
                    </td>
                    <td class="right">Rp {{ number_format($product['hpp'] ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['nilai'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                        Tidak ada data produk
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ $generatedAt }} | Sae Bakery Inventory System
    </div>
</body>
</html>
