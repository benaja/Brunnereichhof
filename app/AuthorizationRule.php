<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthorizationRule extends Model
{
    public $table = "authorizationrule";

    protected $fillable = ['name', 'name_de'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Role::class);
    }
}
