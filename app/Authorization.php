<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    public $table = "authorization";

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
