<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAddition extends Model
{
    protected $fillable = ['inventory_id', 'quantity', 'note'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

}