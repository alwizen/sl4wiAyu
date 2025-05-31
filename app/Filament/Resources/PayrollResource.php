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

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Penggajian';

    protected static ?string $label = 'Penggajian';



    public static function form(Form $form): Form
    {
        return $form
        ->columns(1)
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Nama Relawan')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::hitungTHP($get, $set)),

                \Coolsam\Flatpickr\Forms\Components\Flatpickr::make('month')
                    ->required()
                    ->label('Bulan')
                    ->placeholder('Pilih bulan')
                    ->monthPicker()
                    ->format('Y-m')
                    ->displayFormat('F Y'),

                Forms\Components\TextInput::make('work_days')
                    ->label('Jumlah Hari Masuk')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::hitungTHP($get, $set)),

                Forms\Components\TextInput::make('absences')
                    ->label('Jumlah Absen')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set, callable $get) => self::hitungTHP($get, $set)),

                Forms\Components\TextInput::make('total_thp')
                    ->label('Total THP (Otomatis)')
                    ->required()
                    ->dehydrated(true) // Pastikan field ini tersimpan
                    ->numeric()
                    ->readOnly() // Gunakan readOnly instead of disabled
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Relawan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_days')
                    ->label('Hari Masuk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('absences')
                    ->label('Absen')
                    ->numeric()
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
                //
            ])
            ->actions([
                Tables\Actions\Action::make('cetak_slip')
                    ->label('Cetak Slip')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Payroll $record): string => route('payroll.slip', $record))
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
