<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceiving extends Model
{
    protected $fillable = [
        'purchase_order_id',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Relasi ke StockReceivingItem
    public function stockReceivingItems()
    {
        return $this->hasMany(StockReceivingItem::class);
    }

    public function warehouseItems()
    {
        return $this->hasManyThrough(
            WarehouseItem::class,
            StockReceivingItem::class,
            'stock_receiving_id', // FK di stock_receiving_items
            'id', // PK di warehouse_items
            'id', // PK di stock_receivings
            'warehouse_item_id' // FK di stock_receiving_items
        );
    }
}
