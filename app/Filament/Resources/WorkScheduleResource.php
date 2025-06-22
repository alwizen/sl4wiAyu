<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkScheduleResource\Pages;
use App\Models\WorkSchedule;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Jadwal Kerja';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $label = 'Jadwal Kerja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Karyawan')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->where('work_type', 'shift')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Employee $record) => "{$record->name} ({$record->nip})")
                    ->searchable(['name', 'nip'])
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $employee = Employee::find($state);
                            if ($employee && $employee->work_type !== 'shift') {
                                $set('employee_id', null);
                                // Tambahkan notifikasi jika perlu
                            }
                        }
                    }),


                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->minDate(now()->subDays(7))
                    ->maxDate(now()->addDays(30)),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Jam Masuk')
                    ->required()
                    ->default('08:00')
                    ->seconds(false),

                Forms\Components\TimePicker::make('end_time')
                    ->label('Jam Pulang')
                    ->required()
                    ->default('17:00')
                    ->seconds(false)
                    ->after('start_time'),

                Forms\Components\Select::make('type')
                    ->label('Tipe Shift')
                    ->options([
                        'shift' => 'Shift',
                        'overtime' => 'Lembur',
                        'special' => 'Khusus',
                    ])
                    ->default('shift')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Masuk')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Pulang')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'shift' => 'primary',
                        'overtime' => 'warning',
                        'special' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Tipe Shift')
                    ->options([
                        'shift' => 'Shift',
                        'overtime' => 'Lembur',
                        'special' => 'Khusus',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfWeek()),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->default(now()->endOfWeek()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkSchedules::route('/'),
            'create' => Pages\CreateWorkSchedule::route('/create'),
            'edit' => Pages\EditWorkSchedule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('employee', function (Builder $query) {
                $query->where('work_type', 'shift');
            });
    }
}
