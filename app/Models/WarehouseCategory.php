<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseCategory extends Model
{
    protected $fillable = ['name'];

    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
