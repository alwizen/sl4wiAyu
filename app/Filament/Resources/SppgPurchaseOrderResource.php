<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SppgPurchaseOrderResource\Pages;
use App\Models\SppgPurchaseOrder;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components as Info;
use Filament\Infolists\Infolist;

class SppgPurchaseOrderResource extends Resource
{
    protected static ?string $model = SppgPurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Dapur';
    protected static ?string $navigationLabel = 'PO Dapur (SPPG)';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()->columns(12)->schema([
                TextInput::make('po_number')
                    ->label('Nomor PO')
                    ->default(fn() => SppgPurchaseOrder::generateNumber())
                    ->disabled()
                    ->dehydrated() // tetap simpan nilai default
                    ->required()
                    ->columnSpan(4),

                DatePicker::make('requested_at')
                    ->label('Tanggal')
                    ->default(now())
                    ->required()
                    ->columnSpan(3),

                TimePicker::make('delivery_time')
                    ->label('Jam Pengiriman')
                    ->seconds(false)
                    ->required()
                    ->columnSpan(3),

                TextInput::make('status')
                    ->label('Status')
                    ->disabled()
                    ->dehydrated()
                    ->default('Draft')
                    ->columnSpan(2),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpan(12),

                Forms\Components\Hidden::make('created_by')
                    ->default(fn() => auth()->id())
                    ->dehydrated(),
            ]),

            Repeater::make('items')
                ->relationship('items')
                ->label('Daftar Item')
                ->minItems(1)
                // ->collapsed()
                // ->grid(12)
                ->schema([
                    Section::make()->columns(12)->schema([
                        Toggle::make('manual_entry')
                            ->label('Ketik Manual')
                            ->default(false)
                            ->live()
                            ->columnSpan(2),

                        Select::make('warehouse_item_id')
                            ->label('Barang (Master)')
                            ->options(fn() => WarehouseItem::query()
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => !$get('manual_entry'))
                            ->required(fn($get) => !$get('manual_entry'))
                            ->columnSpan(5)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;
                                $unit = optional(WarehouseItem::find($state))->unit;
                                if ($unit) $set('unit', $unit);
                            }),

                        TextInput::make('item_name')
                            ->label('Nama Barang (Manual)')
                            ->placeholder('Tulis nama barang…')
                            ->visible(fn($get) => (bool) $get('manual_entry'))
                            ->required(fn($get) => (bool) $get('manual_entry'))
                            ->columnSpan(7),

                        TextInput::make('qty')
                            ->label('Jumlah')
                            ->numeric()
                            ->step('0.001')
                            ->minValue(0.001)
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('unit')
                            ->label('Satuan')
                            ->placeholder('kg / liter / pack')
                            ->columnSpan(3),

                        // TextInput::make('note')
                        //     ->label('Catatan')
                        //     ->columnSpan(6),
                    ]),
                ])
                ->columns(12)
                ->addActionLabel('Tambah Item')
                ->reorderable()
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->copyable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_time')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pembuat')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'Draft',
                        'success' => 'Submitted',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d-m-Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Submitted' => 'Submitted',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $v) => $q->whereDate('requested_at', '>=', $v))
                            ->when($data['until'] ?? null, fn($q, $v) => $q->whereDate('requested_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),

                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->status === 'Draft'),

                Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'Draft')
                    ->action(function (SppgPurchaseOrder $record) {
                        // (opsional) merge item duplikat di sini
                        $record->update(['status' => 'Submitted']);
                    }),

                Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'Submitted')
                    ->action(fn(SppgPurchaseOrder $record) => $record->update(['status' => 'Draft'])),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Info\Section::make('Informasi Umum')->columns(2)->schema([
                Info\TextEntry::make('po_number')->label('Nomor PO'),
                Info\TextEntry::make('requested_at')->label('Tanggal')->date('d-m-Y'),
                Info\TextEntry::make('delivery_time')->label('Jam')->dateTime('H:i'),
                Info\TextEntry::make('status')->badge(),
                Info\TextEntry::make('creator.name')->label('Pembuat'),
                Info\TextEntry::make('notes')->label('Catatan')->columnSpanFull(),
            ]),
            Info\Section::make('Daftar Item')->schema([
                Info\RepeatableEntry::make('items')->label('Items')->schema([
                    Info\TextEntry::make('warehouseItem.name')->label('Nama (Master)'),
                    Info\TextEntry::make('item_name')->label('Nama (Manual)'),
                    Info\TextEntry::make('qty')
                        ->label('Qty')
                        ->formatStateUsing(fn($state) => number_format((float) $state, 3, ',', '.')),

                    // Info\TextEntry::make('qty')->label('Qty')
                    //     ->formatStateUsing(fn($s) => number_format((float) $s, 3, ',', '.')),
                    Info\TextEntry::make('unit')->label('Satuan'),
                    Info\TextEntry::make('note')->label('Catatan'),
                ])->columns(5),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambah RelationManager items jika ingin edit item dari detail.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSppgPurchaseOrders::route('/'),
            'create' => Pages\CreateSppgPurchaseOrder::route('/create'),
            'edit' => Pages\EditSppgPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
