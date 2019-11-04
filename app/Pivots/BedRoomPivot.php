<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Bed;
use App\Room;
use App\Reservation;

class BedRoomPivot extends Pivot
{
    public $table = "bed_room";
    public $timestamps = false;

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
