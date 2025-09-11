<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'allowance',
        'salary',
        'bonus', //PJ
        'absence_deduction',
        'permit_amount'
    ];
    protected $casts = [
        'permit_amount' => 'integer',
    ];
}
