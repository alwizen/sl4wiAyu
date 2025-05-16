<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionReportItem extends Model
{
    //

    // add fillable
    protected $fillable = [
        'production_report_id',
        'daily_menu_item_id',
        'target_qty',
        'actual_qty',
        'status',
    ];

    public function productionReport():BelongsTo
    {
        return $this->belongsTo(ProductionReport::class);
    }

    public function dailyMenuItem():BelongsTo
    {
        return $this->belongsTo(DailyMenuItem::class);
    }

    public function calculateStatus(): string
    {
        if ($this->actual_qty == $this->target_qty)
        {
            return 'tercukupi';
        }
        elseif ($this->actual_qty < $this->target_qty) {
            return 'kurang';
        }
        else
        {
            return 'lebih';
        }
}
    public function getMenuNameAttribute()
    {
        return $this->dailyMenu->menu_name ?? 'Menu Tidak Ditemukan';
    }
//    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
