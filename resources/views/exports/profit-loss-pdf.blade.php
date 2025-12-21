<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan & Laba - Sae Bakery</title>
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
            padding: 25px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
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
        
        /* Summary Cards */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        
        .summary-card.primary {
            background-color: #C8A67D;
            color: white;
        }
        
        .summary-card.success {
            background-color: #28a745;
            color: white;
        }
        
        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
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
            padding: 10px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        td {
            padding: 8px;
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
        
        .profit-positive {
            color: #28a745;
            font-weight: bold;
        }
        
        .profit-negative {
            color: #dc3545;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">🥐 SAE BAKERY</div>
            <div class="company-tagline">Roti & Kue Berkualitas</div>
            <div class="report-title">LAPORAN ANALISIS PENJUALAN & LABA</div>
            <div class="report-period">
                @if($startDate && $endDate)
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                @else
                    Semua Periode
                @endif
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-card primary">
                <div class="summary-label">Total Penjualan</div>
                <div class="summary-value">Rp {{ number_format($summary['totalPenjualan'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total HPP</div>
                <div class="summary-value">Rp {{ number_format($summary['totalHPP'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card success">
                <div class="summary-label">Total Laba Kotor</div>
                <div class="summary-value">Rp {{ number_format($summary['totalProfit'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Margin Laba</div>
                <div class="summary-value">{{ $summary['marginPercentage'] }}%</div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">Tanggal</th>
                    <th style="width: 80px;">No. Bukti</th>
                    <th style="width: 100px;">Customer</th>
                    <th>Nama Barang</th>
                    <th style="width: 40px;" class="text-center">Qty</th>
                    <th style="width: 80px;" class="text-right">Harga</th>
                    <th style="width: 90px;" class="text-right">Subtotal</th>
                    <th style="width: 80px;" class="text-right">HPP</th>
                    <th style="width: 80px;" class="text-right">Laba</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesData as $item)
                <tr>
                    <td>{{ $item['tanggal'] }}</td>
                    <td>{{ $item['nomor_bukti'] }}</td>
                    <td>{{ Str::limit($item['customer'], 15) }}</td>
                    <td>{{ $item['nama_barang'] }}</td>
                    <td class="text-center">{{ number_format($item['qty'], 0) }}</td>
                    <td class="text-right">Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['hpp'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $item['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rp {{ number_format($item['profit'], 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data penjualan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Total: {{ count($salesData) }} item penjualan</p>
            <p class="generated-at">Dicetak pada: {{ $generatedAt }}</p>
        </div>
    </div>
</body>
</html>
