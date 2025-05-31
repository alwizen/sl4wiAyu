<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    //

    // add fillable
    protected $fillable = ['name', 'allowance', 'salary', 'bonus', 'absence_deduction'];
}
