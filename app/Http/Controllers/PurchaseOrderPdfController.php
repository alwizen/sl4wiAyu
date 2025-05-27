<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseOrderPdfController extends Controller
{
    public function generate(PurchaseOrder $purchaseOrder)
    {
        $pdf = Pdf::loadView('pdf.purchase-order', compact('purchaseOrder'));

        return $pdf->stream("PO-{$purchaseOrder->order_number}.pdf");
    }
}