<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pivots\BedRoomPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    public $table = "room";

    protected $fillable = ['name', 'location', 'comment', 'number'];

    public function beds()
    {
        return $this->belongsToMany(Bed::class)->withPivot('id');;
    }

    public function bedRoomPivot()
    {
        return $this->hasMany(BedRoomPivot::class);
    }
}
