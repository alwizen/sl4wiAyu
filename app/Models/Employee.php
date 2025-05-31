<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'nip',
        'nik',
        'department_id',
        'name',
        'phone',
        'address',
        'start_join',
    ];

    protected $casts = [
        'start_join' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
