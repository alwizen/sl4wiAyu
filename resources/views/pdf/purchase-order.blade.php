<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan - {{ $purchaseOrder->order_number }}</title>
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
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 25px;
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
            width: 180px;
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
            color: #1e40af;
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
            margin: 30px 0 25px 0;
            padding: 15px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
        }

        .document-title h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .order-number {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
            margin: 0;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 25px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .info-column:last-child {
            padding-right: 0;
            padding-left: 20px;
        }

        .info-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
        }

        .info-block h3 {
            margin: 0 0 12px 0;
            color: #1e40af;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5px;
        }

        .info-item {
            margin-bottom: 6px;
            display: table;
            width: 100%;
        }

        .info-label {
            display: table-cell;
            font-weight: 600;
            color: #374151;
            width: 70px;
            padding-right: 10px;
        }

        .info-colon {
            display: table-cell;
            width: 10px;
        }

        .info-value {
            display: table-cell;
            color: #111827;
        }

        /* Table Styles */
        .table-section {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #374151;
            border-radius: 8px;
            overflow: hidden;
        }

        .table-header {
            background: #374151;
            color: white;
        }

        .table-header th {
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            border-right: 1px solid #6b7280;
        }

        .table-header th:last-child {
            border-right: none;
        }

        tbody tr {
            border-bottom: 1px solid #d1d5db;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody td {
            padding: 10px 8px;
            border-right: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tbody td:last-child {
            border-right: none;
        }

        .col-no {
            width: 40px;
            text-align: center;
            font-weight: 600;
        }

        .col-item {
            text-align: left;
        }

        .col-qty {
            width: 80px;
            text-align: center;
            font-weight: 600;
        }

        .col-unit {
            width: 120px;
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .col-total {
            width: 140px;
            text-align: right;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        /* Total Section */
        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            min-width: 250px;
        }

        .total-label {
            font-size: 12px;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
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
            padding: 20px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
        }

        .signature-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .signature-line {
            border-bottom: 1px solid #6b7280;
            margin: 0 20px 8px 20px;
        }

        .signature-name {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
        }

        /* Print Optimization */
        @media print {
            body {
                padding: 10px;
            }

            .total-box {
                box-shadow: none;
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
            <div class="company-info">
                {{-- <h1 class="company-name">Badan Gizi Nasional</h1> --}}
                <h2 class="company-subtitle">Kementerian Kesehatan Republik Indonesia</h2>
                <p class="company-address">
                    Jl. HR. Rasuna Said Blok X-5 Kav. 4-9, Kuningan, Jakarta Selatan 12950<br>
                    Telepon: (021) 5201590 | Email: info@giznas.kemkes.go.id
                </p>
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>Nota Pesanan Bahan Makanan</h1>
        <p class="order-number">No. {{ $purchaseOrder->order_number }}</p>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-column">
                <div class="info-block">
                    <h3>Informasi Pengirim</h3>
                    <div class="info-item">
                        <span class="info-label">Dari</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $purchaseOrder->createdBy->name ?? 'Bagian Pembelian' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal</span>
                        <span class="info-colon">:</span>
                        <span
                            class="info-value">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d F Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="info-column">
                <div class="info-block">
                    <h3>Informasi Supplier</h3>
                    <div class="info-item">
                        <span class="info-label">Kepada</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $purchaseOrder->supplier->name ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alamat</span>
                        <span class="info-colon">:</span>
                        <span class="info-value">{{ $purchaseOrder->supplier->address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-section">
        <table>
            <thead class="table-header">
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-item">Uraian Jenis Bahan Makanan</th>
                    <th class="col-qty">Jumlah</th>
                    <th class="col-unit">Harga Satuan</th>
                    <th class="col-total">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrder->items as $index => $item)
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-item">{{ $item->item->name }}</td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-unit">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="col-total">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total Section -->
    <div class="total-section">
        <div class="total-box">
            <div class="total-label">TOTAL KESELURUHAN</div>
            <div class="total-amount">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-left">
            <div class="signature-box">
                <div class="signature-title">Supplier</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $purchaseOrder->supplier->name ?? 'Nama Supplier' }}</div>
            </div>
        </div>

        <div class="signature-right">
            <div class="signature-box">
                <div class="signature-title">Mengetahui & Menyetujui</div>
                <div class="signature-line"></div>
                <div class="signature-name">Kepala Bagian Pembelian</div>
            </div>
        </div>
    </div>
</body>

</html>
