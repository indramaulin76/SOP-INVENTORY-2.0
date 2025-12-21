<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Stok - Sae Bakery</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #C8A67D;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #C8A67D;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }
        
        .report-period {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background-color: #C8A67D;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge-in {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .badge-out {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .generated-at {
            font-style: italic;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">🥐 SAE BAKERY</div>
            <div class="company-tagline">Roti & Kue Berkualitas</div>
            <div class="report-title">LAPORAN RIWAYAT TRANSAKSI STOK</div>
            <div class="report-period">
                @if($startDate && $endDate)
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                @else
                    Semua Periode
                @endif
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">Tanggal</th>
                    <th style="width: 80px;">Kode</th>
                    <th>Nama Barang</th>
                    <th style="width: 70px;">Kategori</th>
                    <th style="width: 80px;">Jenis</th>
                    <th style="width: 50px;" class="text-right">Masuk</th>
                    <th style="width: 50px;" class="text-right">Keluar</th>
                    <th style="width: 40px;">Sat.</th>
                    <th style="width: 80px;" class="text-right">Harga</th>
                    <th style="width: 80px;">No. Ref</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement['tanggal'] }}</td>
                    <td>{{ $movement['kode_barang'] }}</td>
                    <td>{{ $movement['nama_barang'] }}</td>
                    <td>{{ $movement['kategori'] }}</td>
                    <td>
                        @if($movement['masuk'] > 0)
                            <span class="badge-in">{{ $movement['jenis_transaksi'] }}</span>
                        @else
                            <span class="badge-out">{{ $movement['jenis_transaksi'] }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $movement['masuk'] > 0 ? number_format($movement['masuk'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $movement['keluar'] > 0 ? number_format($movement['keluar'], 0, ',', '.') : '-' }}</td>
                    <td class="text-center">{{ $movement['satuan'] }}</td>
                    <td class="text-right">Rp {{ number_format($movement['harga_satuan'], 0, ',', '.') }}</td>
                    <td>{{ Str::limit($movement['no_referensi'], 12) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Total: {{ count($movements) }} transaksi</p>
            <p class="generated-at">Dicetak pada: {{ $generatedAt }}</p>
        </div>
    </div>
</body>
</html>
