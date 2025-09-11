<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User; // ← ambil user untuk role akuntan
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class PayrollController extends Controller
{
    public function cetakSlip(Payroll $payroll)
    {
        $payroll->load(['employee.department']);

        // Default
        $accountantName = '(Role akuntan Belum Di set)';

        try {
            $accountantName = \App\Models\User::role('akuntan')->value('name') ?? $accountantName;
        } catch (RoleDoesNotExist $e) {
            // Role 'akuntan' belum dibuat → biarin pakai default
            report($e); // opsional, kalau mau tetap dilog
        }

        $periodeText = Carbon::parse($payroll->start_date)->locale('id')->translatedFormat('d F Y')
            . ' - ' .
            Carbon::parse($payroll->end_date)->locale('id')->translatedFormat('d F Y');

        $data = [
            'payroll'        => $payroll,
            'employee'       => $payroll->employee,
            'department'     => $payroll->employee->department,
            'periode'        => $periodeText,
            'accountantName' => $accountantName,
            'tanggal_cetak'  => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'app_address'    => config('app.address'),
            'app_city'       => config('app.city'),
        ];


        $pdf = Pdf::loadView('payroll.slip', $data)->setPaper('A4', 'portrait');

        $filePeriod = $payroll->month
            ? Carbon::parse($payroll->month)->format('Y-m')
            : Carbon::parse($payroll->start_date)->format('Ymd') . '-' . Carbon::parse($payroll->end_date)->format('Ymd');

        $filename = 'Slip-Gaji-' . str_replace(' ', '-', $payroll->employee->name) . '-' . $filePeriod . '.pdf';

        return $pdf->stream($filename);
    }
}
