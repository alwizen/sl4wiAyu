<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodInspactionItem extends Model
{
   protected $fillable = [
        'food_inspaction_id',
        'menu_id',
        'is_good'
   ];

   public function menu(): BelongsTo
   {
    return $this->belongsTo(Menu::class);
   }

   public function inspaction(): BelongsTo
   {
    return $this->belongsTo(FoodInspaction::class);
   }
}
