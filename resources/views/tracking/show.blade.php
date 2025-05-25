<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pengiriman</title>
</head>
<body>
<h1>Tracking Pengiriman</h1>
<p><strong>No. Pengiriman:</strong> {{ $delivery->delivery_number }}</p>
<p><strong>Tanggal:</strong> {{ $delivery->delivery_date->format('d-m-Y') }}</p>
<p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}</p>
<p><strong>Penerima:</strong> {{ $delivery->recipient->name ?? '-' }}</p>
<p><strong>Supir:</strong> {{ $delivery->user->name ?? '-' }}</p>
<p><strong>Jumlah:</strong> {{ $delivery->qty }} Box</p>

@if ($delivery->received_qty)
    <p><strong>Jumlah Diterima:</strong> {{ $delivery->received_qty }} Box</p>
@endif

@if ($delivery->proof_delivery)
    <p><strong>Bukti Pengiriman:</strong></p>
    <img src="{{ asset('storage/' . $delivery->proof_delivery) }}" alt="Bukti" style="max-width:300px;">
@endif
</body>
</html>
