<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 4px; }
    </style>
</head>
<body>
    <img src="{{ public_path('images/bgn.png') }}" height="50" alt="Logo">
    <h3 style="text-align: center;">RENCANA NUTRISI</h3>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($plan->nutrition_plan_date)->translatedFormat('d F Y') }}</p>

    <table width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Menu</th>
                <th>Penerima</th>
                <th>Netto (gr)</th>
                <th>Energi (kkal)</th>
                <th>Protein</th>
                <th>Lemak</th>
                <th>Karbo</th>
                <th>Serat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plan->nutritionPlanItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->menu->menu_name ?? '-' }}</td>
                    <td>{{ $item->targetGroup->name ?? '-' }}</td>
                    <td>{{ number_format($item->netto, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->energy, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->protein, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->fat, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->carb, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->serat, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
