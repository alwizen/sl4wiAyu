<?php

namespace App\Http\Controllers;

use App\Models\StockReceiving;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StockReceivingController extends Controller
{
    public function print($record)
    {
        $data = StockReceiving::with(['stockReceivingItems.warehouseItem', 'purchaseOrder'])->findOrFail($record);

        $pdf = Pdf::loadView('pdf.stock-receiving', compact('data'));

        return $pdf->stream("penerimaan-barang-{$data->id}.pdf");
    }
}