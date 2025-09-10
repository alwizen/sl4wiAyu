<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PayrollResource extends Resource
{
    protected const PERMIT_RATE = 0.5; // 50% dibayar untuk hari izin
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Relawan';
    protected static ?string $navigationLabel = 'Penggajian';
    protected static ?string $label = 'Penggajian';

    /**
     * Hitung work_days/permit/off_day/absences HANYA dari status explicit.
     * Hari tanpa record TIDAK dihitung sebagai absen.
     */
    protected static function fillAttendanceFromRange(Set $set, Get $get): void
    {
        $employeeId = $get('employee_id');
        $startDate  = $get('start_date');
        $endDate    = $get('end_date');

        if (!($employeeId && $startDate && $endDate)) {
            return;
        }

        $start = \Illuminate\Support\Carbon::parse($startDate)->startOfDay();
        $end   = \Illuminate\Support\Carbon::parse($endDate)->endOfDay();

        $rows = \App\Models\Attendance::query()
            ->select(['date', 'status'])
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalDays = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;

        // Sesuaikan istilah status yang kamu pakai
        $present = ['present', 'masuk', 'hadir'];
        $permit  = ['permit', 'izin'];
        $off     = ['off', 'libur'];
        $absent  = ['absent', 'alpa', 'tidakhadir'];

        // Normalisasi & unik per tanggal
        $byDate = $rows->map(function ($a) {
            $a->status = $a->status ? mb_strtolower(trim($a->status)) : null;
            return $a;
        })->unique('date');

        // HANYA dari status explicit
        $workDays = $byDate->filter(fn($a) => in_array($a->status, $present, true))->count();
        $permitCt = $byDate->filter(fn($a) => in_array($a->status, $permit, true))->count();
        $offDay   = $byDate->filter(fn($a) => in_array($a->status, $off, true))->count();
        $absences = $byDate->filter(fn($a) => in_array($a->status, $absent, true))->count();

        // set ke form
        $set('total_day', $totalDays);
        $set('work_days', $workDays);
        $set('permit', $permitCt);
        $set('off_day', $offDay);
        $set('absences', $absences);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Relawan')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'name')
                        ->label('Nama Relawan')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if ($state) {
                                $employee = Employee::with('department')->find($state);
                                if ($employee && $employee->department) {
                                    $set('department_name', $employee->department->name);
                                    $set('salary_per_day', $employee->department->salary ?? 0);
                                    $set('allowance', $employee->department->allowance ?? 0);
                                    $set('absence_deduction', $employee->department->absence_deduction ?? 0);
                                }
                            }

                            // >> Auto ambil attendance saat ganti karyawan
                            static::fillAttendanceFromRange($set, $get);

                            // Recalculate THP
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),


                    // Field tambahan buat tampilkan departemen
                    Forms\Components\TextInput::make('department_name')
                        ->label('Bagian/Posisi')
                        ->readOnly()
                        ->dehydrated(false)
                        ->default('-'),
                ]),
            Section::make('Info Range Tanggal')
                ->columns(3)
                ->schema([
                    \Coolsam\Flatpickr\Forms\Components\Flatpickr::make('month')
                        ->required()
                        ->label('Bulan')
                        ->placeholder('Pilih bulan')
                        ->monthPicker()
                        ->format('Y-m')
                        ->displayFormat('F Y'),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalDays($set, $get);

                            // >> Auto ambil attendance saat tanggal berubah
                            static::fillAttendanceFromRange($set, $get);
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now())
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalDays($set, $get);

                            // >> Auto ambil attendance saat tanggal berubah
                            static::fillAttendanceFromRange($set, $get);
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),
                ]),

            Forms\Components\Section::make('Absensi')
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('total_day')
                        ->label('Jumlah Hari')
                        ->numeric()
                        ->readOnly()
                        ->required(),

                    Forms\Components\TextInput::make('work_days')
                        ->label('Jumlah Hari Masuk')
                        ->numeric()
                        ->required()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('off_day')
                        ->label('Jumlah Libur')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('permit')
                        ->label('Jumlah Izin')
                        ->numeric()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('absences')
                        ->label('Jumlah Absen')
                        ->numeric()
                        ->required()
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),
                ]),

            Forms\Components\Section::make('Info Gaji')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('other')
                        ->label('Other / PJ')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            static::calculateTotalTHP($set, $get);
                        })
                        ->live(onBlur: true),

                    Forms\Components\TextInput::make('total_thp')
                        ->label('Total THP (Otomatis)')
                        // ->readOnly()
                        ->dehydrated(true)
                        ->numeric()
                        ->prefix('Rp'),

                    Forms\Components\TextInput::make('note')
                        ->label('Catatan')
                        ->placeholder('Jika ada catatan'),
                ]),

            // Hidden fields untuk menyimpan data dari department
            Forms\Components\TextInput::make('salary_per_day')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('allowance')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('absence_deduction')->hidden()->dehydrated(false)->default(0),

        ]);
    }

    /**
     * Calculate Total Days from date range
     */
    protected static function calculateTotalDays(Set $set, Get $get): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            // Calculate total days including both start and end date
            $totalDays = $start->diffInDays($end) + 1;

            $set('total_day', $totalDays);
        }
    }

    /**
     * Calculate Total THP automatically
     * Formula: (work_days * salary_per_day) + allowance + other - (absences * absence_deduction)
     */
    protected static function calculateTotalTHP(Set $set, Get $get): void
    {
        $workDays         = (float) ($get('work_days') ?? 0);
        $permitDays       = (float) ($get('permit') ?? 0);
        $salaryPerDay     = (float) ($get('salary_per_day') ?? 0);
        $allowance        = (float) ($get('allowance') ?? 0);
        $other            = (float) ($get('other') ?? 0);
        $absences         = (float) ($get('absences') ?? 0);
        $absenceDeduction = (float) ($get('absence_deduction') ?? 0);

        // Gaji pokok berdasarkan hadir & izin
        $presentPay = $workDays * $salaryPerDay;
        $permitPay  = $permitDays * $salaryPerDay * (static::PERMIT_RATE ?? 0.5);

        // Potongan absen
        $totalDeduction = $absences * $absenceDeduction;

        $totalTHP = $presentPay + $permitPay + $allowance + $other - $totalDeduction;

        $set('total_thp', max(0, $totalTHP)); // jaga-jaga tidak negatif
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.department.name')
                    ->label('Posisi')
                    ->badge(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->date('M Y'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M')
                    ->label('Dari'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M'),
                Tables\Columns\TextColumn::make('work_days')
                    ->label('Masuk')
                    ->numeric()
                    ->suffix(' Hari')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('off_day')
                    ->label('Libur')
                    ->numeric()
                    ->suffix(' Hari')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('permit')
                    ->label('Izin')
                    ->numeric()
                    ->suffix(' Hari')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('absences')
                    ->label('Absen')
                    ->numeric()
                    ->suffix(' Hari')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employee.department.allowance')
                    ->numeric()
                    ->label('Kesehatan')
                    ->prefix('Rp. '),

                Tables\Columns\TextColumn::make('other')
                    ->label('PJ')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_thp')
                    ->label('Total THP')
                    ->summarize(Sum::make()
                        ->label('Total')
                        ->prefix('Rp. '))
                    ->numeric()
                    ->prefix('Rp. '),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Nama Relawan')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('month')
                    ->label('Bulan & Tahun')
                    ->form([
                        \Coolsam\Flatpickr\Forms\Components\Flatpickr::make('month')
                            ->label('Pilih Bulan')
                            ->monthPicker()
                            ->format('Y-m')
                            ->displayFormat('F Y')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['month'])) {
                            return $query;
                        }
                        $monthDate = Carbon::createFromFormat('Y-m', $data['month']);
                        return $query
                            ->whereYear('month', $monthDate->year)
                            ->whereMonth('month', $monthDate->month);
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('cetak_slip')
                        ->label('Cetak Slip')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn(Payroll $record): string => route('payroll.slip', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->button()->icon('heroicon-o-paper-clip'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
