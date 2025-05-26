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
        'received_qty',
        'user_id',
        'proof_delivery',
        'returned_qty',
        'car_id',
        'short_code'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    // dikemas → disiapkan → dalam perjalanan → terkirim → selesai
    public function isPacking(): bool
    {
        return $this->status === 'dikemas';
    }
    public function isReady(): bool
    {
        return $this->status === 'disiapkan';

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
        return $this->status === 'selesai';
    }
    public function markAsDelivered(): void
    {
        $this->update(['status' => 'terkirim']);
        $this->save();
    }

    protected $hidden = ['created_at', 'updated_at'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate unique short code
            do {
                $model->short_code = self::generateShortCode();
            } while (static::where('short_code', $model->short_code)->exists());
        });
    }

    /**
     * Generate random short code
     */
    private static function generateShortCode(): string
    {
        // Kombinasi huruf dan angka, hindari karakter yang mirip (0, O, I, l)
        $characters = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
        return substr(str_shuffle($characters), 0, 6);
    }

    /**
     * Get short URL
     */
    public function getShortUrlAttribute(): string
    {
        return config('app.url') . '/s/' . $this->short_code;
    }
}
