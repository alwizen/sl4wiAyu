<?php

namespace App\Filament\Resources;

use App\Exports\StockReceivingItemsExport;
use App\Filament\Resources\StockReceivingResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\StockReceiving;
use App\Models\WarehouseItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class StockReceivingResource extends Resource
{
    protected static ?string $model = StockReceiving::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?string $label = 'Penerimaan Stok';

    protected static ?string $pluralLabel = 'Penerimaan Stok';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Daftar Penerimaan Barang')
                ->schema([
                    DatePicker::make('received_date')
                        ->label('Tanggal Penerimaan')
                        ->default(now())
                        ->required(),

                    Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship(
                            name: 'purchaseOrder',
                            titleAttribute: 'order_number',
                            modifyQueryUsing: fn(Builder $query) => $query->where('order_date', '>=', now()->subDays(10))
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $po = PurchaseOrder::with('items.item')->find($state);

                            if (!$po) {
                                $set('stockReceivingItems', []);
                                return;
                            }

                            $set('stockReceivingItems', $po->items->map(function ($item) {
                                return [
                                    'warehouse_item_id' => $item->item_id,
                                    'received_quantity' => null,
                                ];
                            })->toArray());
                        }),

                    Repeater::make('stockReceivingItems')
                        ->label('Item Penerimaan')
                        ->relationship()
                        ->schema([
                            Select::make('warehouse_item_id')
                                ->label('Item Gudang')
                                ->searchable()
                                ->preload()
                                ->options(WarehouseItem::all()->pluck('name', 'id'))
                                ->required(),

                            TextInput::make('received_quantity')
                                ->label('Jumlah Diterima')
                                ->numeric()
                                ->required(),
                        ])
                        ->columns(2),

                    Textarea::make('note')
                        ->label('Catatan')
                        ->rows(3)
                        ->placeholder('Masukkan catatan jika ada'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Penerimaan')
                    ->date(),

                TextColumn::make('purchaseOrder.order_number')
                    ->label('Purchase Order')
                    ->searchable(),

                TextColumn::make('stockReceivingItems.warehouseItem.name')
                    ->label('Item Gudang')
                    ->listWithLineBreaks(),

                TextColumn::make('stockReceivingItems.received_quantity')
                    ->label('Jumlah Diterima')
                    ->listWithLineBreaks(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export-selected')
                    ->label('Ekspor Penerimaan Barang')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id');
                        $timestamp = Carbon::now()->format('Ymd_His');

                        return Excel::download(
                            new StockReceivingItemsExport($ids),
                            "stock-receivings_{$timestamp}.xlsx"
                        );
                    }),
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
