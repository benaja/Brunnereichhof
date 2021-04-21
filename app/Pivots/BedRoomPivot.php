<?php

namespace App\Pivots;

use App\Bed;
use App\Reservation;
use App\Room;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class BedRoomPivot extends Pivot
{
    use SoftDeletes;
    public $incrementing = true;

    public $table = 'bed_room';

    protected $fillable = ['bed_id', 'room_id'];

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
