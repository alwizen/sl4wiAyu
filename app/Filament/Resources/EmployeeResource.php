<?php

namespace App\Filament\Resources;


use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\AttendancesRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\EmployeeRelationManager;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Filament\Support\Enums\ActionSize;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Relawan';

    protected static ?string $navigationLabel = 'Daftar Relawan';

    protected static ?string $label = 'Relawan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nip')
                    ->label('NIP')
                    ->required()
                    ->maxLength(255)
                    ->default(function () {
                        // Generate NIP format MGS-5 diikuti 5 angka acak
                        $randomDigits = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                        return 'SPPG-5' . $randomDigits;
                    })
                    ->disabled()
                    ->dehydrated()
                    ->helperText('NIP otomatis digenerate oleh sistem.'),

                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->default(0)
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Posisi')
                    // ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('rfid_uid')
                    ->label('RFID')
                    ->helperText('Kartu ID Untuk Absesi Relawan')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('start_join')
                    ->label('Tanggal Bergabung')
                    ->default(now())
                    ->placeholder('Tanggal bergabung')
                    ->required(),

                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(255)
                    ->default('Tegal')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rfid_uid')
                    ->label('Kartu RFID (Absen)')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Posisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_join')
                    ->label('Tanggal Bergabung')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    RelationManagerAction::make('attendanceHistory')
                        ->label('Riwayat Absensi')
                        ->color('primary')
                        ->icon('heroicon-o-clock')
                        ->relationManager(AttendancesRelationManager::make()),

                    RelationManagerAction::make('payrollHistory')
                        ->label('Riwayat Penggajian')
                        ->color('success')
                        ->icon('heroicon-o-document-currency-dollar')
                        ->relationManager(EmployeeRelationManager::make()),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Tindakan')
                    ->icon('heroicon-m-paper-clip')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button()
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
            'index' => Pages\ManageEmployees::route('/'),
        ];
    }
}
