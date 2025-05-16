<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'item_id', 'quantity', 'unit_price'];

    public function purchaseOrder():BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item():BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class);
    }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
