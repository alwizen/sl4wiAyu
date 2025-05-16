<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionPlan extends Model
{
    protected $fillable = ['nutrition_plan_date', 'daily_menu_id'];

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function nutritionPlanItems(): HasMany
    {
        return $this->hasMany(NutritionPlanItem::class);
    }
}
