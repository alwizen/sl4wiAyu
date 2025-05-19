<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipient extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
//        'target_group_id',
        'total_recipients'
    ];

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(TargetGroup::class);
    }
}
