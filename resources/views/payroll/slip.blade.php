{{-- resources/views/payroll/slip.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
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
            padding-bottom: 8px;
            margin-bottom: 12px;
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
            line-height: 1.3;
        }

        .header-content .title {
            margin-bottom: 5px;
            font-weight: 700;
            font-size: 12px;
        }

        .header-content .addr {
            font-size: 10px;
            color: #444;
        }

        .header-content .slip {
            margin-top: 6px;
            font-weight: 700;
        }

        .sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .label-sm {
            font-size: 10px;
        }

        .info-section,
        .calculation-section {
            margin-bottom: 14px;
        }

        .info-row {
            display: flex;
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            width: 115px;
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
            padding: 6px 0;
            text-align: left;
            border-bottom: 1px dotted #e5e7eb;
        }

        .calc-table th {
            font-weight: bold;
            background: #f8f8f8;
        }

        .amount {
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
            border-top: 1px solid #9ca3af;
            padding-top: 8px;
        }

        .note {
            font-size: 9px;
            color: #777;
            text-align: center;
            margin-top: 18px;
        }

        .signature {
            margin-top: 22px;
        }

        .signature table {
            width: 100%;
        }

        .center {
            text-align: center;
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
            }

            .logo {
                height: 25px;
            }
        }
    </style>
</head>

<body>
    @php
        // ----- SAFE CASTS -----
        $deptSalary = (int) ($department->salary ?? 0);
        $deptAllowance = (int) ($department->allowance ?? 0); // insentif kesehatan (flat)
        $deptBonus = (int) ($department->bonus ?? 0); // PJ (leader)
        $deptAbsDeduct = (int) ($department->absence_deduction ?? 0);
        $deptPermitAmt = (int) ($department->permit_amount ?? 0); // NOMINAL IZIN / HARI (fixed)

        $workDays = (int) ($payroll->work_days ?? 0);
        $permitDays = (int) ($payroll->permit ?? 0);
        $offDays = (int) ($payroll->off_day ?? 0);
        $absences = (int) ($payroll->absences ?? 0);
        $cashbon = (int) ($payroll->other ?? 0); // dipakai sbg kasbon (potongan) jika > 0

        // ----- COMPONENTS -----
        $gajiHarianTotal = $deptSalary * $workDays;
        $izinTotal = $deptPermitAmt * $permitDays; // IZIN = nominal tetap per hari
        $potAbsen = $deptAbsDeduct * $absences;

        // Pendapatan & Potongan (untuk tampilan breakdown)
        $totalPendapatan = $gajiHarianTotal + $izinTotal + $deptAllowance + $deptBonus;
        $totalPotongan = $potAbsen + max(0, $cashbon);

        // TOTAL untuk ditampilkan:
        // - jika manual -> pakai total_thp apa adanya (murni)
        // - jika otomatis -> pendapatan - potongan
        $isManualThp = (bool) ($payroll->is_manual_thp ?? false);
        $totalTampil = $isManualThp ? (int) ($payroll->total_thp ?? 0) : max(0, $totalPendapatan - $totalPotongan);

        // Periode & tanggal cetak
        $periodeText =
            \Carbon\Carbon::parse($payroll->start_date)->translatedFormat('d F Y') .
            ' - ' .
            \Carbon\Carbon::parse($payroll->end_date)->translatedFormat('d F Y');
        $tanggalCetak = isset($tanggal_cetak) ? $tanggal_cetak : now()->translatedFormat('d F Y');
    @endphp

    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/bgn.png') }}" alt="Logo" class="logo">
            <div class="header-content">
                <div class="title">SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</div>
                <div class="addr">{{ $app_address }}</div>
                <div class="slip">Slip Gaji Periode {{ $periodeText }}</div>
                {{-- <div class="sub label-sm">NOMOR : —</div> --}}
            </div>
        </div>

        {{-- Informasi dasar --}}
        <div class="info-section">
            <div class="info-row"><span class="info-label">Nama</span> <span class="info-value">:
                    {{ $employee->name }}</span></div>
            <div class="info-row"><span class="info-label">Jabatan</span> <span class="info-value">:
                    {{ $department->name }}</span></div>
            {{-- <div class="info-row"><span class="info-label">Keterangan</span> <span class="info-value">: Upah Harian
                    Relawan</span></div> --}}
        </div>

        {{-- Rincian perhitungan --}}
        <div class="calculation-section">
            <div class="center" style="font-weight:bold; margin-bottom:6px;">DENGAN RINCIAN SEBAGAI BERIKUT</div>

            <table class="calc-table">
                <thead>
                    <tr>
                        <th>RINCIAN</th>
                        <th class="amount">NOMINAL</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Pendapatan --}}
                    <tr>
                        <td>Jumlah Hari Masuk Kerja</td>
                        <td class="amount">{{ $workDays }}</td>
                    </tr>
                    <tr>
                        <td>Nominal Upah Harian</td>
                        <td class="amount">Rp {{ number_format($deptSalary, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Jumlah Pendapatan</td>
                        <td class="amount">Rp {{ number_format($gajiHarianTotal, 0, ',', '.') }}</td>
                    </tr>

                    @if ($permitDays > 0)
                        <tr>
                            <td>Izin ({{ $permitDays }} × Rp {{ number_format($deptPermitAmt, 0, ',', '.') }})</td>
                            <td class="amount">Rp {{ number_format($izinTotal, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @if ($deptAllowance > 0)
                        <tr>
                            <td>Tunjangan Kesehatan</td>
                            <td class="amount">Rp {{ number_format($deptAllowance, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @if ($deptBonus > 0)
                        <tr>
                            <td>PJ / Bonus</td>
                            <td class="amount">Rp {{ number_format($deptBonus, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    {{-- Potongan --}}
                    @if ($absences > 0)
                        <tr>
                            <td class="text-danger">Potongan Absen ({{ $absences }} × Rp
                                {{ number_format($deptAbsDeduct, 0, ',', '.') }})</td>
                            <td class="amount text-danger">- Rp {{ number_format($potAbsen, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @if ($cashbon > 0)
                        <tr>
                            <td class="text-danger">Kasbon</td>
                            <td class="amount text-danger">- Rp {{ number_format($cashbon, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    <tr class="total-row">
                        <td><strong>Total Gaji Diterima @if ($isManualThp)
                                    (Manual)
                                @endif
                            </strong></td>
                        <td class="amount text-success"><strong>Rp
                                {{ number_format($totalTampil, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Catatan --}}
        @if (!empty($payroll->note))
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Catatan</span>
                    <span class="info-value">: {{ $payroll->note }}</span>
                </div>
            </div>
        @endif

        {{-- Footer / tanda tangan --}}
        <div class="signature">
            <table>
                <tr>
                    <td class="center">{{ $app_city }}, {{ $tanggal_cetak }}</td>
                </tr>
                <tr>
                    <td>
                        <div style="float:left; width:50%; text-align:center; margin-top:28px;">
                            Staff Akuntan
                            <div style="height:50px;"></div>
                            <u>{{ $accountantName ?? '______________________' }}</u>
                        </div>
                        <div style="float:right; width:50%; text-align:center; margin-top:28px;">
                            Menerima
                            <div style="height:50px;"></div>
                            <u>{{ $employee->name }}</u>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="note">
            Slip ini dicetak otomatis pada {{ $tanggalCetak }}. Hubungi Admin jika ada pertanyaan.
        </div>
    </div>
</body>

</html>
