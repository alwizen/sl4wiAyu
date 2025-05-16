<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMenu extends Model
{
   protected $fillable = ['menu_date'];

   public function menu():BelongsTo
   {
       return $this->belongsTo(Menu::class);
   }
   public function dailyMenuItems():HasMany
   {
       return $this->hasMany(DailyMenuItem::class);
   }
}
