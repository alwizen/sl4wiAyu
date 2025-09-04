<?php

namespace App\Filament\Pages;

use App\Services\HubClient;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Actions;
use Filament\Notifications\Notification;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Support\Arr;

class VerifyReceipts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'SPPG';
    protected static ?string $navigationLabel = 'Verifikasi Timbangan';
    protected static string $view = 'filament.pages.verify-receipts';

    // state filter (ditampilkan di form atas)
    public ?array $filter = [
        'po_number' => null,
        'only_unverified' => true,
    ];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('filter.po_number')
                    ->label('PO Number (opsional)')
                    ->placeholder('SPPG-SLAWI/2025-08-23/00001'),

                Forms\Components\Toggle::make('filter.only_unverified')
                    ->label('Hanya yang belum diverifikasi')
                    ->default(true),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('preview')
                        ->label('Lihat jumlah item siap verifikasi')
                        ->icon('heroicon-o-magnifying-glass')
                        ->action(function () {
                            $res = HubClient::fetchOpenReceiptItems(
                                $this->filter['po_number'] ?? null,
                                (bool) ($this->filter['only_unverified'] ?? true),
                            );
                            $count = (int) ($res['count'] ?? 0);
                            Notification::make()
                                ->title("Ditemukan {$count} item untuk diverifikasi")
                                ->success()
                                ->send();
                        }),
                ])->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verifyAndSend')
                ->label('Muat & Verifikasi → Kirim ke Hub')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalSubmitActionLabel('Kirim ke Hub')
                ->form(function () {
                    // ambil data langsung saat modal dibuka
                    $res = HubClient::fetchOpenReceiptItems(
                        $this->filter['po_number'] ?? null,
                        (bool) ($this->filter['only_unverified'] ?? true),
                    );

                    $rows = collect($res['data'] ?? [])->map(function ($r) {
                        return [
                            'supplier_order_item_id' => $r['supplier_order_item_id'],
                            'po_number'   => $r['po_number'] ?? '-',
                            'supplier'    => $r['supplier'] ?? '-',
                            'item_name'   => $r['item_name'] ?? '-',
                            'unit'        => $r['unit'] ?? '-',
                            'qty_allocated' => $r['qty_allocated'] ?? null,
                            'qty_real'      => $r['qty_real'] ?? null,
                            'price'         => $r['price'] ?? null,

                            // input:
                            'verified_qty'   => $r['verified_qty'] ?? null,
                            'note'           => null,
                        ];
                    })->values()->all();

                    if (empty($rows)) {
                        return [
                            Forms\Components\Placeholder::make('info')
                                ->content('Tidak ada item untuk diverifikasi dengan filter saat ini.')
                                ->columnSpanFull(),
                        ];
                    }

                    return [
                        TableRepeater::make('rows')
                            ->label('Item untuk Diverifikasi')
                            ->default($rows)
                            // ->minItems(1)
                            // ->columns(8)
                            ->schema([
                                Forms\Components\Hidden::make('supplier_order_item_id'),

                                Forms\Components\TextInput::make('po_number')->label('PO')
                                    ->disabled()->dehydrated(false),
                                // Forms\Components\TextInput::make('supplier')->label('Supplier')
                                //     ->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('item_name')->label('Item')
                                    ->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('unit')->label('Sat')
                                    ->disabled()->dehydrated(false)->extraInputAttributes(['style' => 'width:70px']),

                                // Forms\Components\TextInput::make('qty_allocated')->label('Dialokasikan')
                                //     ->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('qty_real')->label('Qty Real')
                                    ->disabled()->dehydrated(false),
                                // Forms\Components\TextInput::make('price')->label('Harga')
                                // ->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('verified_qty')
                                    ->label('Verified Qty')
                                    ->numeric()->minValue(0)->step('0.001')
                                    ->required(),

                                Forms\Components\TextInput::make('note')
                                    ->label('Catatan')->columnSpanFull(),
                            ])
                            ->grid(1),
                    ];
                })
                ->action(function (array $data, Actions\Action $action) {
                    $items = collect($data['rows'] ?? [])
                        ->filter(fn($r) => filled($r['supplier_order_item_id']) && filled($r['verified_qty']))
                        ->map(fn($r) => [
                            'supplier_order_item_id' => $r['supplier_order_item_id'],
                            'verified_qty' => (string) $r['verified_qty'],
                            'note'         => $r['note'] ?? null,
                        ])
                        ->values()
                        ->all();

                    if (empty($items)) {
                        $action->failureNotificationTitle('Isi minimal satu Verified Qty.');
                        $action->failure();
                        return;
                    }

                    HubClient::submitReceipt([
                        'reference'    => 'GRN-' . now()->format('Ymd-His'),
                        'delivered_at' => now()->toIso8601String(),
                        'items'        => $items,
                        'external'     => ['weigher_name' => auth()->user()->name ?? 'SPPG'],
                    ]);

                    $action->successNotificationTitle('Verifikasi terkirim ke Hub.');
                    $action->success();
                }),
        ];
    }
}
