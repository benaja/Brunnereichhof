<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $guarded = [];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function rapport() {
        return $this->belongsTo(Rapport::class);
    }

    public function rapportdetails() {
        return $this->hasMany(Rapportdetail::class);
    }

    public function cars() {
        return $this->belongsToMany(Car::class);
    }

    public function tools() {
        return $this->belongsToMany(Tool::class);
    }
}
