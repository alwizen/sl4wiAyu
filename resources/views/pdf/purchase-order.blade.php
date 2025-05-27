<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan - {{ $purchaseOrder->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .no-border td { border: none; padding: 2px 5px; }
        .header-table { margin-bottom: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .logo { width: 80px; }
    </style>
</head>
<body>

    {{-- Kop & Logo --}}
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                <img src="{{ public_path('images/bgn.png') }}" class="logo">
            </td>
            <td>
                {{-- <strong>KOP BADAN GIZI NASIONAL</strong><br> --}}
                <span>NOTA PESANAN BAHAN MAKANAN</span><br><br>
                <strong>No: </strong> {{ $purchaseOrder->order_number }}
            </td>
        </tr>
    </table>

    {{-- Informasi Umum --}}
    <table class="no-border">
        <tr>
            <td style="width: 80px;">Dari</td>
            <td>: {{ $purchaseOrder->createdBy->name ?? 'Bagian Pembelian' }}</td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>: {{ $purchaseOrder->supplier->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: {{ $purchaseOrder->supplier->address ?? '-' }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>: {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d-m-Y') }}</td>
        </tr>
    </table>

    {{-- Tabel Item --}}
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Uraian Jenis Bahan Makanan</th>
                <th style="width: 80px;">Banyaknya<br>(Angka)</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 100px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    {{-- <td>{{ $item->unit_price }}</td> --}}
                    <td>Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <p class="text-right" style="margin-top: 10px;">
        <strong>Total: Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</strong>
    </p>

</body>
</html>
