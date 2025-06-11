<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodInspaction extends Model
{
    protected $fillable = [
        'inspaction_date'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FoodInspactionItem::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
