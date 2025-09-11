<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected const PERMIT_RATE = 0.5;

    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Data Penggajian')
                ->icon('heroicon-o-plus'),
            Actions\Action::make('bulk_create')
                ->label('Buat Penggajian Masal')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Select::make('department_ids')
                        ->label('Filter Berdasarkan Departemen')
                        ->multiple()
                        ->options(\App\Models\Department::pluck('name', 'id')->toArray())
                        ->placeholder('Pilih departemen (kosongkan untuk semua)')
                        // ->preload()
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('employee_ids', [])),

                    CheckboxList::make('employee_ids')
                        ->label('Pilih Karyawan')
                        ->options(function (Get $get) {
                            $query = \App\Models\Employee::with('department');

                            if (!empty($get('department_ids'))) {
                                $query->whereIn('department_id', $get('department_ids'));
                            }

                            return $query->get()->mapWithKeys(function ($employee) {
                                $deptName = $employee->department ? $employee->department->name : 'No Dept';
                                return [$employee->id => "{$employee->name} ({$deptName})"];
                            })->toArray();
                        })
                        ->columns(2)
                        ->searchable()
                        ->bulkToggleable()
                        ->required()
                        ->live(),


                    \Coolsam\Flatpickr\Forms\Components\Flatpickr::make('month')
                        ->required()
                        ->label('Bulan')
                        ->monthPicker()
                        ->format('Y-m')
                        ->displayFormat('F Y')
                        ->default(now()->format('Y-m')),

                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->default(now()->startOfMonth())
                        ->live(),

                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now()->endOfMonth())
                        ->live(),



                    Toggle::make('auto_calculate_attendance')
                        ->label('Otomatis Hitung Absensi')
                        ->helperText('Sistem akan mengambil data absensi dari database')
                        ->default(true),

                    Toggle::make('skip_existing')
                        ->label('Lewati yang Sudah Ada')
                        ->helperText('Tidak akan membuat payroll jika sudah ada untuk periode yang sama')
                        ->default(true),

                ])
                ->action(function (array $data) {
                    $employeeIds = $data['employee_ids'];
                    $month = $data['month'];
                    $startDate = Carbon::parse($data['start_date']);
                    $endDate = Carbon::parse($data['end_date']);

                    $created = 0;
                    $skipped = 0;
                    $errors = [];

                    foreach ($employeeIds as $employeeId) {
                        try {
                            $employee = Employee::with('department')->find($employeeId);

                            if (!$employee) {
                                $errors[] = "Employee ID {$employeeId} tidak ditemukan";
                                continue;
                            }

                            // Check if payroll already exists
                            if ($data['skip_existing']) {
                                $exists = Payroll::where('employee_id', $employeeId)
                                    ->where('month', $month)
                                    ->exists();

                                if ($exists) {
                                    $skipped++;
                                    continue;
                                }
                            }

                            // Calculate attendance if enabled
                            $attendanceData = [
                                'total_day' => $startDate->diffInDays($endDate) + 1,
                                'work_days' => 0,
                                'permit' => 0,
                                'off_day' => 0,
                                'absences' => 0,
                            ];

                            if ($data['auto_calculate_attendance']) {
                                $attendanceData = static::calculateAttendanceForEmployee($employeeId, $startDate, $endDate);
                            }

                            // Calculate THP
                            $totalThp = static::calculateTHPForEmployee($employee, $attendanceData);

                            // Create payroll
                            Payroll::create([
                                'employee_id' => $employeeId,
                                'month' => $month,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'total_day' => $attendanceData['total_day'],
                                'work_days' => $attendanceData['work_days'],
                                'permit' => $attendanceData['permit'],
                                'off_day' => $attendanceData['off_day'],
                                'absences' => $attendanceData['absences'],
                                'other' => 0,
                                'total_thp' => $totalThp,
                                'is_manual_thp' => false,
                                'note' => 'Generated via bulk create',
                            ]);

                            $created++;
                        } catch (\Exception $e) {
                            $errors[] = "Error untuk {$employee->name}: " . $e->getMessage();
                        }
                    }

                    // Show notification
                    $message = "Berhasil membuat {$created} payroll";
                    if ($skipped > 0) {
                        $message .= ", {$skipped} dilewati (sudah ada)";
                    }
                    if (!empty($errors)) {
                        $message .= ". Errors: " . implode(', ', array_slice($errors, 0, 3));
                    }

                    Notification::make()
                        ->title($created > 0 ? 'Bulk Create Berhasil' : 'Bulk Create Gagal')
                        ->body($message)
                        ->color($created > 0 ? 'success' : 'warning')
                        ->send();
                })
                ->modalWidth('4xl'),
        ];
    }

    /**
     * Calculate attendance data for specific employee and date range
     */
    protected static function calculateAttendanceForEmployee($employeeId, $startDate, $endDate): array
    {
        $rows = \App\Models\Attendance::query()
            ->select(['date', 'status'])
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totalDays = $startDate->copy()->diffInDays($endDate->copy()) + 1;

        // Status categories
        $present = ['present', 'masuk', 'hadir'];
        $permit  = ['permit', 'izin'];
        $off     = ['off', 'libur'];
        $absent  = ['absent', 'alpa', 'tidakhadir'];

        // Normalize and count unique by date
        $byDate = $rows->map(function ($a) {
            $a->status = $a->status ? mb_strtolower(trim($a->status)) : null;
            return $a;
        })->unique('date');

        $workDays = $byDate->filter(fn($a) => in_array($a->status, $present, true))->count();
        $permitCt = $byDate->filter(fn($a) => in_array($a->status, $permit, true))->count();
        $offDay   = $byDate->filter(fn($a) => in_array($a->status, $off, true))->count();
        $absences = $byDate->filter(fn($a) => in_array($a->status, $absent, true))->count();

        return [
            'total_day' => $totalDays,
            'work_days' => $workDays,
            'permit' => $permitCt,
            'off_day' => $offDay,
            'absences' => $absences,
        ];
    }

    /**
     * Calculate THP for employee with attendance data
     */
    protected static function calculateTHPForEmployee($employee, $attendanceData): float
    {
        if (!$employee->department) {
            return 0;
        }

        $workDays = $attendanceData['work_days'];
        $permitDays = $attendanceData['permit'];
        $absences = $attendanceData['absences'];

        $salaryPerDay = $employee->department->salary ?? 0;
        $allowance = $employee->department->allowance ?? 0;
        $absenceDeduction = $employee->department->absence_deduction ?? 0;

        // Calculate based on the same formula as manual input
        $presentPay = $workDays * $salaryPerDay;
        $permitPay = $permitDays * $salaryPerDay * (static::PERMIT_RATE ?? 0.5);
        $totalDeduction = $absences * $absenceDeduction;

        $totalTHP = $presentPay + $permitPay + $allowance - $totalDeduction;

        return max(0, $totalTHP);
    }

    /**
     * Bulk update THP for multiple payrolls
     */
    public static function bulkRecalculateTHP(array $payrollIds): int
    {
        $updated = 0;

        foreach ($payrollIds as $payrollId) {
            try {
                $payroll = Payroll::with('employee.department')->find($payrollId);

                if ($payroll && !$payroll->is_manual_thp) {
                    $attendanceData = [
                        'work_days' => $payroll->work_days,
                        'permit' => $payroll->permit,
                        'absences' => $payroll->absences,
                    ];

                    $newTHP = static::calculateTHPForEmployee($payroll->employee, $attendanceData);

                    $payroll->update(['total_thp' => $newTHP]);
                    $updated++;
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error("Failed to recalculate THP for payroll {$payrollId}: " . $e->getMessage());
            }
        }

        return $updated;
    }
}
