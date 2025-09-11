<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance; // ← PENTING: tarik model Attendance
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
    protected static ?string $model = Payroll::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Relawan';
    protected static ?string $navigationLabel = 'Penggajian';
    protected static ?string $label = 'Penggajian';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Info Relawan')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'name')
                        ->label('Nama Relawan')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            if (!$state) {
                                self::clearEmployeeDerived($set);
                                return;
                            }
                            self::loadEmployeeData($state, $set);
                            self::recalcTotalDay($get, $set);
                            self::pullAttendanceStats($get, $set);  // ← tarik status absensi
                            self::recalcThp($get, $set);
                        }),

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
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalcTotalDay($get, $set);
                            self::pullAttendanceStats($get, $set); // ← auto-ambil absensi
                            self::recalcThp($get, $set);
                        }),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now())
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalcTotalDay($get, $set);
                            self::pullAttendanceStats($get, $set); // ← auto-ambil absensi
                            self::recalcThp($get, $set);
                        }),
                ]),

            Section::make('Absensi')
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('total_day')
                        ->label('Jumlah Hari')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated(false)
                        ->default(0),

                    Forms\Components\TextInput::make('work_days')
                        ->label('Jumlah Hari Masuk')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalcThp($get, $set)),

                    Forms\Components\TextInput::make('off_day')
                        ->label('Jumlah Libur')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalcThp($get, $set)),

                    Forms\Components\TextInput::make('permit')
                        ->label('Jumlah Izin')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalcThp($get, $set)),

                    Forms\Components\TextInput::make('absences')
                        ->label('Jumlah Absen')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::recalcThp($get, $set)),
                ]),

            Section::make('Info Gaji')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('other')
                        ->label('Other / Cashbon (belum dipakai)')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->default(0)
                        ->live(),

                    Forms\Components\Toggle::make('is_manual_thp')
                        ->label('Input THP Manual')
                        ->default(false)
                        ->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            // kalau manual dimatikan, trigger hitung otomatis
                            if (!$state) {
                                self::recalcThp($get, $set);
                            }
                        })
                        ->helperText('Jika aktif, total THP tidak dihitung otomatis.'),

                    Forms\Components\TextInput::make('total_thp')
                        ->label('Total THP')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->default(0)
                        ->live(),

                    Forms\Components\TextInput::make('note')
                        ->label('Catatan')
                        ->placeholder('Jika ada catatan')
                        ->columnSpanFull(),
                ]),

            // Hidden derived (tidak disimpan)
            Forms\Components\TextInput::make('salary_per_day')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('allowance')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('absence_deduction')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('permit_amount')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('dept_bonus')->hidden()->dehydrated(false)->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('employee.department.name')->label('Posisi')->badge(),
                Tables\Columns\TextColumn::make('month')->label('Bulan')->date('M Y'),
                Tables\Columns\TextColumn::make('start_date')->date('d M')->label('Dari'),
                Tables\Columns\TextColumn::make('end_date')->label('Sampai')->date('d M'),
                Tables\Columns\TextColumn::make('work_days')->label('Masuk')->numeric()->suffix(' Hari')->toggleable(),
                Tables\Columns\TextColumn::make('off_day')->label('Libur')->numeric()->suffix(' Hari')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('permit')->label('Izin')->numeric()->suffix(' Hari')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('absences')->label('Absen')->numeric()->suffix(' Hari')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employee.department.allowance')->numeric()->label('Kesehatan')->prefix('Rp. '),
                Tables\Columns\IconColumn::make('is_manual_thp')->label('Manual')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_thp')->label('Total THP')->summarize(Sum::make()->label('Total')->prefix('Rp. '))->numeric()->prefix('Rp. '),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')->label('Nama Relawan')->relationship('employee', 'name')->searchable()->preload(),
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
                        if (empty($data['month'])) return $query;
                        $monthDate = Carbon::createFromFormat('Y-m', $data['month']);
                        return $query->whereYear('month', $monthDate->year)
                            ->whereMonth('month', $monthDate->month);
                    }),
                Tables\Filters\TernaryFilter::make('is_manual_thp')->label('Mode THP')
                    ->placeholder('Semua')->trueLabel('Manual')->falseLabel('Otomatis'),
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
            'index'  => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }

    // =========================
    // Helpers
    // =========================

    protected static function loadEmployeeData(int $employeeId, Set $set): void
    {
        $emp = Employee::with('department')->find($employeeId);

        $deptName   = $emp?->department?->name ?? '-';
        $salary     = (int) ($emp?->department?->salary ?? 0);
        $allowance  = (int) ($emp?->department?->allowance ?? 0);          // insentif kesehatan
        $deduction  = (int) ($emp?->department?->absence_deduction ?? 0);
        $permitAmt  = (int) ($emp?->department?->permit_amount ?? 0);      // nominal izin per hari
        $deptBonus  = (int) ($emp?->department?->bonus ?? 0);              // PJ / bonus leader

        $set('department_name', $deptName);
        $set('salary_per_day', $salary);
        $set('allowance', $allowance);
        $set('absence_deduction', $deduction);
        $set('permit_amount', $permitAmt);
        $set('dept_bonus', $deptBonus);
    }


    protected static function clearEmployeeDerived(Set $set): void
    {
        $set('department_name', '-');
        $set('salary_per_day', 0);
        $set('allowance', 0);
        $set('absence_deduction', 0);
        $set('permit_amount', 0);
        $set('dept_bonus', 0);

        // juga kosongkan angka absensi
        $set('work_days', 0);
        $set('permit', 0);
        $set('off_day', 0);
        $set('absences', 0);
        $set('total_thp', 0);
        $set('total_day', 0);
    }

    protected static function recalcTotalDay(Get $get, Set $set): void
    {
        $start = $get('start_date');
        $end   = $get('end_date');

        if (!$start || !$end) {
            $set('total_day', 0);
            return;
        }

        try {
            $s = Carbon::parse($start)->startOfDay();
            $e = Carbon::parse($end)->startOfDay();
            $days = $s->diffInDays($e) + 1;
            $set('total_day', max(0, $days));
        } catch (\Throwable $e) {
            $set('total_day', 0);
        }
    }

    /**
     * Tarik statistik absensi dari tabel attendances berdasar status.
     * - masuk -> work_days
     * - izin  -> permit
     * - libur -> off_day
     * - alpa  -> absences
     */
    protected static function pullAttendanceStats(Get $get, Set $set): void
    {
        $employeeId = (int) ($get('employee_id') ?? 0);
        $start      = $get('start_date');
        $end        = $get('end_date');

        if (!$employeeId || !$start || !$end) {
            $set('work_days', 0);
            $set('permit', 0);
            $set('off_day', 0);
            $set('absences', 0);
            return;
        }

        try {
            $from = \Illuminate\Support\Carbon::parse($start)->toDateString();
            $to   = \Illuminate\Support\Carbon::parse($end)->toDateString();

            $q = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$from, $to]);

            $workDays = (clone $q)->where('status', 'masuk')->count();
            $permit   = (clone $q)->where('status', 'izin')->count();
            $off      = (clone $q)->where('status', 'libur')->count();
            $absent   = (clone $q)->where('status', 'alpa')->count();

            $set('work_days', $workDays);
            $set('permit', $permit);
            $set('off_day', $off);
            $set('absences', $absent);
        } catch (\Throwable $e) {
            $set('work_days', 0);
            $set('permit', 0);
            $set('off_day', 0);
            $set('absences', 0);
        }
    }

    /**
     * THP otomatis (jika BUKAN manual):
     *   THP = (work_days × salary_per_day)
     *       + (permit × permit_amount)
     *       + allowance                      // NEW
     *       + dept_bonus                     // NEW (PJ)
     *       − (absences × absence_deduction)
     * Catatan:
     *   - off_day tidak dibayar
     *   - other/cashbon BELUM dihitung sekarang
     */
    protected static function recalcThp(Get $get, Set $set): void
    {
        if ((bool) ($get('is_manual_thp') ?? false)) {
            // Mode manual murni: jangan ubah total_thp sama sekali
            return;
        }

        $workDays   = (int) ($get('work_days') ?? 0);
        $permitDays = (int) ($get('permit') ?? 0);
        $absences   = (int) ($get('absences') ?? 0);

        $daily      = (int) ($get('salary_per_day') ?? 0);
        $permitAmt  = (int) ($get('permit_amount') ?? 0);
        $deduction  = (int) ($get('absence_deduction') ?? 0);

        $allowance  = (int) ($get('allowance') ?? 0);     // NEW
        $deptBonus  = (int) ($get('dept_bonus') ?? 0);    // NEW

        $presentPay = $workDays * $daily;
        $permitPay  = $permitDays * $permitAmt;
        $penalty    = $absences * $deduction;

        $thp = max(0, (int) ($presentPay + $permitPay + $allowance + $deptBonus - $penalty));
        $set('total_thp', $thp);
    }
}
