<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'work_days',
        'absences',
        'total_thp',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

}
