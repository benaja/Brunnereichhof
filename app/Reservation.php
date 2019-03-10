<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public $table = "reservation";

    protected $fillable = ['entry', 'exit', 'bed_room_id'];

    public function beds()
    {
        return $this->join('room_bed', 'room_bed.id', '=', 'reservation.room_bed_id')
            ->join('bed', 'beed.id', '=', 'room_bed.bed_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function bedRoomPivot()
    {
        return $this->belognsTo(BedRoomPivot::class);
    }

    protected $dates = [
        'entry',
        'exit'
    ];
}
