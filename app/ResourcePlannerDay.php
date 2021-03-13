<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourcePlannerDay extends Model
{
    protected $guarded = [];

    protected $dates = [
        'date'
    ];

    public function resources() {
        return $this->hasMany(Resource::class);
    }
}
