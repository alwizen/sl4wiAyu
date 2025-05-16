<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'student_count'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
