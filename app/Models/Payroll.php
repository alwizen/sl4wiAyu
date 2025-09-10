<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'start_date',
        'end_date',
        'work_days',
        'permit',
        'off_day',
        'absences',
        'total_thp',
        'other',
        'bonus',
        'is_manual_thp',
        'note'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
