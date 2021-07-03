<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $guarded = [];

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
