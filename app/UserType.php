<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    public $table = 'usertype';
    public $timestamps = false;

    protected $fillable = ['id', 'name'];

    public function users()
    {
        return $this->hasMany(User::class, 'type_id');
    }
}
