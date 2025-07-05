<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 'total_amount', 'status', 'order_date', 'order_number', 'payment_status', 'payment_date', 'created_by', 'is_received_complete'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivings(): HasMany
    {
        return $this->hasMany(StockReceiving::class);
    }

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

    /**
     * Get delivery status summary
     */
    public function getDeliveryStatusAttribute(): string
    {
        $receivings = $this->receivings;

        if ($receivings->isEmpty()) {
            return 'Not Started';
        }

        // Calculate total received per item
        $receivedTotals = [];
        foreach ($receivings as $receiving) {
            foreach ($receiving->stockReceivingItems as $item) {
                $warehouseItemId = $item->warehouse_item_id;
                if (!isset($receivedTotals[$warehouseItemId])) {
                    $receivedTotals[$warehouseItemId] = 0;
                }
                $receivedTotals[$warehouseItemId] += $item->received_quantity;
            }
        }

        $isComplete = true;
        $hasOverDelivery = false;
        $hasPartial = false;

        foreach ($this->items as $orderItem) {
            $orderedQty = $orderItem->quantity;
            $receivedQty = $receivedTotals[$orderItem->item_id] ?? 0;

            if ($receivedQty > $orderedQty) {
                $hasOverDelivery = true;
            } elseif ($receivedQty < $orderedQty) {
                $isComplete = false;
                if ($receivedQty > 0) {
                    $hasPartial = true;
                }
            }
        }

        if ($hasOverDelivery) {
            return 'Over Delivery';
        } elseif ($isComplete) {
            return 'Complete';
        } elseif ($hasPartial) {
            return 'Partial';
        } else {
            return 'Not Started';
        }
    }

    /**
     * Get total delivery progress percentage
     */
    public function getDeliveryProgressAttribute(): float
    {
        $totalOrdered = $this->items->sum('quantity');

        if ($totalOrdered == 0) {
            return 0;
        }

        $totalReceived = 0;
        foreach ($this->receivings as $receiving) {
            $totalReceived += $receiving->stockReceivingItems->sum('received_quantity');
        }

        return min(100, ($totalReceived / $totalOrdered) * 100);
    }

    /**
     * Check if all items have been fully delivered
     */
    public function isFullyDelivered(): bool
    {
        return $this->delivery_status === 'Complete';
    }

    /**
     * Check if PO has partial delivery (some items received but not complete)
     */
    public function hasPartialDelivery(): bool
    {
        return in_array($this->delivery_status, ['Partial', 'Over Delivery']);
    }

    /**
     * Check if PO is available for receiving (not fully delivered)
     */
    public function isAvailableForReceiving(): bool
    {
        return !$this->isFullyDelivered() || $this->hasPartialDelivery();
    }

    /**
     * Get items with their delivery progress
     */
    public function getItemsWithProgress(): \Illuminate\Support\Collection
    {
        // Calculate total received per item
        $receivedTotals = [];
        foreach ($this->receivings as $receiving) {
            foreach ($receiving->stockReceivingItems as $item) {
                $warehouseItemId = $item->warehouse_item_id;
                if (!isset($receivedTotals[$warehouseItemId])) {
                    $receivedTotals[$warehouseItemId] = 0;
                }
                $receivedTotals[$warehouseItemId] += $item->received_quantity;
            }
        }

        return $this->items->map(function ($orderItem) use ($receivedTotals) {
            $receivedQty = $receivedTotals[$orderItem->item_id] ?? 0;
            $remainingQty = $orderItem->quantity - $receivedQty;
            $progressPercentage = $orderItem->quantity > 0 ? ($receivedQty / $orderItem->quantity) * 100 : 0;

            return (object) [
                'item' => $orderItem->item,
                'ordered_quantity' => $orderItem->quantity,
                'received_quantity' => $receivedQty,
                'remaining_quantity' => max(0, $remainingQty),
                'progress_percentage' => min(100, $progressPercentage),
                'status' => $this->getItemDeliveryStatus($receivedQty, $orderItem->quantity),
                'unit_price' => $orderItem->unit_price,
                'total_value_ordered' => $orderItem->quantity * $orderItem->unit_price,
                'total_value_received' => $receivedQty * $orderItem->unit_price,
            ];
        });
    }

    /**
     * Get only items that still need to be received
     */
    public function getItemsNeedingReceiving(): \Illuminate\Support\Collection
    {
        return $this->getItemsWithProgress()->filter(function ($item) {
            return $item->remaining_quantity > 0;
        });
    }

    /**
     * Get individual item delivery status
     */
    private function getItemDeliveryStatus($received, $ordered): string
    {
        if ($received == 0) return 'Not Started';
        if ($received >= $ordered) return 'Complete';
        return 'Partial';
    }

    /**
     * Scope untuk query PO yang masih bisa menerima barang
     */
    public function scopeAvailableForReceiving($query)
    {
        return $query->where('status', 'approved')
            ->where(function ($subQuery) {
                $subQuery->where('is_received_complete', false)
                    ->orWhereHas('items', function ($itemQuery) {
                        $itemQuery->whereRaw('
                            quantity > (
                                SELECT COALESCE(SUM(sri.received_quantity), 0) 
                                FROM stock_receiving_items sri 
                                JOIN stock_receivings sr ON sr.id = sri.stock_receiving_id 
                                WHERE sr.purchase_order_id = purchase_orders.id 
                                AND sri.warehouse_item_id = purchase_order_items.item_id
                            )
                        ');
                    });
            });
    }
}
