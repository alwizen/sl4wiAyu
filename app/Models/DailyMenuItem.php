<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMenuItem extends Model
{
    protected $fillable = ['daily_menu_id', 'menu_id', 'target_group_id','target_quantity'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(TargetGroup::class);
    }
}
