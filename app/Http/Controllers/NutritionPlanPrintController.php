<?php

namespace App\Http\Controllers;

use App\Models\NutritionPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class NutritionPlanPrintController extends Controller
{
    public function print(NutritionPlan $record)
    {
        $pdf = Pdf::loadView('pdf.nutrition-plan', [
            'plan' => $record,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('nutrition-plan.pdf');
    }
}