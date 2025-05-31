<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'code',
        'purchase_date',
        'name',
        'stock_init',
        'addition',
        'damaged',
        'missing',
        'stock_end'
    ];

    public function additions(): HasMany
    {
        return $this->hasMany(InventoryAddition::class);
    }

    public function missings()
    {
        return $this->hasMany(InventoryMissing::class);
    }
}
