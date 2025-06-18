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
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 0;
        }
        
        .slip-title {
            font-size: 18px;
            color: #374151;
            margin: 10px 0 0 0;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-column {
            width: 48%;
        }
        
        .info-title {
            font-weight: bold;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .calculation-section {
            margin-top: 30px;
        }
        
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .calc-table th,
        .calc-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .calc-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .calc-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 16px;
        }
        
        .total-row td {
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }
        
        .note {
            margin-top: 40px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="company-name">{{ config('app.name') }}</h1>
        <p class="slip-title">SLIP GAJI KARYAWAN</p>
    </div>

    <div class="info-section">
        <div class="info-column">
            <div class="info-title">INFORMASI KARYAWAN</div>
            <div class="info-item">
                <span class="info-label">Nama</span>
                <span>: {{ $employee->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Departemen</span>
                <span>: {{ $department->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Periode</span>
                <span>: {{ $periode }}</span>
            </div>
        </div>
        
        <div class="info-column">
            <div class="info-title">INFORMASI KEHADIRAN</div>
            <div class="info-item">
                <span class="info-label">Hari Kerja</span>
                <span>: {{ $payroll->work_days }} hari</span>
            </div>
            <div class="info-item">
                <span class="info-label">Absen</span>
                <span>: {{ $payroll->absences }} hari</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Cetak</span>
                <span>: {{ $tanggal_cetak }}</span>
            </div>
        </div>
    </div>

    <div class="calculation-section">
        <table class="calc-table">
            <thead>
                <tr>
                    <th>KOMPONEN GAJI</th>
                    <th style="text-align: right;">NOMINAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Harian ({{ number_format($department->salary, 0, ',', '.') }} × {{ $payroll->work_days }} hari)</td>
                    <td class="amount">Rp {{ number_format($department->salary * $payroll->work_days, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Insentif Bulanan</td>
                    <td class="amount">Rp {{ number_format($department->allowance, 0, ',', '.') }}</td>
                </tr>
                @if($department->bonus > 0)
                <tr>
                    <td>Bonus</td>
                    <td class="amount">Rp {{ number_format($department->bonus, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="color: #dc2626;">Potongan Absen ({{ number_format($department->absence_deduction, 0, ',', '.') }} × {{ $payroll->absences }} hari)</td>
                    <td class="amount" style="color: #dc2626;">- Rp {{ number_format($department->absence_deduction * $payroll->absences, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL TAKE HOME PAY (THP)</strong></td>
                    <td class="amount" style="color: #059669;"><strong>Rp {{ number_format($payroll->total_thp, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="note">
        <strong>Catatan:</strong> Slip gaji ini dicetak secara otomatis oleh sistem. Untuk pertanyaan terkait gaji, silakan hubungi bagian Keuangan.
    </div>
</body>
</html>