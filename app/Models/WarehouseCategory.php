<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseCategory extends Model
{
    protected $fillable = ['name'];

    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function warehouseItems(): HasMany
    {
        return $this->hasMany(WarehouseItem::class);
    }
}
