<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResourcePlannerDay extends Model
{
    protected $guarded = [];

    public function resources() {
        return $this->hasMany(Resource::class);
    }
}
