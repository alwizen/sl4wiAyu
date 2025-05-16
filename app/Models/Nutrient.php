<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nutrient extends Model
{
    //

    // add fillable
    protected $fillable = ['name', 'unit'];
    
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function nutritionPlan():HasMany
    {
        return $this->hasMany(NutritionPlan::class);
    }
}
