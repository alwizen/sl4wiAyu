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
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Maatwebsite\Excel\Facades\Excel;

class StockReceivingResource extends Resource
{
    protected static ?string $model = StockReceiving::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';
    protected static ?string $navigationGroup = 'Gudang';
    protected static ?string $label = 'Penerimaan Stok';
    protected static ?string $navigationLabel = 'Penerimaan Stok';
    protected static ?string $pluralLabel = 'Penerimaan Stok';
    protected static bool $shouldRegisterNavigation = false;

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
                            modifyQueryUsing: fn(Builder $query) => $query->availableForReceiving()
                        )
                        ->getOptionLabelFromRecordUsing(function ($record) {
                            $deliveryStatus = $record->delivery_status;
                            $progress = number_format($record->delivery_progress, 1);
                            return "{$record->order_number} - {$deliveryStatus} ({$progress}%)";
                        })
                        ->preload() // kalau data PO banyak, matikan preload & aktifkan searchable()
                        // ->searchable()
                        ->required()
                        ->live(onBlur: true) // lebih hemat render
                        ->afterStateUpdated(function ($state, callable $set) {
                            $po = PurchaseOrder::with('items.item')->find($state);

                            if (! $po) {
                                $set('stockReceivingItems', []);
                                return;
                            }

                            $items = $po->getItemsNeedingReceiving();
                            if ($items->isEmpty()) {
                                $set('stockReceivingItems', []);
                                return;
                            }

                            $set('stockReceivingItems', $items->map(function ($i) {
                                return [
                                    'warehouse_item_id' => $i->item->id,
                                    'unit'              => $i->item->unit,          // cache unit, hindari query di suffix
                                    'expected_quantity' => (float) $i->remaining_quantity,
                                    'received_quantity' => null,
                                    'good_quantity'     => null,
                                    'damaged_quantity'  => null,
                                    'is_quantity_matched' => false,
                                ];
                            })->values()->toArray());
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
                            // Pilih item — pakai relationship agar ringan
                            Select::make('warehouse_item_id')
                                ->label('Item Gudang')
                                ->relationship('warehouseItem', 'name')
                                ->searchable()
                                // ->preload() // aktifkan kalau item tidak terlalu banyak
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // cache unit sekali saja, hindari query berulang di suffix
                                    $unit = WarehouseItem::query()
                                        ->whereKey($state)
                                        ->value('unit');
                                    $set('unit', $unit ?? '');
                                }),

                            // cache unit untuk suffix, tidak disimpan ke DB
                            Forms\Components\Hidden::make('unit')->dehydrated(false),

                            TextInput::make('expected_quantity')
                                ->label('Jumlah PO')
                                ->numeric()
                                ->step('0.01')
                                ->minValue(0)
                                ->disabled() // readonly – berasal dari PO
                                ->suffix(fn(callable $get) => $get('unit') ?? ''),

                            TextInput::make('good_quantity')
                                ->label('Jumlah Baik')
                                ->numeric()
                                ->step('0.01')
                                ->minValue(0)
                                ->required()
                                ->live(onBlur: true)
                                ->suffix(fn(callable $get) => $get('unit') ?? '')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $damaged = (float) ($get('damaged_quantity') ?? 0);
                                    $total = (float) ($state ?? 0) + $damaged;

                                    $set('received_quantity', $total);

                                    $expected = (float) ($get('expected_quantity') ?? 0);
                                    if ($expected > 0) {
                                        $set('is_quantity_matched', round($total, 2) === round($expected, 2));
                                    }
                                }),

                            TextInput::make('damaged_quantity')
                                ->label('Jumlah Rusak')
                                ->numeric()
                                ->default(0)
                                ->step('0.01')
                                ->minValue(0)
                                ->live(onBlur: true)
                                ->suffix(fn(callable $get) => $get('unit') ?? '')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $good = (float) ($get('good_quantity') ?? 0);
                                    $total = $good + (float) ($state ?? 0);

                                    $set('received_quantity', $total);

                                    $expected = (float) ($get('expected_quantity') ?? 0);
                                    if ($expected > 0) {
                                        $set('is_quantity_matched', round($total, 2) === round($expected, 2));
                                    }
                                }),

                            TextInput::make('received_quantity')
                                ->label('Jumlah Diterima')
                                ->numeric()
                                ->step('0.01')
                                ->minValue(0)
                                ->required()
                                ->live(onBlur: true)
                                ->suffix(fn(callable $get) => $get('unit') ?? '')
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $expected = (float) ($get('expected_quantity') ?? 0);
                                    if ($expected > 0 && $state !== null && $state !== '') {
                                        $set('is_quantity_matched', round((float) $state, 2) === round($expected, 2));
                                    }
                                }),

                            Checkbox::make('is_quantity_matched')
                                ->label('Sesuai?')
                                ->disabled()
                                ->dehydrated(true),
                        ])
                        ->columns(6) // +1 kolom karena ada hidden 'unit' (tak terlihat)
                        ->collapsible()
                        ->itemLabel(
                            fn(array $state): ?string =>
                            \App\Models\WarehouseItem::find($state['warehouse_item_id'] ?? null)?->name ?? null
                        ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('received_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('purchaseOrder.order_number')->label('No. PO')->searchable()->sortable(),

                TextColumn::make('stockReceivingItems.warehouseItem.name')
                    ->label('Nama Item')->listWithLineBreaks(),

                // TextColumn::make('stockReceivingItems.expected_quantity')
                //     ->label('Total PO')->numeric(2)->listWithLineBreaks(),

                TextColumn::make('stockReceivingItems.received_quantity')
                    ->label('Total Diterima')->numeric(2)->listWithLineBreaks()->sortable(),

                TextColumn::make('stockReceivingItems.good_quantity')
                    ->label('Kondisi Baik')->numeric(2)->listWithLineBreaks()->color('success')->sortable(),

                TextColumn::make('stockReceivingItems.damaged_quantity')
                    ->label('Kondisi Rusak')->numeric(2)->listWithLineBreaks()->color('danger')->sortable(),

                IconColumn::make('stockReceivingItems.is_quantity_matched')
                    ->label('Sesuai')
                    ->icon(fn(string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        '0' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'warning',
                        default => 'secondary',
                    })
                    ->listWithLineBreaks(),

                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('quantity_matched')
                    ->label('Jumlah Sesuai')
                    ->query(fn(Builder $q) => $q->whereHas('stockReceivingItems', fn($qq) => $qq->where('is_quantity_matched', true))),

                Tables\Filters\Filter::make('has_damaged')
                    ->label('Ada Barang Rusak')
                    ->query(fn(Builder $q) => $q->whereHas('stockReceivingItems', fn($qq) => $qq->where('damaged_quantity', '>', 0))),

                Tables\Filters\Filter::make('recent')
                    ->label('7 Hari Terakhir')
                    ->query(fn(Builder $q) => $q->where('received_date', '>=', now()->subDays(7))),
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
                ]),
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
            'index'  => Pages\ListStockReceivings::route('/'),
            'create' => Pages\CreateStockReceiving::route('/create'),
            'edit'   => Pages\EditStockReceiving::route('/{record}/edit'),
        ];
    }
}
