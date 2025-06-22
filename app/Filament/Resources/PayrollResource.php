<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Relawan';

    protected static ?string $navigationLabel = 'Penggajian';

    protected static ?string $label = 'Penggajian';

    protected static function updateTotalDays(callable $get, callable $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            if ($endDate->greaterThanOrEqualTo($startDate)) {
                $days = $startDate->diffInDays($endDate) + 1; // tambahkan +1 untuk inklusif
                $set('total_day', $days);
            } else {
                $set('total_day', 0);
            }
        }
    }

    protected static function hitungKehadiran($get, $set): void
    {
        $employeeId = $get('employee_id');
        $startDate = $get('start_date');
        $endDate = $get('end_date');

        if (!$employeeId || !$startDate || !$endDate) {
            $set('work_days', 0);
            $set('absences', 0);
            $set('permit', 0);
            return;
        }

        $masuk = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'masuk')
            ->count();

        $permit = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'izin')
            ->count();

        $alpa = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'alpa')
            ->count();

        $workDays = $masuk + $permit;

        $set('work_days', $workDays);
        $set('absences', $alpa);
        $set('permit', $permit);

        static::hitungTHP($get, $set);
    }

    protected static function hitungTHP($get, $set): void
    {
        $employeeId = $get('employee_id');
        $workDays = (int) $get('work_days') ?? 0;
        $absences = (int) $get('absences') ?? 0;

        if (!$employeeId) {
            $set('total_thp', 0);
            return;
        }

        $employee = Employee::with('department')->find($employeeId);

        if (!$employee || !$employee->department) {
            $set('total_thp', 0);
            return;
        }

        $dept = $employee->department;
        $salaryPerDay = $dept->salary ?? 0;        // Salary per hari (contoh: 50.000)
        $insentif = $dept->allowance ?? 0;         // Insentif bulanan (contoh: 500.000)
        $potonganPerHari = $dept->absence_deduction ?? 0; // Potongan per hari absen (contoh: 10.000)

        // Rumus: (salary/hari × hari kehadiran) + insentif bulanan - (absen × potongan per hari)
        $gajiHarian = $salaryPerDay * $workDays;
        $potonganAbsen = $absences * $potonganPerHari;

        $total = $gajiHarian + $insentif - $potonganAbsen;

        $set('total_thp', max($total, 0));
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Nama Relawan')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => [
                        static::updateTotalDays($get, $set),
                        static::hitungKehadiran($get, $set),
                    ]),

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
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => [
                        static::updateTotalDays($get, $set),
                        static::hitungKehadiran($get, $set),
                    ]),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->required()
                    ->reactive()
                    ->default(now())
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => [
                        static::updateTotalDays($get, $set),
                        static::hitungKehadiran($get, $set),
                    ]),

                Forms\Components\TextInput::make('total_day')
                    ->label('Jumlah Hari')
                    ->numeric()
                    ->readOnly()
                    ->required(),

                Forms\Components\TextInput::make('work_days')
                    ->label('Jumlah Hari Masuk')
                    ->numeric()
                    ->debounce(500)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::hitungTHP($get, $set)),

                Forms\Components\TextInput::make('permit')
                    ->label('Jumlah permit')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('absences')
                    ->label('Jumlah Absen')
                    ->numeric()
                    ->debounce(500)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::hitungTHP($get, $set)),

                Forms\Components\TextInput::make('total_thp')
                    ->label('Total THP (Otomatis)')
                    ->required()
                    ->dehydrated(true)
                    ->numeric()
                    ->readOnly()
                    ->helperText(function ($get) {
                        $employeeId = $get('employee_id');
                        if (!$employeeId) {
                            return 'Pilih karyawan terlebih dahulu untuk melihat nominal';
                        }

                        $employee = Employee::with('department')->find($employeeId);
                        if (!$employee || !$employee->department) {
                            return 'Data departement tidak ditemukan';
                        }

                        $dept = $employee->department;
                        $salaryPerDay = number_format($dept->salary ?? 0, 0, ',', '.');
                        $insentif = number_format($dept->allowance ?? 0, 0, ',', '.');
                        $potonganPerHari = number_format($dept->absence_deduction ?? 0, 0, ',', '.');

                        return "Gaji/hari: Rp {$salaryPerDay} | Insentif: Rp {$insentif} | Potongan absen/hari: Rp {$potonganPerHari}";
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M')
                    ->label('Dari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d M')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_days')
                    ->label('Hari Masuk')
                    ->numeric()
                    ->sortable()
                    ->suffix(' Hari'),
                Tables\Columns\TextColumn::make('permit')
                    ->label('Jml permit')
                    ->numeric()
                    ->sortable()
                    ->suffix(' Hari'),
                Tables\Columns\TextColumn::make('absences')
                    ->label('Absen')
                    ->numeric()
                    ->suffix(' Hari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_thp')
                    ->label('Total THP')
                    ->summarize(Sum::make())
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter Nama Relawan
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
                Tables\Actions\Action::make('cetak_slip')
                    ->label('Cetak Slip')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn(Payroll $record): string => route('payroll.slip', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayrolls::route('/'),
        ];
    }
}
