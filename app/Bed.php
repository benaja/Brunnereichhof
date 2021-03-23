<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use SoftDeletes;

    public $table = 'bed';

    protected $fillable = ['name', 'width', 'places', 'comment'];

    public function room()
    {
        return $this->belongsToMany(Room::class)->withPivot('id');
    }

    public function inventars()
    {
        return $this->belongsToMany(Inventar::class)
            ->withPivot('amount', 'amount_2')->withTrashed();
    }
}
