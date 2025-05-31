<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    public function cetakSlip(Payroll $payroll)
    {
        // Load relasi yang diperlukan
        $payroll->load(['employee.department']);
        
        // Data untuk slip gaji
        $data = [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'department' => $payroll->employee->department,
            'periode' => \Carbon\Carbon::parse($payroll->month)->format('F Y'),
            'tanggal_cetak' => now()->format('d F Y'),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('payroll.slip', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Slip-Gaji-' . $payroll->employee->name . '-' . \Carbon\Carbon::parse($payroll->month)->format('Y-m') . '.pdf';
        
        return $pdf->download($filename);
    }
}