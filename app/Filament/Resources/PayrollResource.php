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

// form action button
use Filament\Forms\Components\Actions as FormActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Section;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Relawan';
    protected static ?string $navigationLabel = 'Penggajian';
    protected static ?string $label = 'Penggajian';

    /* === Util: Hitung hari total (ringan) === */
    public static function updateTotalDays(callable $get, callable $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            if ($endDate->greaterThanOrEqualTo($startDate)) {
                $days = $startDate->diffInDays($endDate) + 1; // inklusif
                $set('total_day', $days);
            } else {
                $set('total_day', 0);
            }
        }
    }

    /* === Util: Hitung rekap kehadiran (1 query agregat) === */
    public static function hitungKehadiran(callable $get, callable $set): void
    {
        $employeeId = $get('employee_id');
        $startDate = $get('start_date');
        $endDate   = $get('end_date');

        if (!$employeeId || !$startDate || !$endDate) {
            $set('work_days', 0);
            $set('absences', 0);
            $set('permit', 0);
            $set('off_day', 0);
            return;
        }

        $agg = \App\Models\Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("
                SUM(CASE WHEN status = 'masuk' THEN 1 ELSE 0 END) AS masuk,
                SUM(CASE WHEN status = 'izin'  THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'alpa'  THEN 1 ELSE 0 END) AS alpa,
                SUM(CASE WHEN status = 'libur' THEN 1 ELSE 0 END) AS libur
            ")
            ->first();

        $masuk = (int) ($agg->masuk ?? 0);
        $izin  = (int) ($agg->izin ?? 0);
        $alpa  = (int) ($agg->alpa ?? 0);
        $libur = (int) ($agg->libur ?? 0);

        $workDays = $masuk + $izin;

        $set('work_days', $workDays);
        $set('absences', $alpa);
        $set('permit', $izin);
        $set('off_day', $libur);
    }

    /* === Util: Hitung THP (tanpa query, pakai state hidden) === */
    public static function hitungTHP(callable $get, callable $set): void
    {
        $employeeId = $get('employee_id');
        $workDays   = (int) ($get('work_days') ?? 0);
        $absences   = (int) ($get('absences') ?? 0);
        $other      = (int) ($get('other') ?? 0);

        if (!$employeeId) {
            $set('total_thp', 0);
            return;
        }

        $salaryPerDay     = (int) ($get('salary_per_day') ?? 0);
        $insentif         = (int) ($get('allowance') ?? 0);
        $potonganPerHari  = (int) ($get('absence_deduction') ?? 0);

        $gajiHarian     = $salaryPerDay * $workDays;
        $potonganAbsen  = $absences * $potonganPerHari;
        $total          = $gajiHarian + $insentif + $other - $potonganAbsen;

        $set('total_thp', max($total, 0));
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
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $emp  = \App\Models\Employee::with('department')->find($state);
                                $dept = $emp?->department;

                                // set hidden state untuk perhitungan
                                $set('salary_per_day', (int)($dept->salary ?? 0));
                                $set('allowance', (int)($dept->allowance ?? 0));
                                $set('absence_deduction', (int)($dept->absence_deduction ?? 0));

                                // tampilkan nama departemen di field khusus
                                $set('department_name', $dept?->name ?? '-');
                            } else {
                                $set('salary_per_day', 0);
                                $set('allowance', 0);
                                $set('absence_deduction', 0);
                                $set('department_name', '-');
                            }

                            $set('show_thp', false); // reset tampilan THP
                        }),

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
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) => static::updateTotalDays($get, $set)),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now())
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) => static::updateTotalDays($get, $set)),
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
                        ->required(),

                    Forms\Components\TextInput::make('off_day')
                        ->label('Jumlah Libur')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('permit')
                        ->label('Jumlah Izin')
                        ->numeric(),

                    Forms\Components\TextInput::make('absences')
                        ->label('Jumlah Absen')
                        ->numeric()
                        ->required(),
                ]),

            Forms\Components\Section::make('Info Gaji')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('other')
                        ->label('Other / PJ')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    Forms\Components\TextInput::make('total_thp')
                        ->label('Total THP (Otomatis)')
                        ->readOnly()
                        ->dehydrated(true)
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn($get) => (bool) $get('show_thp'))
                        ->helperText(function ($get) {
                            $employeeId = $get('employee_id');
                            if (!$employeeId) {
                                return 'Pilih karyawan terlebih dahulu untuk melihat nominal';
                            }

                            $salaryPerDay    = number_format((int) ($get('salary_per_day') ?? 0), 0, ',', '.');
                            $insentif        = number_format((int) ($get('allowance') ?? 0), 0, ',', '.');
                            $potonganPerHari = number_format((int) ($get('absence_deduction') ?? 0), 0, ',', '.');

                            return "Gaji/hari: Rp {$salaryPerDay} | Insentif: Rp {$insentif} | Potongan absen/hari: Rp {$potonganPerHari}";
                        }),

                    Forms\Components\TextInput::make('note')
                        ->label('Catatan')
                        ->placeholder('Jika ada catatan'),
                ]),

            /* === State & tombol aksi === */
            // state kontrol
            Forms\Components\Hidden::make('show_thp')
                ->default(false)
                ->dehydrated(false),

            // state salary/allowance/deduction (tidak disimpan ke DB)
            Forms\Components\TextInput::make('salary_per_day')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('allowance')->hidden()->dehydrated(false)->default(0),
            Forms\Components\TextInput::make('absence_deduction')->hidden()->dehydrated(false)->default(0),

            // tombol hitung
            FormActions::make([
                FormAction::make('hitung_thp')
                    ->label('Hitung Total THP')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->action(function (callable $get, callable $set) {
                        // hitung semua sekali saat tombol ditekan
                        static::updateTotalDays($get, $set);
                        static::hitungKehadiran($get, $set);
                        static::hitungTHP($get, $set);
                        $set('show_thp', true);
                    }),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
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
