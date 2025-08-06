<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rencana Nutrisi - {{ \Carbon\Carbon::parse($plan->nutrition_plan_date)->translatedFormat('d F Y') }}</title>
    <style>
        body {
            font-family: 'Arial', Arial;
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
            margin-bottom: 8px;
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
            margin: 8px 0 8px 0;
            padding: 10px;
            border-radius: 8px;
        }

        .document-title h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-subtitle {
            font-size: 11px;
            margin: 0;
            opacity: 0.9;
        }

        /* Date Section */
        .date-section {
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .date-label {
            font-weight: bold;
            margin-right: 10px;
        }

        .date-value {
            color: #374151;
            font-weight: 600;
        }

        /* Table Styles */
        .table-section {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            background: white;
            font-size: 9px;
        }

        .table-header {
            background: #f8f9fa;
            border-bottom: 2px solid #000;
        }

        .table-header th {
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            color: #374151;
            border-right: 1px solid #00000;
            vertical-align: middle;
        }

        .table-header th:last-child {
            border-right: none;
        }

        tbody tr {
            border-bottom: 1px solid #00000;
        }

        tbody td {
            padding: 6px 4px;
            border-right: 1px solid #000;
            vertical-align: middle;
            text-align: center;
        }

        tbody td:last-child {
            border-right: none;
        }

        /* Column Specific Styles */
        .col-no {
            width: 3px;
            font-weight: 600;
            font-size: 10px;
        }

        .col-menu {
            width: 140px;
            text-align: left;
            font-weight: 500;
            font-size: 10px;
        }

        .col-recipient {
            width: 100px;
            text-align: left;
            font-weight: 500;
            font-size: 10px;
        }

        .col-netto,
        .col-energy,
        .col-protein,
        .col-fat,
        .col-carb,
        .col-fiber {
            width: 65px;
            font-family: 'Arial';
            font-size: 10px;
        }

        /* Summary Row Styles */
        .summary-row {
            /* font-weight: bold; */
        }

        .summary-row td {
            padding: 5px 2px !important;
            background: #e5e7eb !important;

        }

        .summary-label {
            color: #374151 !important;
            font-weight: bold !important;
            text-align: center !important;
            background: #e5e7eb !important;

        }

        .summary-total {
            color: #1f2937 !important;
            font-family: 'Arial';
            font-weight: bold !important;
            background: #e5e7eb !important;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
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
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 35px;
            text-transform: uppercase;
        }

        .signature-line {
            border-bottom: 1px solid #6b7280;
            margin: 0 20px 8px 20px;
        }

        .signature-name {
            font-size: 9px;
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
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            background: white;
        }

        /* Print Optimization */
        @media print {
            body {
                padding: 10px;
                padding-bottom: 50px;
            }

            .table-section {
                overflow-x: visible;
            }

            tbody tr:hover {
                background-color: inherit;
            }

            .footer {
                position: fixed;
                bottom: 10px;
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 800px) {
            table {
                font-size: 8px;
            }

            .col-menu {
                width: 120px;
            }

            .col-recipient {
                width: 80px;
            }

            .col-netto,
            .col-energy,
            .col-protein,
            .col-fat,
            .col-carb,
            .col-fiber {
                width: 55px;
            }
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
        <span
            class="date-value">{{ \Carbon\Carbon::parse($plan->nutrition_plan_date)->translatedFormat('d F Y') }}</span>
    </div>

    <!-- Items Table with Integrated Summary -->
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
                @php
                    $groupedItems = $plan->nutritionPlanItems->groupBy('target_group_id');
                    $itemCounter = 1;
                @endphp

                @foreach ($groupedItems as $groupId => $items)
                    @php
                        $groupName = $items->first()->targetGroup->name ?? 'Tidak diketahui';
                    @endphp

                    @foreach ($items as $item)
                        <tr>
                            <td class="col-no">{{ $itemCounter }}</td>
                            <td class="col-menu">{{ $item->menu->menu_name ?? '-' }}</td>
                            <td class="col-recipient">{{ $item->targetGroup->name ?? '-' }}</td>
                            <td class="col-netto">{{ number_format($item->netto, 1, ',', '.') }}</td>
                            <td class="col-energy">{{ number_format($item->energy, 1, ',', '.') }}</td>
                            <td class="col-protein">{{ number_format($item->protein, 1, ',', '.') }}</td>
                            <td class="col-fat">{{ number_format($item->fat, 1, ',', '.') }}</td>
                            <td class="col-carb">{{ number_format($item->carb, 1, ',', '.') }}</td>
                            <td class="col-fiber">{{ number_format($item->serat, 1, ',', '.') }}</td>
                        </tr>
                        @php $itemCounter++; @endphp
                    @endforeach

                    <!-- Summary Row for Current Group -->
                    <tr class="summary-row">
                        <td class="col-no"></td>
                        <td class="col-menu summary-label">Total</td>
                        <td class="col-recipient"></td>
                        <td class="col-netto summary-total">{{ number_format($items->sum('netto'), 1, ',', '.') }}</td>
                        <td class="col-energy summary-total">{{ number_format($items->sum('energy'), 1, ',', '.') }}
                        </td>
                        <td class="col-protein summary-total">{{ number_format($items->sum('protein'), 1, ',', '.') }}
                        </td>
                        <td class="col-fat summary-total">{{ number_format($items->sum('fat'), 1, ',', '.') }}</td>
                        <td class="col-carb summary-total">{{ number_format($items->sum('carb'), 1, ',', '.') }}</td>
                        <td class="col-fiber summary-total">{{ number_format($items->sum('serat'), 1, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

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
        <p>Dokumen ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </p>
    </div>
</body>

</html>
