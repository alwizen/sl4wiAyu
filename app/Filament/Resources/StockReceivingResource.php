<?php

namespace App\Filament\Resources;

use App\Exports\StockReceivingItemsExport;
use App\Filament\Resources\StockReceivingResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\StockReceiving;
use App\Models\WarehouseItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

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
                ->collapsible()
                ->columns(2)
                ->schema([
                    DatePicker::make('received_date')
                        ->label('Tanggal')
                        ->default(now())
                        ->required(),

                    Select::make('purchase_order_id')
                        ->label('Purchase Order')
                        ->relationship(
                            name: 'purchaseOrder',
                            titleAttribute: 'order_number',
                            modifyQueryUsing: fn(Builder $query) => $query
                                ->where('payment_status', '!=', 'paid')
                                // ->where('status', 'approved')
                                ->where('is_received_complete', false)

                            // modifyQueryUsing: fn(Builder $query) => $query->where('order_date', '>=', now()->subDays(10))
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
                                    'expected_quantity' => $item->quantity, // Quantity dari PO
                                    'received_quantity' => null,
                                    'good_quantity' => null,
                                    'damaged_quantity' => null,
                                    'is_quantity_matched' => false,
                                ];
                            })->toArray());
                        }),

                    Textarea::make('note')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Masukkan catatan jika ada'),
                ]),

            Section::make('Item Penerimaan')
                ->collapsible()
                ->schema([
                    TableRepeater::make('stockReceivingItems')
                        ->label('')
                        ->relationship()
                        ->schema([
                            Select::make('warehouse_item_id')
                                ->label('Item Gudang')
                                ->searchable()
                                ->preload()
                                ->options(WarehouseItem::all()->pluck('name', 'id'))
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $item = WarehouseItem::find($state);
                                }),

                            TextInput::make('expected_quantity')
                                ->label('Jumlah PO')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->suffix(function (callable $get) {
                                    $itemId = $get('warehouse_item_id');
                                    if ($itemId) {
                                        $item = WarehouseItem::find($itemId);
                                        return $item?->unit ?? '';
                                    }
                                    return '';
                                }),

                            TextInput::make('received_quantity')
                                ->label('Jumlah Diterima')
                                ->numeric()
                                ->debounce(500)
                                ->required()
                                ->reactive()
                                ->suffix(function (callable $get) {
                                    $itemId = $get('warehouse_item_id');
                                    if ($itemId) {
                                        $item = WarehouseItem::find($itemId);
                                        return $item?->unit ?? '';
                                    }
                                    return '';
                                })
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $expected = $get('expected_quantity');
                                    if ($expected && $state) {
                                        $set('is_quantity_matched', $state == $expected);
                                    }

                                    // Auto-calculate good_quantity if only received_quantity is filled
                                    // $good = $get('good_quantity');
                                    // $damaged = $get('damaged_quantity');

                                    // if ($state && !$good && !$damaged) {
                                    //     $set('good_quantity', $state);
                                    //     $set('damaged_quantity', 0);
                                    // }
                                }),

                            TextInput::make('good_quantity')
                                ->label('Jumlah Baik')
                                ->numeric()
                                ->required()
                                ->debounce(500)
                                ->reactive()
                                ->suffix(function (callable $get) {
                                    $itemId = $get('warehouse_item_id');
                                    if ($itemId) {
                                        $item = WarehouseItem::find($itemId);
                                        return $item?->unit ?? '';
                                    }
                                    return '';
                                })
                            // ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            //     $damaged = $get('damaged_quantity') ?? 0;
                            //     $total = $state + $damaged;
                            //     $set('received_quantity', $total);

                            //     $expected = $get('expected_quantity');
                            //     if ($expected) {
                            //         $set('is_quantity_matched', $total == $expected);
                            //     }
                            // })
                            ,

                            TextInput::make('damaged_quantity')
                                ->label('Jumlah Rusak')
                                ->numeric()
                                ->default(0)
                                ->reactive()
                                ->suffix(function (callable $get) {
                                    $itemId = $get('warehouse_item_id');
                                    if ($itemId) {
                                        $item = WarehouseItem::find($itemId);
                                        return $item?->unit ?? '';
                                    }
                                    return '';
                                })
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $good = $get('good_quantity') ?? 0;
                                    $total = $good + $state;
                                    $set('received_quantity', $total);

                                    $expected = $get('expected_quantity');
                                    if ($expected) {
                                        $set('is_quantity_matched', $total == $expected);
                                    }
                                }),

                            Checkbox::make('is_quantity_matched')
                                ->label('Sesuai?')
                                ->disabled()
                                ->dehydrated(true),
                        ])
                        ->columns(5)
                        ->collapsible()
                        ->itemLabel(
                            fn(array $state): ?string =>
                            WarehouseItem::find($state['warehouse_item_id'])?->name ?? null
                        ),

                ]),

            // Section::make('Catatan')
            // ->collapsible()
            //     ->schema([
            //         Textarea::make('note')
            //             ->label('Catatan')
            //             ->rows(3)
            //             ->placeholder('Masukkan catatan jika ada'),
            //     ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('received_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('purchaseOrder.order_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),

                // TextColumn::make('stockReceivingItems')
                //     ->label('Total Item')
                //     ->state(function (StockReceiving $record): string {
                //         $total = $record->stockReceivingItems->count();
                //         $matched = $record->stockReceivingItems->where('is_quantity_matched', true)->count();
                //         return "{$matched}/{$total}";
                //     })
                //     ->badge()
                //     ->color(
                //         fn(StockReceiving $record): string =>
                //         $record->is_all_quantity_matched ? 'primary' : 'success'
                //     ),


                TextColumn::make('stockReceivingItems.warehouseItem.name')
                    ->label('Nama Item')
                    ->listWithLineBreaks(),


                TextColumn::make('stockReceivingItems.expected_quantity')
                    ->label('Total PO')
                    ->numeric()
                    ->listWithLineBreaks()
                    // ->suffix(function (callable $get) {
                    //     $itemId = $get('warehouse_item_id');
                    //     if ($itemId) {
                    //         $item = WarehouseItem::find($itemId);
                    //         return $item?->unit ?? '';
                    //     }
                    //     return '';
                    // })
                    ->sortable(),

                TextColumn::make('stockReceivingItems.received_quantity')
                    ->label('Total Diterima')
                    ->listWithLineBreaks()
                    ->numeric()
                    ->sortable(),

                TextColumn::make('stockReceivingItems.good_quantity')
                    ->label('Kondisi Baik')
                    ->listWithLineBreaks()
                    ->numeric()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('stockReceivingItems.damaged_quantity')
                    ->label('Kondisi Rusak')
                    ->listWithLineBreaks()
                    ->numeric()
                    ->color('danger')
                    ->sortable(),

                IconColumn::make('stockReceivingItems.is_quantity_matched')
                    ->label('Sesuai')
                    ->icon(fn(string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        '0' => 'heroicon-o-x-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                    })
                    ->listWithLineBreaks(),


                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('quantity_matched')
                    ->label('Jumlah Sesuai')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereHas('stockReceivingItems', function (Builder $query) {
                            $query->where('is_quantity_matched', true);
                        })
                    ),

                Tables\Filters\Filter::make('has_damaged')
                    ->label('Ada Barang Rusak')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereHas('stockReceivingItems', function (Builder $query) {
                            $query->where('damaged_quantity', '>', 0);
                        })
                    ),

                Tables\Filters\Filter::make('recent')
                    ->label('7 Hari Terakhir')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->where('received_date', '>=', now()->subDays(7))
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('print')
                        ->label('Cetak PDF')
                        ->icon('heroicon-o-printer')
                        ->url(fn(StockReceiving $record) => route('stock-receiving.print', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])

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

                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('received_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockReceivings::route('/'),
            'create' => Pages\CreateStockReceiving::route('/create'),
            // 'view' => Pages\ViewStockReceiving::route('/{record}'),
            'edit' => Pages\EditStockReceiving::route('/{record}/edit'),
        ];
    }
}
