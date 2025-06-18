<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Daftar Posisi';

    protected static ?string $label = 'Posisi';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationIcon = '';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('salary')
                    ->label('Gaji Harian')
                    ->required()
                    ->numeric()
                    ->suffix(' /Hari')
                    ->default(0),
                Forms\Components\TextInput::make('allowance')
                    ->label('Tunjangan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('absence_deduction')
                    ->required()
                    ->label('Denda Harian')
                    ->numeric()
                    ->default(0)
                    ->helperText('Denda untuk setiap ketidakhadiran'),
                // Forms\Components\TextInput::make('bonus')
                //     ->numeric()
                //     ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('Nama Posisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('salary')
                    ->label('Gaji Harian')
                    ->numeric()
                    ->suffix(' /Hari')
                    ->prefix('Rp. ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('allowance')
                    ->numeric()
                    ->label('Tunjangan')
                    ->prefix('Rp. ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('absence_deduction')
                    ->numeric()
                    ->label('Denda Ketidakhadiran')
                    ->suffix(' /Hari')
                    ->prefix('Rp. ')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('bonus')
                //     ->numeric()
                //     ->sortable(),
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
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
