<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderReceivingHistory extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    use HasPageShield;

    protected static string $view = 'filament.pages.purchase-order-receiving-history';
    protected static ?string $navigationGroup = 'Pengadaan & Permintaan';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $title = 'Histori Penerimaan PO';

    protected function getTableQuery(): Builder
    {
        return PurchaseOrder::with([
            'supplier',
            'items.item',
            'receivings.stockReceivingItems',
        ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('order_date')
                ->label('Tanggal PO')
                ->dateTime('d/m/Y')
                ->sortable(),

            TextColumn::make('order_number')
                ->label('No. PO')
                ->searchable(),

            TextColumn::make('supplier.name')
                ->label('Supplier'),

            TextColumn::make('receivings')
                ->label('Update Penerimaan')
                ->formatStateUsing(function ($state, $record) {
                    $record->loadMissing('items.item', 'receivings.stockReceivingItems');

                    // Mapping PO total quantity
                    $poItemQuantities = [];
                    foreach ($record->items as $item) {
                        $poItemQuantities[$item->item_id] = [
                            'name' => $item->item->name ?? 'Unknown Item',
                            'quantity' => $item->quantity,
                        ];
                    }

                    $receivedByDate = [];

                    foreach ($record->receivings as $receiving) {
                        $date = optional($receiving->received_date)?->format('d/m/Y') ?? '-';

                        foreach ($receiving->stockReceivingItems as $item) {
                            $id = $item->warehouse_item_id;
                            $name = $item->warehouseItem->name ?? 'Unknown Item';
                            $qty = $item->received_quantity;
                            $totalPoQty = collect($record->items)->firstWhere('item_id', $id)?->quantity ?? 0;

                            $receivedByDate[$date][$name] = [
                                'received' => ($receivedByDate[$date][$name]['received'] ?? 0) + $qty,
                                'total' => $totalPoQty,
                            ];
                        }
                    }

                    $html = '<ul>';
                    foreach ($receivedByDate as $date => $items) {
                        $html .= "<li><strong>{$date}</strong><ul>";
                        foreach ($items as $name => $data) {
                            $html .= "<li>{$name}: {$data['received']} dari total PO {$data['total']}</li>";
                        }
                        $html .= '</ul></li>';
                    }
                    $html .= '</ul>';

                    return $html;
                })
                ->html()
                ->wrap(),

            TextColumn::make('items')
                ->label('Status Jumlah')
                ->formatStateUsing(function ($state, $record) {
                    $record->loadMissing('items.item', 'receivings.stockReceivingItems');

                    // Hitung total penerimaan per item berdasarkan ID
                    $receivedQuantities = [];

                    foreach ($record->receivings as $receiving) {
                        foreach ($receiving->stockReceivingItems as $item) {
                            $id = $item->warehouse_item_id;
                            $receivedQuantities[$id] = ($receivedQuantities[$id] ?? 0) + $item->received_quantity;
                        }
                    }

                    $html = '<ul>';

                    foreach ($record->items as $poItem) {
                        $id = $poItem->item_id;
                        $name = $poItem->item->name ?? 'Unknown Item';
                        $ordered = $poItem->quantity;
                        $received = $receivedQuantities[$id] ?? 0;
                        $status = '';
                        $color = '';

                        if ($received < $ordered) {
                            $status = 'kurang ' . ($ordered - $received);
                            $color = 'red';
                        } elseif ($received > $ordered) {
                            $status = 'lebih ' . ($received - $ordered);
                            $color = 'green';
                        } else {
                            $status = 'sesuai/pas';
                            $color = 'green';
                        }

                        $html .= "<li><strong>{$name}</strong>: <span style='color:{$color}'>{$status}</span></li>";
                    }

                    $html .= '</ul>';

                    return $html;
                })
                ->html()
                ->wrap(),

        ];
    }
}
