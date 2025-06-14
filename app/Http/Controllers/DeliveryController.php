<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function print(Delivery $record)
    {
        $pdf = Pdf::loadView('pdf.delivery', [
            'delivery' => $record,
        ])->setPaper('A4', 'portrait');

        $fileName = $record->delivery_date . '_' .
        // Carbon::parse($record->delivery_date)->format('Ymd') . '_' .
        Str::slug($record->recipient?->name ?? 'penerima') . '.pdf';

        return $pdf->stream($fileName);
    }
}
