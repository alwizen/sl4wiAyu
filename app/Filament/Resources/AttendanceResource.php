<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Absensi Relawan';

    protected static ?string $label = 'Absensi Relawan';

    protected static ?string $navigationGroup = 'Relawan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->required()
                    ->label('Nama Relawan')
                    ->relationship('employee', 'name'),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                Forms\Components\TimePicker::make('check_in')
                    ->label('Jam Masuk')
                    ->locale('id')
                    ->default(now())
                    ->displayFormat('H:i'),
                Forms\Components\TimePicker::make('check_out')
                    ->label('Keluar (Opsional)'),
                Forms\Components\Select::make('status')
                    ->options([
                        'masuk' => 'Masuk',
                        'libur' => 'Libur',
                        'izin' => 'Izin',
                        'alpa' => 'Alpa'
                    ])
                    ->default('masuk')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions(['50', '100'])
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.department.name')
                    ->label('Posisi')
                    ->badge(),

                Tables\Columns\TextColumn::make('date')
                    ->date('d-m-Y')
                    ->sortable()
                    ->label('Tanggal'),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Jam Masuk')
                    ->sortable()
                    ->date('H:i'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Jam Keluar')
                    ->date('H:i'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'masuk',
                        'primary' => 'libur',
                        'warning' => 'izin',
                        'danger'  => 'alpa'
                    ]),
                Tables\Columns\TextColumn::make('status_in')
                    ->badge()
                    ->label('Keterlambatan')
                    ->getStateUsing(function ($record) {
                        // $record adalah instance Attendance
                        if (!$record->check_in) {
                            return 'no_checkin';
                        }

                        $employee = $record->employee;
                        $dept = $employee?->department;

                        if (!$dept || !$dept->start_time) {
                            return 'not_configured';
                        }

                        // Tentukan tanggal attendance (menggunakan method di Department)
                        $attendanceDate = $dept->getAttendanceDate($record->check_in);
                        $shiftStart = Carbon::parse($attendanceDate . ' ' . $dept->start_time);

                        // shiftStart->diffInMinutes(check_in, false)
                        // positif = terlambat, negatif = lebih awal
                        $diffInMinutes = $shiftStart->diffInMinutes($record->check_in, false);
                        $tolerance = $dept->tolerance_late_minutes ?? 0;

                        // Jika datang terlalu awal (> 6 jam)
                        if ($diffInMinutes < -360) {
                            return 'too_early';
                        }

                        // On-time jika keterlambatan <= tolerance
                        return $diffInMinutes <= $tolerance ? 'on_time' : 'late';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                        'too_early' => 'Terlalu Awal',
                        'not_configured' => 'Belum Diatur',
                        'no_checkin' => '—',
                        default => '—',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'on_time' => 'success',
                        'late' => 'danger',
                        'too_early' => 'warning',
                        'not_configured' => 'gray',
                        'no_checkin' => 'gray',
                        default => 'gray',
                    })
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
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name'),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Departemen')
                    ->relationship('employee.department', 'name'),

                Tables\Filters\Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])


            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

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
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }
}
