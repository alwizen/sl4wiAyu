<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'status_out',
        'status_in'
    ];

    protected $casts = [
        'date'      => 'date',
        'check_in'  => 'datetime',
        'check_out' => 'datetime',
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_PERMIT  = 'permit';  // izin
    public const STATUS_OFF     = 'off';     // libur resmi
    public const STATUS_ABSENT  = 'absent';  // alpa

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
