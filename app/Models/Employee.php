<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'nip',
        'nik',
        'rfid_uid',
        'department_id',
        'name',
        'phone',
        'address',
        'start_join',
        // 'work_type',
    ];

    protected $casts = [
        'start_join' => 'date',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
