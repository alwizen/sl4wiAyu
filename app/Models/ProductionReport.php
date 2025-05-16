<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionReport extends Model
{
    //

    // add fillable
    protected $fillable = ['date'];

    protected $casts = ['date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(ProductionReportItem::class);
    }
    // add guaded
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
