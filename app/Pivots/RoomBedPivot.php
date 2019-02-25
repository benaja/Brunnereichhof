<?php

namespace App\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomBedPivot extends Pivot
{
    public $table = "room_bed";

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
