<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rencana Nutrisi - {{ \Carbon\Carbon::parse($plan->nutrition_plan_date)->translatedFormat('d F Y') }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 11px; 
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        /* Header/Kop Styles */
        .letterhead {
            border-bottom: 3px solid #1c5bad;
            padding-bottom: 15px;
            margin-bottom: 8px; /* Further reduced to make content closer */
        }
        
        .letterhead-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
        }
        
        .logo {
            width: 150px;
            height: auto;
        }
        
        .company-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-subtitle {
            font-size: 14px;
            color: #374151;
            margin: 0 0 3px 0;
        }
        
        .company-address {
            font-size: 10px;
            color: #6b7280;
            margin: 0;
            line-height: 1.3;
        }
        
        /* Document Title */
        .document-title {
            text-align: center;
            margin: 8px 0 8px 0; /* Further reduced for tighter spacing */
            padding: 10px; /* Further reduced */
            border-radius: 8px;
        }
        
        .document-title h1 {
            font-size: 16px; /* Reduced from 18px */
            font-weight: bold;
            margin: 0 0 6px 0; /* Reduced from 8px */
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .document-subtitle {
            font-size: 11px; /* Reduced from 12px */
            margin: 0;
            opacity: 0.9;
        }
        
        /* Date Section */
        .date-section {
            /* background: #f0fdf4;
            border: 1px solid #bbf7d0; */
            border-radius: 6px;
            padding: 6px 10px; /* Further reduced */
            margin-bottom: 10px; /* Further reduced to make table closer */
            display: inline-block;
        }
        
        .date-label {
            font-weight: bold;
            /* color: #4171c9; */
            margin-right: 10px;
        }
        
        .date-value {
            color: #374151;
            font-weight: 600;
        }
        
        /* Table Styles */
        .table-section {
            margin-bottom: 20px; /* Reduced from 25px */
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
            background: white;
            font-size: 9px; /* Reduced from inherited 11px */
        }
        
        .table-header {
            background: #f8f9fa;
            border-bottom: 2px solid #d1d5db;
        }
        
        .table-header th {
            padding: 8px 4px; /* Reduced from 10px 8px */
            text-align: center;
            font-weight: bold;
            font-size: 9px; /* Reduced from 10px */
            color: #374151;
            border-right: 1px solid #d1d5db;
            vertical-align: middle;
        }
        
        .table-header th:last-child {
            border-right: none;
        }
        
        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody td {
            padding: 6px 4px; /* Reduced from 8px */
            border-right: 1px solid #e5e7eb;
            vertical-align: middle;
            text-align: center;
        }
        
        tbody td:last-child {
            border-right: none;
        }
        
        /* Column Specific Styles - More compact and symmetric */
        .col-no { 
            width: 35px; /* Reduced from 40px */
            font-weight: 600;
        }
        
        .col-menu { 
            width: 140px; /* Reduced from 180px */
            text-align: left;
            font-weight: 500;
        }
        
        .col-recipient { 
            width: 100px; /* Reduced from 140px */
            text-align: left;
            font-weight: 500;
        }
        
        .col-netto,
        .col-energy,
        .col-protein,
        .col-fat,
        .col-carb,
        .col-fiber { 
            width: 65px; /* Reduced from 80px */
            font-family: 'Courier New', monospace;
            font-size: 8px; /* Reduced from 10px */
        }
        
        /* Summary Section */
        .summary-section {
            margin-top: 20px; /* Reduced from 30px */
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px; /* Reduced from 20px */
        }
        
        .summary-title {
            font-size: 11px; /* Reduced from 12px */
            font-weight: bold;
            color: #374151;
            margin-bottom: 12px; /* Reduced from 15px */
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px; /* Reduced from 5px */
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 8px; /* Reduced from 10px */
            border-right: 1px solid #d1d5db;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 9px; /* Reduced from 10px */
            color: #6b7280;
            margin-bottom: 4px; /* Reduced from 5px */
        }
        
        .summary-value {
            font-size: 12px; /* Reduced from 14px */
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #374151;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 30px; /* Reduced from 40px */
            display: table;
            width: 100%;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            padding-left: 20px;
        }
        
        .signature-box {
            text-align: center;
            padding: 15px; /* Reduced from 20px */
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
        }
        
        .signature-title {
            font-size: 10px; /* Reduced from 11px */
            font-weight: bold;
            color: #374151;
            margin-bottom: 35px; /* Reduced from 40px */
            text-transform: uppercase;
        }
        
        .signature-line {
            border-bottom: 1px solid #6b7280;
            margin: 0 20px 8px 20px;
        }
        
        .signature-name {
            font-size: 9px; /* Reduced from 10px */
            color: #6b7280;
            font-style: italic;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 15px;
            right: 15px;
            text-align: center;
            font-size: 8px; /* Reduced from 9px */
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            background: white;
        }
        
        /* Print Optimization */
        @media print {
            body { 
                padding: 10px; 
                padding-bottom: 50px; /* Add space for fixed footer */
            }
            .table-section { overflow-x: visible; }
            tbody tr:hover { background-color: inherit; }
            .footer {
                position: fixed;
                bottom: 10px;
            }
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 800px) {
            table { font-size: 8px; }
            .col-menu { width: 120px; }
            .col-recipient { width: 80px; }
            .col-netto,
            .col-energy,
            .col-protein,
            .col-fat,
            .col-carb,
            .col-fiber { width: 55px; }
        }
    </style>
</head>
<body>
    <!-- Letterhead/Kop -->
    <div class="letterhead">
        <div class="letterhead-content">
            <div class="logo-section">
                <img src="{{ public_path('images/bgn.png') }}" class="logo" alt="Logo BGN">
            </div>
            {{-- <div class="company-info">
                <h1 class="company-name">Badan Gizi Nasional</h1>
                <h2 class="company-subtitle">Kementerian Kesehatan Republik Indonesia</h2>
                <p class="company-address">
                    Jl. HR. Rasuna Said Blok X-5 Kav. 4-9, Kuningan, Jakarta Selatan 12950<br>
                    Telepon: (021) 5201590 | Email: info@giznas.kemkes.go.id
                </p>
            </div> --}}
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>Rencana Nutrisi</h1>
        {{-- <p class="document-subtitle">Perencanaan Asupan Gizi Harian</p> --}}
    </div>

    <!-- Date Section -->
    <div class="date-section">
        <span class="date-label">Tanggal Rencana:</span>
        <span class="date-value">{{ \Carbon\Carbon::parse($plan->nutrition_plan_date)->translatedFormat('d F Y') }}</span>
    </div>

    <!-- Items Table -->
    <div class="table-section">
        <table>
            <thead class="table-header">
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-menu">MENU</th>
                    <th class="col-recipient">PENERIMA</th>
                    <th class="col-netto">NETTO (GR)</th>
                    <th class="col-energy">ENERGI (KKAL)</th>
                    <th class="col-protein">PROTEIN (GR)</th>
                    <th class="col-fat">LEMAK (GR)</th>
                    <th class="col-carb">KARBO (GR)</th>
                    <th class="col-fiber">SERAT (GR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->nutritionPlanItems as $index => $item)
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-menu">{{ $item->menu->menu_name ?? '-' }}</td>
                        <td class="col-recipient">{{ $item->targetGroup->name ?? '-' }}</td>
                        <td class="col-netto">{{ number_format($item->netto, 1, ',', '.') }}</td>
                        <td class="col-energy">{{ number_format($item->energy, 1, ',', '.') }}</td>
                        <td class="col-protein">{{ number_format($item->protein, 1, ',', '.') }}</td>
                        <td class="col-fat">{{ number_format($item->fat, 1, ',', '.') }}</td>
                        <td class="col-carb">{{ number_format($item->carb, 1, ',', '.') }}</td>
                        <td class="col-fiber">{{ number_format($item->serat, 1, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->

@php
$groupedItems = $plan->nutritionPlanItems->groupBy('target_group_id');
@endphp

@foreach($groupedItems as $groupId => $items)
@php
    $groupName = $items->first()->targetGroup->name ?? 'Tidak diketahui';
@endphp

<div class="summary-section">
    <div class="summary-title">Ringkasan Nutrisi: {{ $groupName }}</div>
    <div class="summary-grid">
        {{-- <div class="summary-item">
            <div class="summary-label">Total Item</div>
            <div class="summary-value">{{ $items->count() }}</div>
        </div> --}}
        <div class="summary-item">
            <div class="summary-label">Total Energi (kkal)</div>
            <div class="summary-value">{{ number_format($items->sum('energy'), 1, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Protein (gr)</div>
            <div class="summary-value">{{ number_format($items->sum('protein'), 1, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Lemak (gr)</div>
            <div class="summary-value">{{ number_format($items->sum('fat'), 1, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Karbo (gr)</div>
            <div class="summary-value">{{ number_format($items->sum('carb'), 1, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Serat (gr)</div>
            <div class="summary-value">{{ number_format($items->sum('serat'), 1, ',', '.') }}</div>
        </div>
    </div>
</div>
@endforeach

    
    {{-- <div class="summary-section">
        <div class="summary-title">Ringkasan Total Nutrisi</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Item</div>
                <div class="summary-value">{{ $plan->nutritionPlanItems->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Energi (kkal)</div>
                <div class="summary-value">{{ number_format($plan->nutritionPlanItems->sum('energy'), 1, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Protein (gr)</div>
                <div class="summary-value">{{ number_format($plan->nutritionPlanItems->sum('protein'), 1, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Lemak (gr)</div>
                <div class="summary-value">{{ number_format($plan->nutritionPlanItems->sum('fat'), 1, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Karbo (gr)</div>
                <div class="summary-value">{{ number_format($plan->nutritionPlanItems->sum('carb'), 1, ',', '.') }}</div>
            </div>
        </div>
    </div> --}}

    <!-- Signature Section -->
    {{-- <div class="signature-section">
        <div class="signature-left">
            <div class="signature-box">
                <div class="signature-title">Disusun Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name">Ahli Gizi</div>
            </div>
        </div>
        
        <div class="signature-right">
            <div class="signature-box">
                <div class="signature-title">Mengetahui & Menyetujui</div>
                <div class="signature-line"></div>
                <div class="signature-name">Kepala Bidang Gizi</div>
            </div>
        </div>
    </div> --}}

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>