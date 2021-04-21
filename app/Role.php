<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $table = 'role';

    protected $fillable = ['name', 'name_de'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function authorizationRules()
    {
        return $this->belongsToMany(AuthorizationRule::class, 'role_authorizationrule', 'role_id', 'authorizationrule_id');
    }
}
