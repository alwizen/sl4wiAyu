<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceivingItem extends Model
{
    protected $fillable = [
        'stock_receiving_id',
        'warehouse_item_id',
        'expected_quantity',
        'received_quantity',
        'good_quantity',
        'damaged_quantity',
        'is_quantity_matched',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'good_quantity'     => 'decimal:2',
        'damaged_quantity'  => 'decimal:2',
        'is_quantity_matched' => 'boolean',
    ];


    public function stockReceiving()
    {
        return $this->belongsTo(StockReceiving::class);
    }

    public function warehouseItem()
    {
        return $this->belongsTo(WarehouseItem::class);
    }

    protected static function booted()
    {
        static::created(function (StockReceivingItem $item) {
            // Memastikan warehouseItem ada sebelum mencoba memperbarui stok
            if ($item->warehouseItem) {
                $item->warehouseItem->stock += $item->received_quantity;
                $item->warehouseItem->save();
            }
        });

        static::updated(function (StockReceivingItem $item) {
            // Jika quantity berubah, perbarui stok sesuai dengan perubahan
            if ($item->isDirty('received_quantity') && $item->warehouseItem) {
                $originalQuantity = $item->getOriginal('received_quantity');
                $newQuantity = $item->received_quantity;
                $difference = $newQuantity - $originalQuantity;

                $item->warehouseItem->stock += $difference;
                $item->warehouseItem->save();
            }
        });

        static::deleted(function (StockReceivingItem $item) {
            // Jika item dihapus, kurangi stok
            if ($item->warehouseItem) {
                $item->warehouseItem->stock -= $item->received_quantity;
                $item->warehouseItem->save();
            }
        });
    }
}
