<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //

    // add fillable
    protected $fillable = ['name', 'address', 'phone'];
    
}
