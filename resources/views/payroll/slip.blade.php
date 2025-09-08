<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Slip Gaji - {{ $employee->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 10px;
        }

        .container {
            border: 1px solid #0a0a0a;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0, 0, 0, .05);
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

        .text-danger {
            color: #dc2626;
        }

        .text-success {
            color: #059669;
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
    @php
        // Siapkan angka agar konsisten di tabel
        $gajiHarian = (int) ($department->salary ?? 0) * (int) ($payroll->work_days ?? 0);
        $insentif = (int) ($department->allowance ?? 0);
        $bonus = (int) ($department->bonus ?? 0);
        $potAbs = (int) ($department->absence_deduction ?? 0) * (int) ($payroll->absences ?? 0);
        $other = (int) ($payroll->other ?? 0); // bisa positif (tambahan) atau negatif (potongan)
    @endphp

    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/bgn.png') }}" alt="Logo" class="logo">
            <div class="header-content">
                {{-- <h3>MBG</h3> --}}
                <p>SLIP GAJI KARYAWAN</p>
                <p>
                    Periode:
                    {{ \Carbon\Carbon::parse($payroll->start_date)->translatedFormat('d F Y') }}
                    -
                    {{ \Carbon\Carbon::parse($payroll->end_date)->translatedFormat('d F Y') }}
                </p>
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
            @if (($payroll->permit ?? 0) > 0)
                <div class="info-row">
                    <span class="info-label">Izin</span>
                    <span class="info-value">: {{ $payroll->permit }} hari</span>
                </div>
            @endif
            @if (($payroll->off_day ?? 0) > 0)
                <div class="info-row">
                    <span class="info-label">Libur</span>
                    <span class="info-value">: {{ $payroll->off_day }} hari</span>
                </div>
            @endif
        </div>

        <div class="calculation-section">
            <table class="calc-table">
                <thead>
                    <tr>
                        <th>RINCIAN</th>
                        <th class="amount">NOMINAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gaji Harian ({{ number_format($department->salary, 0, ',', '.') }} ×
                            {{ $payroll->work_days }} hari)</td>
                        <td class="amount">Rp {{ number_format($gajiHarian, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Insentif Bulanan</td>
                        <td class="amount">Rp {{ number_format($insentif, 0, ',', '.') }}</td>
                    </tr>
                    @if ($bonus > 0)
                        <tr>
                            <td>Bonus</td>
                            <td class="amount">Rp {{ number_format($bonus, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    {{-- Other / PJ (bisa positif = tambahan, negatif = potongan) --}}
                    @if ($other !== 0)
                        <tr>
                            <td>
                                Other / PJ
                                @if ($other < 0)
                                    <span class="text-danger">(Potongan)</span>
                                @else
                                    <span class="text-success">(Tambahan)</span>
                                @endif
                            </td>
                            <td class="amount {{ $other < 0 ? 'text-danger' : '' }}">
                                @if ($other < 0)
                                    -
                                @endif
                                Rp {{ number_format(abs($other), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td class="text-danger">
                            Potongan Absen ({{ number_format($department->absence_deduction, 0, ',', '.') }} ×
                            {{ $payroll->absences }} hari)
                        </td>
                        <td class="amount text-danger">- Rp {{ number_format($potAbs, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="total-row">
                        <td><strong>TOTAL TAKE HOME PAY</strong></td>
                        <td class="amount text-success">
                            <strong>Rp {{ number_format($payroll->total_thp, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if (!empty($payroll->note))
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Catatan</span>
                    <span class="info-value">: {{ $payroll->note }}</span>
                </div>
            </div>
        @endif

        <div class="note">
            Slip ini dicetak otomatis pada {{ $tanggal_cetak }}. Harap hubungi Admin jika ada pertanyaan.
        </div>
    </div>
</body>

</html>
