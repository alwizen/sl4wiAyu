<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PurchaseOrderReceivingHistory extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.purchase-order-receiving-history';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Histori Penerimaan PO';

    protected function getTableQuery(): Builder
    {
        return PurchaseOrder::with(['supplier', 'receivings.stockReceivingItems.warehouseItem']);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('order_number')->label('No. PO'),
            TextColumn::make('supplier.name')->label('Supplier'),


            TextColumn::make('receivings')
                ->label('Penerimaan & Sisa')
                ->formatStateUsing(function ($state, $record) {
                    // Ambil semua item PO beserta qty yang dipesan
                    $poItems = $record->items; // asumsi relasi PO ke items sudah ada

                    // Ambil semua penerimaan dan hitung total qty diterima per item
                    $receivedQuantities = [];

                    foreach ($record->receivings as $receiving) {
                        foreach ($receiving->stockReceivingItems as $receivedItem) {
                            $id = $receivedItem->warehouse_item_id;
                            $receivedQuantities[$id] = ($receivedQuantities[$id] ?? 0) + $receivedItem->received_quantity;
                        }
                    }

                    $html = '<ul>';

                    foreach ($poItems as $poItem) {
                        $id = $poItem->item_id;  // sesuaikan dengan field id item di PO
                        $name = $poItem->item->name ?? 'Unknown Item';
                        $orderedQty = $poItem->quantity; // qty di PO
                        $receivedQty = $receivedQuantities[$id] ?? 0;
                        $remainingQty = $orderedQty - $receivedQty;

                        $html .= "<li><strong>{$name}</strong>: Dipesan {$orderedQty}, Diterima {$receivedQty}, Sisa <span style='color:" . ($remainingQty > 0 ? 'red' : 'green') . "'>{$remainingQty}</span></li>";
                    }

                    $html .= '</ul>';

                    return $html;
                })
                ->html()
                ->wrap()
        ];
    }
}
