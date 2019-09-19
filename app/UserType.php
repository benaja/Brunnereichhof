<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public $table = "usertype";
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class, 'type_id');
    }
}
