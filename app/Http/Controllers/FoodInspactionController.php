<?php

namespace App\Http\Controllers;

use App\Models\FoodInspaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FoodInspactionController extends Controller
{
    public function print(FoodInspaction $record)
    {
        $pdf = Pdf::loadView('pdf.food-inspaction', [
            'plan' => $record,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('food-inspaction.pdf');
    }
}
