<?php

namespace App\Filament\Resources;

use App\Exports\PurchaseOrderItemsExport;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers\ReceivingsRelationManager;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\WarehouseItem;
use App\Models\User;
use App\Notifications\PurchaseOrderApproved;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class PurchaseOrderResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';

    protected static ?string $navigationLabel = 'Pemesanan Supplier (PO)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Tambahkan hidden field untuk created_by
                Hidden::make('created_by')
                    ->default(auth()->id())
                    ->dehydrated(),

                Card::make('Informasi Umum')
                    ->collapsible()
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Order')
                            ->default(function () {
                                return PurchaseOrder::generateOrderNumber();
                            })
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Tanggal Pemesanan')
                            ->required()
                            ->default(now()),

                        Select::make('supplier_id')
                            ->required()
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->label('Nama Supplier')->required(),
                                TextInput::make('address')->label('Alamat')->required(),
                                TextInput::make('phone')->label('No. Telepon')->required(),
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Pending' => 'Pending',
                                'Ordered' => 'Ordered',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                            ])
                            ->default('Pending')
                            ->required(),
                    ])
                    ->columns(4),

                Card::make('Daftar Item Pembelian')
                    ->collapsible()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(WarehouseItem::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->debounce(500)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        static::updateTotal($get, $set);
                                    }),

                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->default(0)
                                    ->debounce(500)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        static::updateTotal($get, $set);
                                    }),
                            ])
                            ->columns(3)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                static::updateTotal($get, $set);
                            }),
                    ]),

                Card::make('Informasi Pembayaran')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'Paid' => 'Lunas',
                                'Unpaid' => 'Belum Lunas',
                            ])
                            ->default('Unpaid'),

                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ]),
            ]);
    }

    protected static function updateTotal(callable $get, callable $set): void
    {
        $items = $get('items') ?? [];

        $total = collect($items)->reduce(function ($carry, $item) {
            return $carry + ((float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0));
        }, 0);

        $set('total_amount', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nomor Order')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('order_date')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'Pending' => 'warning',
                            'Ordered' => 'primary',
                            'Approved' => 'success',
                            'Rejected' => 'danger',
                            default => 'secondary',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->label('Pembayaran')
                    ->colors([
                        'success' => 'Paid',
                        'warning' => 'Partially Paid',
                        'danger' => 'Unpaid',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR', locale: 'id_ID')
                    ->summarize(Sum::make()->label('Total Seluruh')),
            ])
            ->filters([
                Filter::make('order_date_range')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('order_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('order_date', '<=', $data['until']));
                    }),

                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'Pending' => 'Pending',
                                'Ordered' => 'Ordered',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                            ])
                            ->placeholder('Pilih status'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['status'], fn($q) => $q->where('status', $data['status']));
                    }),

                Filter::make('payment_status')
                    ->form([
                        Select::make('payment_status')
                            ->options([
                                'Paid' => 'Lunas',
                                'Unpaid' => 'Belum Lunas',
                            ])
                            ->placeholder('Pilih status pembayaran'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['payment_status'], fn($q) => $q->where('payment_status', $data['payment_status']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->color('warning')
                    ->icon('heroicon-o-printer')
                    ->url(fn(PurchaseOrder $record) => route('purchase-orders.print', $record))
                    ->openUrlInNewTab()
                    ->visible(fn(PurchaseOrder $record) => $record->payment_status === 'Paid'),

                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->color('secondary')
                    ->tooltip('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->infolist([
                        Section::make('Informasi Umum')
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Nomor Order'),
                                TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh'),
                                TextEntry::make('total_amount')
                                    ->label('Total')
                                    ->money('IDR', true),
                            ]),

                        Section::make('Daftar Item Pembelian')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->label('Item')
                                    ->schema([
                                        TextEntry::make('item.name')->label('Nama Item'),
                                        TextEntry::make('quantity')->label('Jumlah'),
                                        TextEntry::make('unit_price')
                                            ->label('Harga Satuan')
                                            ->money('IDR', true),
                                    ])
                                    ->columns(3),
                            ]),
                    ])
                    ->slideOver(),
                ActionGroup::make([
                    RelationManagerAction::make('stockReceivingItemsHistory')
                        ->label('Riwayat Penerimaan Stok')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->relationManager(ReceivingsRelationManager::make()),

                    Tables\Actions\Action::make('mark_paid')
                        ->label('Tandai Lunas')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(fn($record) => $record->status === 'Approved' && $record->payment_status !== 'Paid')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'payment_status' => 'Paid',
                                'payment_date' => now(),
                            ]);
                        }),

                    Tables\Actions\Action::make('mark_unpaid')
                        ->label('Tandai Belum Lunas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn($record) => $record->status === 'Approved' && $record->payment_status !== 'Unpaid')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'payment_status' => 'Unpaid',
                                'payment_date' => null,
                            ]);
                        }),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status === 'Pending' &&
                                auth()->user()?->can('approve_purchase::order')
                        )
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'Approved']);

                            if ($record->creator) {
                                $record->creator->notify(new \App\Notifications\PurchaseOrderApproved($record));
                            }

                            // Tetap tampilkan pesan sukses lokal (jika mau)
                            Notification::make()
                                ->title('Purchase Order Disetujui')
                                ->body("PO {$record->order_number} berhasil disetujui dan notifikasi telah dikirim.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('rejected')
                        ->label('Tolak / Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            $record->status === 'Pending' &&
                                auth()->user()?->can('rejected_purchase::order')
                        )
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => 'Rejected']);

                            Notification::make()
                                ->title('Purchase Order Ditolak / Batal')
                                ->body("PO {$record->order_number} ditolak / batal.")
                                ->success()
                                ->send();
                        }),


                    Tables\Actions\Action::make('Send to WhatsApp')
                        ->label('Kirim ke WA')
                        ->tooltip('Kirim Data Pesanan ke Supplier')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (PurchaseOrder $record) {
                            $record->update(['status' => 'Ordered']);
                        })
                        ->url(function (PurchaseOrder $record) {
                            $phoneNumber = preg_replace('/[^0-9]/', '', $record->supplier->phone);

                            if (strlen($phoneNumber) > 0) {
                                if (substr($phoneNumber, 0, 1) === '0') {
                                    $phoneNumber = '62' . substr($phoneNumber, 1);
                                } elseif (substr($phoneNumber, 0, 2) !== '62') {
                                    $phoneNumber = '62' . $phoneNumber;
                                }
                            }

                            $message = "**Purchase Order **" . "\n" . $record->order_number . "\n" .
                                "Tanggal: " . \Carbon\Carbon::parse($record->order_date)->format('d-m-Y') . "\n" .
                                "Supplier: " . $record->supplier->name . "\n\n" .
                                "📦 Daftar Barang:\n" .
                                $record->items->map(
                                    fn($item) =>
                                    "- " . $item->item->name . ": " . $item->quantity . " " . $item->item->unit . " x Rp " . number_format($item->unit_price, 0, ',', '.')
                                )->implode("\n") .
                                "\n\nTotal: Rp " . number_format($record->total_amount, 0, ',', '.');

                            $encodedMessage = urlencode($message);

                            return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
                        })
                        ->openUrlInNewTab()
                        ->visible(
                            fn($record) =>
                            $record->status === 'Approved' &&
                                auth()->user()?->can('send_whatsapp_purchase::order')
                        ),
                    Tables\Actions\EditAction::make(),
                    // Tables\Actions\DeleteAction::make()
                ])
                    ->button()
                    ->label('Aksi')
                    ->icon('heroicon-o-bars-3-bottom-left')
            ])
            ->bulkActions([
                BulkAction::make('export-selected')
                    ->label('Ekspor PO Item')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        $ids = $records->pluck('id');
                        $timestamp = Carbon::now()->format('Ymd_His');

                        return Excel::download(
                            new PurchaseOrderItemsExport($ids),
                            "purchase-order-items_{$timestamp}.xlsx"
                        );
                    }),
            ]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'publish',
            'approve',
            'rejected',
            'send_whatsapp',
            'mark_ordered'
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReceivingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
