<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id', 'date', 'check_in', 'check_out','status','status_out','status_in'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
