<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SppgPurchaseOrderResource\Pages;
use App\Jobs\PushPoToHubJob;
use App\Models\SppgPurchaseOrder;
use App\Models\WarehouseItem;
use App\Services\HubClient;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Schema;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components as Info;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class SppgPurchaseOrderResource extends Resource
{
    protected static ?string $model = SppgPurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';

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
                            ->step('0.01')
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('unit')
                            ->label('Satuan')
                            ->placeholder('kg / liter / pack')
                            ->columnSpan(3),
                    ]),
                ])
                ->columns(3)
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
                    // ->time('H:i')
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

                // Action::make('submit')
                //     ->label('Submit')
                //     ->icon('heroicon-o-paper-airplane')
                //     ->color('success')
                //     ->requiresConfirmation()
                //     ->visible(fn($record) => $record->status === 'Draft')
                //     ->action(function (SppgPurchaseOrder $record) {
                //         // (opsional) merge item duplikat di sini
                //         $record->update(['status' => 'Submitted']);
                //     }),
                // Action::make('submit')
                //     ->label('Submit ke Hub')
                //     ->color('primary')
                //     ->icon('heroicon-o-paper-airplane')
                //     ->requiresConfirmation()
                //     ->visible(fn($record) => $record->status === 'Draft')
                //     ->action(function (\App\Models\SppgPurchaseOrder $record) {
                //         // ganti status lokal
                //         $record->status = 'Submitted';
                //         $record->save();

                //         // kirim ke Hub via Job (non-blocking)
                //         // dispatch(new PushPoToHubJob($record->id));

                //         // kalau mau langsung tanpa queue:
                //         // (new \App\Services\HubClient)::submitIntake(...susun payload...);

                //         \Filament\Notifications\Notification::make()
                //             ->title('PO disubmit')
                //             ->body('Sedang dikirim ke Hub...')
                //             ->success()
                //             ->send();
                //     }),
                Action::make('submitToHub')
                    ->label('Submit ke Hub')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn(SppgPurchaseOrder $record) => $record->status === 'Draft')
                    ->action(function (SppgPurchaseOrder $record) {
                        // siapkan relasi yang dibutuhkan untuk payload
                        $record->loadMissing(['items.warehouseItem', 'creator']);

                        $time = $record->delivery_time;
                        if ($time) {
                            try {
                                // Terima HH:MM atau HH:MM:SS → kirim HH:MM
                                $time = Carbon::parse($time)->format('H:i');
                            } catch (\Throwable $e) {
                                $time = null;
                            }
                        }

                        // susun payload sesuai controller Hub kita
                        $payload = [
                            'po_number'     => $record->po_number,
                            'requested_at'  => optional($record->requested_at)->toDateString(),
                            'delivery_time' => $time, // <-- SUDAH H:i
                            'submitted_at'  => Carbon::now('UTC')->toIso8601String(),
                            'notes'         => $record->notes,
                            'items' => $record->items->map(function ($it) {
                                return [
                                    'id' => $it->id,
                                    'warehouse_item_id' => $it->warehouse_item_id,
                                    'qty'  => (string) $it->qty,
                                    'unit' => $it->unit ?? optional($it->warehouseItem)->unit ?? 'unit',
                                    'warehouse_item' => [
                                        'name' => optional($it->warehouseItem)->name ?? $it->item_name ?? 'N/A',
                                        'unit' => optional($it->warehouseItem)->unit ?? $it->unit ?? 'unit',
                                    ],
                                    'note' => $it->note,
                                ];
                            })->values()->all(),
                            'external' => [
                                'sppg_po_id'   => $record->id,
                                'creator_id'   => $record->created_by,
                                'creator_name' => optional($record->creator)->name,
                            ],
                        ];

                        try {
                            $resp = HubClient::submitIntake($payload);

                            // update status lokal + jejak (jika kolom tracking sudah kamu buat)
                            $record->status = 'Submitted';

                            if (Schema::hasColumn($record->getTable(), 'hub_intake_id')) {
                                $record->hub_intake_id = $resp['intake_id'] ?? null;
                            }
                            if (Schema::hasColumn($record->getTable(), 'hub_synced_at')) {
                                $record->hub_synced_at = Carbon::now();
                            }
                            if (Schema::hasColumn($record->getTable(), 'hub_last_error')) {
                                $record->hub_last_error = null;
                            }

                            $record->save();

                            Notification::make()
                                ->title('Berhasil dikirim ke Hub')
                                ->body('PO ' . $record->po_number . ' → status Hub: ' . ($resp['status'] ?? 'Received'))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            // simpan error jika kolom ada, tapi jangan bikin gagal fatal
                            if (Schema::hasColumn($record->getTable(), 'hub_last_error')) {
                                $record->hub_last_error = $e->getMessage();
                                $record->save();
                            }

                            Notification::make()
                                ->title('Gagal kirim ke Hub')
                                ->body(mb_strimwidth($e->getMessage(), 0, 300, '…'))
                                ->danger()
                                ->send();

                            // OPTIONAL: kalau mau revert status ke Draft saat gagal:
                            // $record->update(['status' => 'Draft']);
                        }
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
                        ->formatStateUsing(fn($state) => number_format((float) $state, 2, ',')),

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
