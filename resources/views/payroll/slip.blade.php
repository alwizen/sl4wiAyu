<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $employee->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000000;
            font-size: 10px;
        }

        .container {
            border: 1px solid #0a0a0a;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .header {
            border-bottom: 1px solid #0d80dd;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            height: 37px;
            width: auto;
            max-width: 120px;
        }

        .header-content {
            text-align: center;
        }

        .header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .header p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #666;
        }

        .info-section,
        .calculation-section {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            width: 100px;
            flex-shrink: 0;
        }

        .info-value {
            flex-grow: 1;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calc-table th,
        .calc-table td {
            padding: 8px 0;
            text-align: left;
            border-bottom: 1px dotted #eee;
        }

        .calc-table th {
            font-weight: bold;
            background-color: #f8f8f8;
        }

        .calc-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row td {
            font-weight: bold;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .note {
            font-size: 9px;
            color: #777;
            text-align: center;
            margin-top: 20px;
        }

        /* Print specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                width: 190mm;
                margin: 10mm auto;
                border: none;
                box-shadow: none;
                padding: 0;
                box-sizing: border-box;
            }
            .logo {
                height: 25px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/bgn.png') }}" alt="Logo" class="logo">
            <div class="header-content">
                <h3>MBG</h3>
                <p>SLIP GAJI KARYAWAN</p>
                <p>Periode: {{ \Carbon\Carbon::parse($payroll->start_date)->translatedFormat('d F Y') }} -
                    {{ \Carbon\Carbon::parse($payroll->end_date)->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value">: {{ $employee->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Departemen</span>
                <span class="info-value">: {{ $department->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Hari Kerja</span>
                <span class="info-value">: {{ $payroll->work_days }} hari</span>
            </div>
            <div class="info-row">
                <span class="info-label">Absen</span>
                <span class="info-value">: {{ $payroll->absences }} hari</span>
            </div>
            @if ($payroll->permit > 0)
                <div class="info-row">
                    <span class="info-label">Izin</span>
                    <span class="info-value">: {{ $payroll->permit }} hari</span>
                </div>
            @endif
        </div>

        <div class="calculation-section">
            <table class="calc-table">
                <thead>
                    <tr>
                        <th>KOMPONEN</th>
                        <th class="amount">NOMINAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gaji Harian ({{ number_format($department->salary, 0, ',', '.') }} ×
                            {{ $payroll->work_days }} hari)</td>
                        <td class="amount">Rp
                            {{ number_format($department->salary * $payroll->work_days, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Insentif Bulanan</td>
                        <td class="amount">Rp {{ number_format($department->allowance, 0, ',', '.') }}</td>
                    </tr>
                    @if ($department->bonus > 0)
                        <tr>
                            <td>Bonus</td>
                            <td class="amount">Rp {{ number_format($department->bonus, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="color: #dc2626;">Potongan Absen
                            ({{ number_format($department->absence_deduction, 0, ',', '.') }} ×
                            {{ $payroll->absences }} hari)</td>
                        <td class="amount" style="color: #dc2626;">- Rp
                            {{ number_format($department->absence_deduction * $payroll->absences, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>TOTAL TAKE HOME PAY</strong></td>
                        <td class="amount" style="color: #059669;"><strong>Rp
                                {{ number_format($payroll->total_thp, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="note">
            Slip ini dicetak otomatis pada {{ $tanggal_cetak }}. Harap hubungi Admin jika ada pertanyaan.
        </div>
    </div>
</body>

</html>