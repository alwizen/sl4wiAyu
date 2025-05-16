<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssueItem extends Model
{
    protected $fillable = [
        'stock_issue_id',
        'warehouse_item_id',
        'requested_quantity',
        'issued_quantity',
    ];

    public function stockIssue(): BelongsTo
    {
        return $this->belongsTo(StockIssue::class);
    }

    public function warehouseItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class);
    }
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
