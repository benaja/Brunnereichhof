<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public $table = "reservation";

    protected $fillable = ['entry', 'exit'];

    public function bed()
    {
        return $this->join('room_bed', 'room_bed.id', '=', 'reservation.room_bed_id')
            ->join('bed', 'beed.id', '=', 'room_bed.bed_id');
    }

    protected $dates = [
        'entry',
        'exit'
    ];
}
