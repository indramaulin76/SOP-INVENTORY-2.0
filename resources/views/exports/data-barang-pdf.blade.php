<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Data Barang</title>
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
            border-bottom: 2px solid #ca8a04;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #ca8a04;
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 9px;
            color: #666;
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #ca8a04;
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
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            color: #888;
        }
        .summary {
            margin-top: 10px;
            background: #fef9c3;
            border: 1px solid #fde047;
            padding: 8px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA BARANG</h1>
        <p>Sae Bakery Inventory System | Dicetak: {{ $generatedAt }}</p>
        @if($categoryName)
            <p>Kategori: {{ $categoryName }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Tgl Input</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th class="right">Kuantitas</th>
                <th class="right">Harga Beli</th>
                <th class="right">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product['tgl_input'] }}</td>
                    <td>{{ $product['kode_barang'] }}</td>
                    <td>{{ $product['nama_barang'] }}</td>
                    <td>{{ $product['kategori'] }}</td>
                    <td>{{ $product['satuan'] }}</td>
                    <td class="right">{{ number_format($product['kuantitas'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['harga_beli'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['harga_jual'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                        Tidak ada data barang
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Total: {{ count($products) }} items</strong>
    </div>

    <div class="footer">
        Dicetak pada {{ $generatedAt }} | Sae Bakery Inventory System
    </div>
</body>
</html>
