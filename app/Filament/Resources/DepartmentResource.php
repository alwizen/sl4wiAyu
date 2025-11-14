<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Daftar Jabatan';

    protected static ?string $label = 'Jabatan';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = 'heroicon-o-window';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Jabatan')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('salary')
                    ->label('Gaji Harian')
                    ->required()
                    ->prefix('Rp.')
                    ->numeric()
                    ->suffix(' /Hari')
                    ->default(0),
                Forms\Components\TextInput::make('allowance')
                    ->label('Tunjangan')
                    ->required()
                    ->prefix('Rp.')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('permit_amount')
                    ->label('Gaji (izin)')
                    ->required()
                    ->prefix('Rp.')
                    ->numeric()
                    ->default(50000),
                Forms\Components\TextInput::make('absence_deduction')
                    ->required()
                    ->label('Denda Harian')
                    ->numeric()
                    ->prefix('Rp.')
                    ->default(0)
                    ->helperText('Denda untuk setiap ketidakhadiran'),
                Forms\Components\TextInput::make('bonus')
                    ->label('PJ')
                    ->numeric()
                    ->prefix('Rp.')
                    ->default(0),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Jam Masuk')
                    ->native(false)
                    ->default('16:00')
                    ->seconds(false),

                Forms\Components\TimePicker::make('end_time')
                    ->label('Jam Pulang')
                    ->native(false)
                    ->seconds(false),

                Forms\Components\Toggle::make('is_overnight')
                    ->label('Melewati Tengah Malam?')
                    ->helperText('Centang jika shift melewati jam 00:00'),

                Forms\Components\TextInput::make('tolerance_late_minutes')
                    ->label('Toleransi Telat (Menit)')
                    ->numeric()
                    ->default(15),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->columns([
                ColumnGroup::make('Gaji', [
                    Tables\Columns\TextColumn::make('name')
                        ->label('Nama Posisi')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('salary')
                        ->label('Gaji')
                        ->numeric()
                        ->suffix(' /Hari')
                        ->prefix('Rp. '),
                    Tables\Columns\TextColumn::make('allowance')
                        ->numeric()
                        ->label('Tunj. Kesehatan')
                        ->prefix('Rp. '),
                    Tables\Columns\TextColumn::make('absence_deduction')
                        ->numeric()
                        ->label('Denda Absen')
                        ->suffix(' /Hari')
                        ->prefix('Rp. '),
                    Tables\Columns\TextColumn::make('permit_amount')
                        ->numeric()
                        ->label('Gaji /Izin')
                        ->suffix(' /Hari')
                        ->prefix('Rp. '),

                    Tables\Columns\TextColumn::make('bonus')
                        ->numeric()
                        ->label('PJ')
                        ->prefix('Rp. '),
                ]),

                ColumnGroup::make('Jam Kerja', [
                    Tables\Columns\TextColumn::make('start_time')
                        ->label('Masuk')
                        ->time('H:i'),
                    Tables\Columns\TextColumn::make('end_time')
                        ->label('Pulang')
                        ->time('H:i'),
                    Tables\Columns\TextColumn::make('is_overnight')
                        ->label('Tengah Malam'),
                    Tables\Columns\TextColumn::make('tolerance_late_minutes')
                        ->label('Toleransi')
                        ->suffix(' Menit'),

                ])
                    ->alignment(Alignment::Center)
                    ->wrapHeader(),

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
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->icon('heroicon-o-adjustments-horizontal')
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
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
