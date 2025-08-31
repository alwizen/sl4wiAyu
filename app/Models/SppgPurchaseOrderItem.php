<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppgPurchaseOrderItem extends Model
{
    protected $table = 'sppg_purchase_order_items';

    protected $fillable = [
        'sppg_purchase_order_id',
        'warehouse_item_id',
        'item_name',
        'qty',
        'unit',
        'note',
        'delivery_time_item'
    ];

    protected $casts = ['qty' => 'decimal:3'];

    public function po(): BelongsTo
    {
        return $this->belongsTo(SppgPurchaseOrder::class, 'sppg_purchase_order_id');
    }

    public function warehouseItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class);
    }
}
