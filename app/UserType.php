<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public $table = "usertype";

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
