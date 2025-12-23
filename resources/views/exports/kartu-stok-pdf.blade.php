<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kartu Stok - {{ $product->nama_barang ?? 'Unknown' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4F46E5;
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        .header h2 {
            font-size: 14px;
            margin: 0 0 5px 0;
            font-weight: normal;
        }
        .header p {
            font-size: 10px;
            color: #666;
            margin: 3px 0;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 3px 5px;
        }
        .info-box .label {
            font-weight: bold;
            width: 120px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table.data th {
            background-color: #4F46E5;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #4F46E5;
        }
        table.data th.right {
            text-align: right;
        }
        table.data td {
            padding: 6px 5px;
            border: 1px solid #ddd;
        }
        table.data td.right {
            text-align: right;
        }
        table.data tr:nth-child(even) {
            background-color: #f9fafb;
        }
        table.data tr:hover {
            background-color: #f3f4f6;
        }
        .masuk {
            color: #059669;
            font-weight: bold;
        }
        .keluar {
            color: #dc2626;
            font-weight: bold;
        }
        .saldo {
            font-weight: bold;
            background-color: #f0f9ff !important;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #888;
        }
        .summary {
            margin-top: 15px;
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 10px;
        }
        .summary table {
            width: 100%;
        }
        .summary td {
            padding: 5px;
        }
        .summary .value {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARTU STOK</h1>
        <h2>{{ $product->nama_barang ?? 'Unknown Product' }}</h2>
        <p>Kode: {{ $product->kode_barang ?? '-' }} | Kategori: {{ $product->category->nama_kategori ?? '-' }} | Satuan: {{ $product->unit->singkatan ?? '-' }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td class="label">Periode:</td>
                <td>{{ $startDate ?? 'Semua' }} s/d {{ $endDate ?? 'Sekarang' }}</td>
                <td class="label">Dicetak:</td>
                <td>{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="right">Masuk</th>
                <th class="right">Keluar</th>
                <th class="right">Saldo</th>
                <th class="right">Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = 0; @endphp
            @forelse($movements as $movement)
                @php 
                    $masuk = $movement['masuk'];
                    $keluar = $movement['keluar'];
                    $runningBalance += ($masuk - $keluar);
                @endphp
                <tr>
                    <td>{{ $movement['tanggal'] }}</td>
                    <td>{{ $movement['keterangan'] }}</td>
                    <td class="right">
                        @if($masuk > 0)
                            <span class="masuk">+{{ number_format($masuk, 0, ',', '.') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="right">
                        @if($keluar > 0)
                            <span class="keluar">-{{ number_format($keluar, 0, ',', '.') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="right saldo">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($movement['harga'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #888;">
                        Tidak ada data mutasi stok
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td style="width: 33%;">Total Masuk: <strong class="masuk">{{ number_format($totalMasuk, 0, ',', '.') }}</strong></td>
                <td style="width: 33%;">Total Keluar: <strong class="keluar">{{ number_format($totalKeluar, 0, ',', '.') }}</strong></td>
                <td style="width: 33%;">Saldo Akhir: <strong>{{ number_format($saldoAkhir, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ $generatedAt }} | Sae Bakery Inventory System
    </div>
</body>
</html>
