<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Relawan';

    protected static ?string $navigationLabel = 'Daftar Relawan';

    protected static ?string $label = 'Daftar Relawan';

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
                        return 'MGS-5' . $randomDigits;
                    })
                    ->disabled() // Nonaktifkan field agar tidak dapat diubah
                    ->dehydrated() // Pastikan nilai tetap dikirim ke database
                    ->helperText('NIP otomatis dihasilkan dengan format MGS-5xxxxx'),
                
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\DatePicker::make('start_join')
                    ->label('Tanggal Bergabung')
                    ->required(),
                
                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('start_join')
                    ->label('Tanggal Bergabung')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ManageEmployees::route('/'),
        ];
    }
}