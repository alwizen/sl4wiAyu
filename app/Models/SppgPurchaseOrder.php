<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppgPurchaseOrder extends Model
{
    protected $table = 'sppg_purchase_orders';

    protected $fillable = [
        'po_number',
        'requested_at',
        'delivery_time',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'delivery_time' => 'datetime:H:i',
        'hub_synced_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SppgPurchaseOrderItem::class, 'sppg_purchase_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(?string $kitchenCode = null): string
    {
        $code = $kitchenCode ?? config('app.kitchen_code', env('KITCHEN_CODE', 'K-XX'));
        $today = now()->format('Y-m-d');

        $countToday = static::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $running = str_pad((string) ($countToday + 1), 5, '0', STR_PAD_LEFT);
        return "{$code}/{$today}/{$running}";
    }
}
