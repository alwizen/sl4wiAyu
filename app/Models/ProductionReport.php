<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    protected $fillable = [
        'production_date',
    ];

    public function items()
    {
        return $this->hasMany(ProductionReportItem::class);
    }

   
}
