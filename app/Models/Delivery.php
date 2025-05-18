<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_number',
        'delivery_date',
        'recipient_id',
        'status',
        'qty',
        'prepared_at',
        'shipped_at',
        'returned_at',
        'received_qty'
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function isPacking(): bool
    {
        return $this->status === 'dikemas';
    }
    public function isDelivered(): bool
    {
        return $this->status === 'terkirim';
    }
    public function isInTransit(): bool
    {
        return $this->status === 'dalam_perjalanan';
    }
    public function isReturn(): bool
    {
        return $this->status === 'kembali';
    }
    public function markAsDelivered(): void
    {
        $this->update(['status' => 'terkirim']);
        $this->save();
    }

    protected $hidden = ['created_at', 'updated_at'];
}
