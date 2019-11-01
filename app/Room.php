<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pivots\BedRoomPivot;

class Room extends Model
{
    public $table = "room";

    protected $fillable = ['name', 'location', 'comment', 'number', 'isDeleted'];

    public function beds()
    {
        return $this->belongsToMany(Bed::class)->withPivot('id');;
    }

    public function bedRoomPivot()
    {
        return $this->hasMany(BedRoomPivot::class);
    }
}
