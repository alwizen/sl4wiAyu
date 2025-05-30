<?php
namespace App\Exports;

use App\Models\PurchaseOrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseOrderItemsExport implements FromCollection, WithHeadings
{
    protected $purchaseOrderIds;

    public function __construct($purchaseOrderIds)
    {
        $this->purchaseOrderIds = $purchaseOrderIds;
    }

    public function collection()
    {
        return PurchaseOrderItem::with(['purchaseOrder.supplier', 'item'])
            ->whereIn('purchase_order_id', $this->purchaseOrderIds)
            ->get()
            ->map(function ($item) {
                return [
                    'Nomor Order' => $item->purchaseOrder->order_number ?? '-',
                    'Tanggal Pemesanan' => $item->purchaseOrder->order_date ?? '-',
                    'Supplier' => $item->purchaseOrder->supplier->name ?? '-',
                    'Status Order' => $item->purchaseOrder->status ?? '-',
                    'Status Pembayaran' => $item->purchaseOrder->payment_status ?? '-',
                    'Item' => $item->item->name ?? '-',
                    'Jumlah' => $item->quantity,
                    'Harga Satuan' => $item->unit_price,
                    'Total' => (float) $item->quantity * (float) $item->unit_price,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nomor Order',
            'Tanggal Pemesanan',
            'Supplier',
            'Status Order',
            'Status Pembayaran',
            'Item',
            'Jumlah',
            'Harga Satuan',
            'Total',
        ];
    }
}

