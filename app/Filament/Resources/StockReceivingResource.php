<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockReceivingResource\Pages;
use App\Filament\Resources\StockReceivingResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\StockReceiving;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
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

class StockReceivingResource extends Resource
{
    protected static ?string $model = StockReceiving::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $label = 'Penerimaan Stok';

    protected static ?string $pluralLabel = 'Penerimaan Stok';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Daftar Penerimaan Barang')
                ->schema([
                    // Tambahkan field untuk purchase_order_id
                    Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship('purchaseOrder', 'order_number') // Sesuaikan dengan kolom yang menampilkan nomor PO
                        ->required(),

                    Repeater::make('stockReceivingItems')
                        ->label('Item Penerimaan')
                        ->relationship() // Tambahkan ini agar repeater bekerja dengan relationship
                        ->schema([
                            Select::make('warehouse_item_id')
                                ->label('Item Gudang')
                                ->options(WarehouseItem::all()->pluck('name', 'id'))
                                ->required(),
                            TextInput::make('received_quantity')
                                ->label('Jumlah Diterima')
                                ->numeric()
                                ->required(),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Penerimaan')
                    ->date(),
                TextColumn::make('purchaseOrder.order_number')
                    ->label('Purchase Order ID')
                    ->searchable(),
                // Gunakan relationship table untuk menampilkan item-item yang diterima
                TextColumn::make('stockReceivingItems.warehouseItem.name')
                    ->label('Item Gudang')
                    ->listWithLineBreaks(),
                TextColumn::make('stockReceivingItems.received_quantity')
                    ->label('Jumlah Diterima')
                    ->listWithLineBreaks(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->listWithLineBreaks(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockReceivings::route('/'),
            'create' => Pages\CreateStockReceiving::route('/create'),
            'edit' => Pages\EditStockReceiving::route('/{record}/edit'),
        ];
    }
}
