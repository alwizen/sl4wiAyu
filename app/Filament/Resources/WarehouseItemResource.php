<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Filament\Resources\WarehouseItemResource\RelationManagers;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Warehouse';

    
public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Informasi Barang')
            ->description('Detail informasi barang')
            ->columns(2)
            ->schema([
                Select::make('warehouse_category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->required()
                    ->columnSpan(1)
                    ->createOptionForm([
                        TextInput::make('name')->label('Nama Kategori')->required(),
                    ]),

                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->columnSpan(1),
            ]),

        Section::make('Detail Stok')
            ->description('Informasi satuan dan stok barang')
            ->columns(2)
            ->schema([
                TextInput::make('unit')
                    ->label('Satuan (kg, liter, pcs)')
                    ->required()
                    ->columnSpan(1),

                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->default(0)
                    ->disabled() // input hanya dari proses lain
                    ->columnSpan(1),
            ]),
    ]);
}



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                // TextColumn::make('unit')
                //     ->label('Satuan'),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 0, ',', '.') . ' ' . $record->unit)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseItems::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
