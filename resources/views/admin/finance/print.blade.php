<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Wilayah - Pijat.In</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            padding: 24px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #065f46;
        }
        .summary-box {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .summary-item {
            flex: 1;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .summary-label {
            font-size: 11px;
            color: #64748b;
        }
        .summary-value {
            font-size: 15px;
            font-weight: bold;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-credit {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-debit {
            background: #f1f5f9;
            color: #334155;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #059669; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="header">
        <div>
            <div class="title">Laporan Arus Kas & Keuangan Wilayah</div>
            <div style="color: #64748b; margin-top: 2px;">Wilayah: <strong>{{ $regionName }}</strong></div>
        </div>
        <div style="text-align: right; color: #64748b; font-size: 11px;">
            Tanggal Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
            Aplikasi Pijat.In
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Arus Masuk (Top-up & Komisi)</div>
            <div class="summary-value" style="color: #059669;">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Arus Keluar (Beban & Pengeluaran)</div>
            <div class="summary-value" style="color: #dc2626;">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Saldo Bersih Arus Kas</div>
            <div class="summary-value">Rp {{ number_format($totalInflow - $totalOutflow, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 120px;">Waktu</th>
                <th>Wilayah</th>
                <th>Pengguna</th>
                <th>Tipe</th>
                <th class="text-right">Nominal</th>
                <th class="text-right">Saldo Sesudah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $tx)
                <tr>
                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $tx->region?->name ?? 'Pusat' }}</td>
                    <td>{{ $tx->user?->name ?? 'Sistem' }}</td>
                    <td>
                        <span class="badge {{ $tx->isCredit() ? 'badge-credit' : 'badge-debit' }}">
                            {{ strtoupper($tx->type) }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight: bold; {{ $tx->isCredit() ? 'color: #059669;' : '' }}">
                        {{ $tx->isCredit() ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                    <td>{{ $tx->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

