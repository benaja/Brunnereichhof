<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Bed;
use App\Room;
use App\Reservation;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedRoomPivot extends Pivot
{
    use SoftDeletes;

    public $table = "bed_room";

    public function bed()
    {
        return $this->belongsTo(Bed::class)->withTrashed();
    }

    public function room()
    {
        return $this->belongsTo(Room::class)->withTrashed();
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'bed_room_id', 'id');
    }
}
