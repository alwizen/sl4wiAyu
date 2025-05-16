<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 'total_amount', 'status','order_date','order_number'];
    
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'order_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Event creating akan dijalankan saat model baru dibuat
        static::creating(function ($purchaseOrder) {
            // Generate order number jika belum diisi
            if (!$purchaseOrder->order_number) {
                $purchaseOrder->order_number = self::generateOrderNumber();
            }
        });
    }

    // Method untuk generate nomor order otomatis
    public static function generateOrderNumber()
    {
        // Format: DDMMYYYYXXX (Tanggal-Bulan-Tahun + 3 karakter random)
        $date = now()->format('dmY');
        
        // 3 karakter random (huruf dan angka)
        $random = strtoupper(Str::random(3));
        
        $orderNumber = $date . $random;
        
        // Cek jika nomor sudah ada, generate ulang
        while (self::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(Str::random(3));
            $orderNumber = $date . $random;
        }
        
        return $orderNumber;
    }
}
