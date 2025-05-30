<?php
namespace App\Exports;

use App\Models\StockReceivingItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReceivingItemsExport implements FromCollection, WithHeadings
{
    protected $stockReceivingIds;

    public function __construct($stockReceivingIds)
    {
        $this->stockReceivingIds = $stockReceivingIds;
    }

    public function collection()
    {
        return StockReceivingItem::with(['stockReceiving.purchaseOrder.supplier', 'warehouseItem'])
            ->whereIn('stock_receiving_id', $this->stockReceivingIds)
            ->get()
            ->map(function ($item) {
                return [
                    'Tanggal Penerimaan' => $item->stockReceiving->received_date ?? '-',
                    'Nomor PO' => $item->stockReceiving->purchaseOrder->order_number ?? '-',
                    'Supplier' => $item->stockReceiving->purchaseOrder->supplier->name ?? '-',
                    'Item Gudang' => $item->warehouseItem->name ?? '-',
                    'Jumlah Diterima' => $item->received_quantity,
                    'Catatan' => $item->stockReceiving->note ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal Penerimaan',
            'Nomor PO',
            'Supplier',
            'Item Gudang',
            'Jumlah Diterima',
            'Catatan',
        ];
    }
}
