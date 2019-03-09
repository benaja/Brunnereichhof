<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BedRoomPivot extends Pivot
{
    public $table = "bed_room";

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
