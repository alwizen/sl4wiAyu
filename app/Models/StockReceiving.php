<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceiving extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'received_date',
        'note',
    ];

    protected static function boot()
    {
        parent::boot();

        // Saat data penerimaan dihapus
        static::deleting(function ($receiving) {
            foreach ($receiving->stockReceivingItems as $item) {
                $warehouseItem = $item->warehouseItem;

                if ($warehouseItem) {
                    // Kurangi stok sesuai jumlah yang diterima
                    $warehouseItem->stock -= $item->received_quantity;
                    $warehouseItem->save();
                }
            }

            // Hapus juga item terkait agar tidak orphan
            $receiving->stockReceivingItems()->delete();
        });
    }

    protected $casts = [
        'received_date' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }


    // Relasi ke item penerimaan
    public function stockReceivingItems()
    {
        return $this->hasMany(StockReceivingItem::class);
    }

    // Relasi ke item gudang melalui item penerimaan
    public function warehouseItems()
    {
        return $this->hasManyThrough(
            WarehouseItem::class,
            StockReceivingItem::class,
            'stock_receiving_id',   // FK di stock_receiving_items
            'id',                   // PK di warehouse_items
            'id',                   // PK di stock_receivings
            'warehouse_item_id'     // FK di stock_receiving_items
        );
    }
}
