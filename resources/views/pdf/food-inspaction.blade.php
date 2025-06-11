<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pemeriksaan Makanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 40px;
        }

        .kop-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .logo-cell {
            width: 80px;
            vertical-align: top;
        }

        .logo-cell img {
            width: 70px;
            height: auto;
        }

        .header-text {
            text-align: center;
        }

        .header-text h2 {
            margin: 0;
            font-size: 16px;
        }

        .header-text p {
            margin: 0;
            font-size: 13px;
        }

        .nomor {
            text-align: center;
            margin-bottom: 20px;
        }

        .judul {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 15px;
        }

        .info td {
            padding: 2px 5px;
            vertical-align: top;
        }

        table.pemeriksaan {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }

        table.pemeriksaan th,
        table.pemeriksaan td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
        }

        table.pemeriksaan th {
            background: #f2f2f2;
            font-size: 9px;
        }

        .footer {
            margin-top: 50px;
            width: 100%;
        }

        .footer .ttd {
            width: 250px;
            float: right;
            text-align: center;
        }

        .footer .ttd p {
            margin: 5px 0;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table style="width: 100%;">
        <tr>
            <td style="width: 70px;">
                <img src="{{ public_path('images/bgn.png') }}" alt="Logo" width="130">
            </td>
            {{-- <td style="text-align: left; font-size: 12px;">
                <strong>BADAN GIZI NASIONAL</strong><br>
                Pemeriksaan Uji Penyediaan Makanan<br>
                Berdasarkan Program Pemenuhan Gizi<br>
                Bergizi Gratis (SSPG)
            </td> --}}
        </tr>
    </table>
    
    <hr style="border: 1px solid black; margin-top: 5px; margin-bottom: 10px;">
    

    <div class="judul"><h2>Pemeriksaan Makanan</h2></div>

    <div class="nomor">No. .............................................................</div>

    {{-- Informasi pengantar --}}
    <table class="info">
        <tr>
            <td width="60px">Dari</td>
            <td>: ..................................................</td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>: ..................................................</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: ..................................................</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>: {{ \Carbon\Carbon::parse($plan->inspaction_date)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    {{-- Tabel utama pemeriksaan --}}
    <table class="pemeriksaan">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Uraian Jenis Makanan</th>
                <th rowspan="2">Tanggal Sampel Makanan disiapkan</th>
                <th colspan="2">Hasil Uji Makanan</th>
            </tr>
            <tr>
                <th>Baik</th>
                <th>Tidak Baik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plan->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $item->menu->menu_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($plan->inspaction_date) }}</td>
                    <td>{{ $item->is_good === 1 ? 'Ya' : '' }}</td>
                    <td>{{ $item->is_good === 0 ? 'Ya' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda tangan --}}
    <div class="footer">
        <div class="ttd">
            <p>........................., ..........</p>
            <p>Kepala SSPG</p>
            <br><br><br>
            <p>(.................................................)</p>
            {{-- <p>NIP. .......................................</p> --}}
        </div>
    </div>

</body>
</html>
