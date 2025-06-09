<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Format Pemeriksaan Bahan Makanan</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            margin: 20px;
            line-height: 1.3;
        }
        
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .kop-table td {
            border: none;
            vertical-align: top;
            padding: 5px;
        }
        
        .logo-cell {
            width: 80px;
        }
        
        .logo-cell img {
            width: 130px;
            height: auto;
        }
        
        .header-text {
            text-align: left;
            padding-left: 20px;
        }
        
        .header-text h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .header-text p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        .document-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
        }
        
        .document-number {
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        
        .info-label {
            width: 80px;
            font-weight: normal;
        }
        
        .info-colon {
            width: 15px;
            text-align: center;
        }
        
        
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
        }
        
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .main-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 9px;
        }
        
        .main-table .number-col {
            width: 30px;
        }
        
        .main-table .item-col {
            width: 25%;
            text-align: left;
        }
        
        .main-table .quantity-col {
            width: 12%;
        }
        
        .main-table .unit-col {
            width: 10%;
        }
        
        .main-table .check-col {
            width: 8%;
        }
        
        .signature-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature-section p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .signature-name {
            margin-top: 50px;
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 200px;
            text-align: center;
        }
        
        /* Placeholder rows */
        .empty-row {
            height: 25px;
        }
    </style>
</head>
<body>

    {{-- HEADER KOP --}}
    <table class="kop-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('images/bgn.png') }}" alt="Logo Badan Gizi Nasional">
            </td>
            <td class="header-text">
                <h2>KOP BADAN GIZI NASIONAL</h2>
                <p>Format Pemeriksaan Bahan Makanan</p>
            </td>
        </tr>
    </table>
    
    <hr style="border: 1px solid #000; margin: 15px 0;">

    {{-- DOCUMENT TITLE --}}
    <div class="document-title">
        FORMAT PEMERIKSAAN BAHAN MAKANAN
    </div>
    
    <div class="document-number">
        No. ..........................................
    </div>

    {{-- INFORMATION SECTION --}}
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Dari :</div>
            {{-- <div class="info-colon">:</div>
            <div class="info-value"></div> --}}
        </div>
        <div class="info-row">
            <div class="info-label">Kepada :</div>
            {{-- <div class="info-colon">:</div>
            <div class="info-value"></div> --}}
        </div>
        <div class="info-row">
            <div class="info-label">Alamat :</div>
            {{-- <div class="info-colon">:</div>
            <div class="info-value"></div> --}}
        </div>
        <div class="info-row">
            <div class="info-label">Waktu :</div>
            {{-- <div class="info-colon">:</div>
            <div class="info-value"></div>
        </div> --}}
    </div>

    {{-- MAIN TABLE --}}
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="number-col">No</th>
                <th rowspan="2" class="item-col">Jenis Bahan Makanan</th>
                <th rowspan="2" class="quantity-col">Banyaknya<br>(Angka)</th>
                <th rowspan="2" class="unit-col">Satuan</th>
                <th colspan="2">Jumlah</th>
                <th colspan="2">Kondisi Bahan Makanan</th>
            </tr>
            <tr>
                <th class="check-col">Sesuai</th>
                <th class="check-col">Tidak</th>
                <th class="check-col">Baik</th>
                <th class="check-col">Rusak</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data) && $data->stockReceivingItems)
                @foreach($data->stockReceivingItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="text-align: left;">{{ $item->warehouseItem->name }}</td>
                        <td>{{ $item->expected_quantity }}</td>
                        <td>{{ $item->warehouseItem->unit }}</td>
                        <td>{{ $item->expected_quantity == $item->received_quantity ? $item->received_quantity : '' }}</td>
                        <td>{{ $item->expected_quantity != $item->received_quantity ? $item->received_quantity : '' }}</td>
                        <td>{{ $item->good_quantity }}</td>
                        <td>{{ $item->damaged_quantity }}</td>
                    </tr>
                @endforeach
            @endif
           
        </tbody>
    </table>

    {{-- SIGNATURE SECTION --}}
    <div class="signature-section">
        <p>.........................., {{ isset($data) ? \Carbon\Carbon::now()->translatedFormat('d F Y') : '........................' }}</p>
        <p>Kepala Satuan Pelayanan</p>
        <p>Pemenuhan Gizi</p>
        <div class="signature-name">
            (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
        </div>
    </div>

</body>
</html>