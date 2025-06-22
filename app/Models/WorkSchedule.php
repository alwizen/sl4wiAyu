<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = ['employee_id', 'date', 'start_time', 'end_time', 'type'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
