<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'category_id',
        'purchase_order_id',
        'amount',
        'description',
        'methode',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class);
    }
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
