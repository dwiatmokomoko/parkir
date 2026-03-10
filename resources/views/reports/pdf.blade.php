<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Parkir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
        }
        .metadata {
            margin-bottom: 20px;
            font-size: 12px;
        }
        .metadata-row {
            margin: 5px 0;
        }
        .summary {
            margin-bottom: 20px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 13px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
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
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🅓 DISHUB</div>
        <div class="title">Laporan Transaksi Pembayaran Parkir</div>
        <div class="subtitle">Dinas Perhubungan</div>
    </div>

    <div class="metadata">
        <div class="metadata-row">
            <strong>Periode:</strong> 
            {{ \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->format('d/m/Y') }} 
            s/d 
            {{ \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->format('d/m/Y') }}
        </div>
        @if(!empty($filters['street_section']))
            <div class="metadata-row">
                <strong>Lokasi:</strong> {{ $filters['street_section'] }}
            </div>
        @endif
        <div class="metadata-row">
            <strong>Tanggal Cetak:</strong> {{ $generatedAt }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Transaksi:</span>
            <span class="summary-value">{{ $transactionCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Transaksi Berhasil:</span>
            <span class="summary-value">{{ $successCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Transaksi Gagal:</span>
            <span class="summary-value">{{ $failedCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Transaksi Pending:</span>
            <span class="summary-value">{{ $pendingCount }}</span>
        </div>
        <div class="summary-row" style="border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
            <span class="summary-label">Total Pendapatan:</span>
            <span class="summary-value"><strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal/Jam</th>
                <th>Juru Parkir</th>
                <th>Lokasi</th>
                <th>Jenis Kendaraan</th>
                <th>Jumlah</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->parkingAttendant->name ?? '-' }}</td>
                    <td>{{ $transaction->street_section }}</td>
                    <td class="text-center">
                        @if($transaction->vehicle_type === 'motorcycle')
                            Motor
                        @elseif($transaction->vehicle_type === 'car')
                            Mobil
                        @else
                            {{ $transaction->vehicle_type }}
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($transaction->payment_status === 'success')
                            <span class="status-success">Berhasil</span>
                        @elseif($transaction->payment_status === 'failed')
                            <span class="status-failed">Gagal</span>
                        @elseif($transaction->payment_status === 'pending')
                            <span class="status-pending">Pending</span>
                        @else
                            {{ $transaction->payment_status }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Monitoring Pembayaran Parkir</p>
        <p>© Dinas Perhubungan - {{ now()->year }}</p>
    </div>
</body>
</html>
