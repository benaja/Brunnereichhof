<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    public $table = "bed";

    protected $fillable = ['width', 'places', 'comment'];

    public function room()
    {
        return $this->belongsToMany(Room::class);
    }

    public function inventar()
    {
        return $this->belongsToMany(Inventar::class)
            ->withPivot('amount');
    }
}
