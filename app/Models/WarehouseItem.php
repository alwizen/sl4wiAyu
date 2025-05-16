<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseItem extends Model
{
    protected $fillable = [
        'warehouse_category_id',
        'name',
        'unit',
        'stock',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'warehouse_category_id');
    }

    public function stockReceivingItems(): HasMany
    {
        return $this->hasMany(StockReceivingItem::class);
    }
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    // public function updateStock($warehouseItemId, $receivedQuantity)
    // {
    //     $item = WarehouseItem::find($warehouseItemId);
    //     $item->quantity += $receivedQuantity;
    //     $item->save();
    // }
}
