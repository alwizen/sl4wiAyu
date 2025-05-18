<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlanItem extends Model
{
    protected $fillable = [
        'menu_id',
        'target_group_id',
        'nutrition_plan_id',
        'energy',
        'protein',
        'fat',
        'carb',
        'vitamin',
        'netto'
        ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
    public function targetGroup()
    {
        return $this->belongsTo(TargetGroup::class);
    }
}
