<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssue extends Model
{
    //
    protected $fillable = [
        'issue_date',
        'status',
        'notes',
    ];
    protected $casts = [
        'issue_date' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    // Helper methods
    public function getTotalRequestedAttribute()
    {
        return $this->items->sum('requested_quantity');
    }
    
    public function getTotalIssuedAttribute()
    {
        return $this->items->sum('issued_quantity');
    }
    
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'draft' => 'secondary',
            'requested' => 'primary',
            'preparing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        
        $labels = [
            'draft' => 'Draft',
            'requested' => 'Diminta',
            'preparing' => 'Sedang Disiapkan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        
        return [
            'color' => $colors[$this->status] ?? 'secondary',
            'label' => $labels[$this->status] ?? ucfirst($this->status),
        ];
    }
    
    // Scope untuk filter status
    public function scopeWithStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        
        return $query->where('status', $status);
    }
    
    // Scope untuk pengeluaran yang menunggu persiapan oleh gudang
    public function scopePendingPreparation($query)
    {
        return $query->where('status', 'requested');
    }
    
    // Scope untuk pengeluaran yang sedang diproses
    public function scopeInProgress($query)
    {
        return $query->where('status', 'preparing');
    }
    
    // Scope untuk pengeluaran yang sudah selesai
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

}
