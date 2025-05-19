<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\WarehouseItem;
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
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section as ComponentsSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

use Filament\Tables\Columns\BadgeColumn;

class PurchaseOrderResource extends Resource implements HasShieldPermissions
// implements HasShieldPermissions
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';

    protected static ?string $navigationLabel = 'Pemesanan Bahan Baku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Order')
                            ->default(function () {
                                return PurchaseOrder::generateOrderNumber();
                            })
                            ->disabled()
                            ->dehydrated() // Pastikan nilai tetap disimpan
                            ->required(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Tanggal Pemesanan')
                            ->required()
                            ->default(now()),

                        Select::make('supplier_id')
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
                                'Paid' => 'Paid',
                            ])
                            ->default('Pending')
                            ->required(),
                    ])
                    ->columns(4),

                Card::make()
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Item Pembelian')
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(WarehouseItem::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        static::updateTotal($get, $set);
                                    }),

                                TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->default(0)
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

                Card::make()
                    ->schema([
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
            return $carry + (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0));
        }, 0);

        $set('total_amount', $total);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nomor Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('order_date')
                    ->date('d-m-Y'),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'Pending' => 'warning',
                            'Ordered' => 'primary',
                            'Approved' => 'success',
                            'Rejected' => 'danger',
                            'Paid' => 'info',
                            default => 'secondary',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR', locale: 'id_ID') // Format ke rupiah
                    ->summarize(Sum::make()->label('Total Seluruh')),

            ])
            ->filters([
                // filters can be added here
            ])
            ->actions([
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
                    }),
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->tooltip('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->infolist([
                        Section::make('Informasi Umum')
                            ->schema([
                                TextEntry::make('order_date')->label('Tanggal Pemesanan'),
                                TextEntry::make('supplier.name')->label('Nama Supplier'),
                                TextEntry::make('status')->label('Status'),
                                TextEntry::make('total_amount')
                                    ->label('Total')
                                    ->money('IDR', true),
                                TextEntry::make('rejection_note')
                                    ->label('Catatan Penolakan')
                                    ->hidden(fn($record) => $record->status !== 'Rejected'),
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

                Tables\Actions\EditAction::make(),
                    // ->visible(fn(PurchaseOrder $record) => $record->status === 'Pending'),
                    
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
                        // Ambil nomor telepon supplier
                        $phoneNumber = preg_replace('/[^0-9]/', '', $record->supplier->phone);

                        // Format nomor telepon ke format internasional Indonesia
                        if (strlen($phoneNumber) > 0) {
                            if (substr($phoneNumber, 0, 1) === '0') {
                                // Jika dimulai dengan 0, ganti dengan 62
                                $phoneNumber = '62' . substr($phoneNumber, 1);
                            }
                            // Jika nomor tidak dimulai dengan '62', tambahkan '62'
                            elseif (substr($phoneNumber, 0, 2) !== '62') {
                                $phoneNumber = '62' . $phoneNumber;
                            }
                        }

                        // Format pesan WhatsApp
                        $message = "**Purchase Order **" . "\n" . $record->order_number . "\n" .
                            "Tanggal: " . \Carbon\Carbon::parse($record->order_date)->format('d-m-Y') . "\n" .
                            "Supplier: " . $record->supplier->name . "\n\n" .
                            "📦 Daftar Barang:\n" .
                            $record->items->map(
                                fn($item) =>
                                "- " . $item->item->name . ": " . $item->quantity . " " . $item->item->unit . " x Rp " . number_format($item->unit_price, 0, ',', '.')
                            )->implode("\n") .
                            "\n\nTotal: Rp " . number_format($record->total_amount, 0, ',', '.');

                        // Encode pesan untuk URL WhatsApp
                        $encodedMessage = urlencode($message);

                        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
                    })
                    ->openUrlInNewTab()
                    ->visible(
                        fn($record) =>
                        $record->status === 'Approved' &&
                            auth()->user()?->can('send_whatsapp_purchase::order')
                    ),
                    // ->visible(fn(PurchaseOrder $record) => $record->status === 'Approved'),

                Tables\Actions\Action::make('mark_ordered')
                    ->label('Tandai Sudah kirim Wa')
                    ->tooltip('Tandai Sudah Dikirim')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn(PurchaseOrder $record) => $record->update(['status' => 'Ordered']))
                    // ->visible(fn($record) => $record->status === 'Approved'), 
                    ->visible(
                        fn($record) =>
                        $record->status === 'Approved' &&
                            auth()->user()?->can('mark_ordered_purchase::order')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(PurchaseOrder $record) => $record->status === 'Pending'),
            ])

            ->bulkActions([
                ExportBulkAction::make()
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
            'send_whatsapp', 
            'mark_ordered'
        ];
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
