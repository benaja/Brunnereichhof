<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    public $table = "bed";

    protected $fillable = ['name', 'width', 'places', 'comment', 'isDeleted'];

    public function room()
    {
        return $this->belongsToMany(Room::class)->withPivot('id');;
    }

    public function inventars()
    {
        return $this->belongsToMany(Inventar::class)
            ->withPivot('amount', 'amount_2');
    }
}
