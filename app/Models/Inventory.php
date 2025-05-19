<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
