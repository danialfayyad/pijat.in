<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Lintas Wilayah - Pijat.In</title>
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
            <div class="title">Laporan Konsolidasi Finansial Lintas Wilayah</div>
            <div style="color: #64748b; margin-top: 2px;">
                Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
            </div>
        </div>
        <div style="text-align: right; color: #64748b; font-size: 11px;">
            Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
            Kantor Pusat Pijat.In
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Arus Masuk (Top-up & Komisi)</div>
            <div class="summary-value" style="color: #059669;">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Arus Keluar (Beban & Pencairan)</div>
            <div class="summary-value" style="color: #dc2626;">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Komisi Platform</div>
            <div class="summary-value" style="color: #047857;">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Arus Kas Bersih (Net)</div>
            <div class="summary-value">Rp {{ number_format($totalInflow - $totalOutflow, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Wilayah Operasional</th>
                <th class="text-right">Arus Masuk</th>
                <th class="text-right">Arus Keluar</th>
                <th class="text-right">Net Kas</th>
                <th class="text-right">Komisi Platform</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summaryPerRegion as $item)
                <tr>
                    <td style="font-weight: bold;">{{ $item['region']->name }}</td>
                    <td class="text-right" style="color: #059669;">Rp {{ number_format($item['inflow'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['outflow'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($item['net'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #065f46;">Rp {{ number_format($item['commission'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

