<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Illuminate\Http\Request;


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

    public function cetakSlipBulk(Request $request)
    {
        // ids = "1,3,5" dari query
        $ids = collect(explode(',', (string) $request->query('ids')))->filter()->map(fn($v) => (int) $v)->all();
        if (empty($ids)) {
            abort(400, 'Tidak ada payroll yang dipilih.');
        }

        $payrolls = Payroll::with('employee.department')->whereIn('id', $ids)->orderBy('id')->get();
        if ($payrolls->isEmpty()) {
            abort(404, 'Data payroll tidak ditemukan.');
        }

        // ambil akuntan (Shield/Spatie)
        $accountantName = '(Role akuntan tidak ada)';
        try {
            $accountantName = User::role('akuntan')->value('name') ?? $accountantName;
        } catch (RoleDoesNotExist $e) {
            report($e);
        }

        $data = [
            'payrolls'      => $payrolls,
            'accountantName' => $accountantName,
            'app_address'   => config('app.address'),
            'app_city'      => config('app.city'),
            'tanggalCetak'  => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('payroll.slips-bulk', $data)->setPaper('A4', 'portrait');
        $filename = 'Slip-Gaji-Bulk-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
