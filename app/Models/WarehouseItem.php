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

    protected $casts = [
        'stock' => 'decimal:2',
    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(WarehouseCategory::class, 'warehouse_category_id');
    }

    public function stockReceivingItems(): HasMany
    {
        return $this->hasMany(StockReceivingItem::class);
    }

    // public function updateStock($warehouseItemId, $receivedQuantity)
    // {
    //     $item = WarehouseItem::find($warehouseItemId);
    //     $item->quantity += $receivedQuantity;
    //     $item->save();
    // }
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }

    public function stockIssueItems(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }
}
