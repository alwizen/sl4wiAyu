<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pengiriman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 20px 30px;
        }

        .judul h2 {
            margin: 0;
            font-size: 14px;
        }

        .nomor {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .info {
            margin-bottom: 10px;
            width: 100%;
        }

        .info td {
            padding: 1px 3px;
            vertical-align: top;
            font-size: 9px;
        }

        table.pemeriksaan {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 10px;
        }

        table.pemeriksaan th,
        table.pemeriksaan td {
            border: 1px solid #000;
            padding: 4px 2px;
            text-align: center;
        }

        table.pemeriksaan th {
            background: #f2f2f2;
        }

        .footer {
            margin-top: 30px;
            width: 100%;
        }

        .footer .ttd {
            width: 200px;
            float: right;
            text-align: center;
            font-size: 9px;
        }

        .kop-table {
            width: 100%;
            margin-bottom: 5px;
        }

        .kop-table img {
            width: 100px;
            height: auto;
        }

    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td>
                <img src="{{ public_path('images/bgn.png') }}" alt="Logo">
            </td>
        </tr>
    </table>

    <hr style="border: 0.5px solid black; margin-top: 5px; margin-bottom: 10px;">

    <div class="judul" style="text-align: center;">
        <h2>Surat Pengiriman Barang</h2><br>
    </div>

    <table class="info">
        <tr>
            <td>No Reff</td>
            <td>: {{ $delivery->delivery_number }}</td>
        </tr>
        <tr>
            <td width="70px">Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($delivery->delivery_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Penerima</td>
            <td>: {{ $delivery->recipient?->name }}</td>
        </tr>
        <tr>
            <td>Petugas</td>
            <td>: {{ $delivery->user?->name }}</td>
        </tr>
        <tr>
            <td>Kendaraan</td>
            <td>: {{ $delivery->car?->car_number }}</td>
        </tr>
    </table>

    <table class="pemeriksaan">
        <thead>
            <tr>
                <th>No</th>
                <th>Jumlah Dikirim</th>
                <th>Jumlah Diterima</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $delivery->qty . ' Porsi/Box' }}</td>
                <td>{{ $delivery->received_qty }}</td>
                <td>{{ $delivery->note }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd">
            <p>{{ now()->translatedFormat('d F Y') }}</p>
            <p>Penerima</p>
            <br><br><br>
            <p>({{ $delivery->recipient?->name }})</p>
        </div>
    </div>

</body>
</html>
