<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TargetGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'energy',
        'protein',
        'fat',
        'carb',
        'vitamin',
        'mineral'
    ];

}
