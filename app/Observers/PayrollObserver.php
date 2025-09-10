<?php

namespace App\Observers;

use App\Models\Payroll;
use App\Models\Employee;

class PayrollObserver
{
    public function creating(Payroll $payroll): void
    {
        $this->calculateTHP($payroll);
    }

    public function updating(Payroll $payroll): void
    {
        $this->calculateTHP($payroll);
    }

    private function calculateTHP(Payroll $payroll): void
    {
        if (!$payroll->employee_id) {
            $payroll->total_thp = 0;
            return;
        }

        // Ambil data employee dengan department
        $employee = Employee::with('department')->find($payroll->employee_id);
        $department = $employee?->department;

        if (!$department) {
            $payroll->total_thp = 0;
            return;
        }

        // Ambil nilai-nilai perhitungan
        $salaryPerDay = (int) ($department->salary ?? 0);
        $allowance = (int) ($department->allowance ?? 0);
        $absenceDeduction = (int) ($department->absence_deduction ?? 0);

        $workDays = (int) ($payroll->work_days ?? 0);
        $absences = (int) ($payroll->absences ?? 0);
        $other = (int) ($payroll->other ?? 0);

        // Hitung THP
        $gajiHarian = $salaryPerDay * $workDays;
        $potonganAbsen = $absences * $absenceDeduction;
        $totalTHP = $gajiHarian + $allowance + $other - $potonganAbsen;

        $payroll->total_thp = max($totalTHP, 0);

        // Log untuk debug
        \Log::info('THP Calculation in Observer:', [
            'event' => $payroll->exists ? 'updating' : 'creating',
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
            'salary_per_day' => $salaryPerDay,
            'work_days' => $workDays,
            'allowance' => $allowance,
            'other' => $other,
            'absences' => $absences,
            'absence_deduction' => $absenceDeduction,
            'gaji_harian' => $gajiHarian,
            'potongan_absen' => $potonganAbsen,
            'total_thp' => $payroll->total_thp,
            'raw_calculation' => $totalTHP
        ]);
    }
}
