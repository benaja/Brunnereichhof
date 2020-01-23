<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pivots\BedRoomPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    public $table = "reservation";

    protected $fillable = ['entry', 'exit', 'bed_room_id'];

    public function beds()
    {
        return $this->join('bed_room', 'bed_room.id', '=', 'reservation.bed_room_id')
            ->join('bed', 'bed.id', '=', 'bed_room.bed_id');
    }

    public function bedRoomPivot()
    {
        return $this->belongsTo(BedRoomPivot::class, 'bed_room_id')->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function days()
    {
        return $this->entry->diff($this->exit)->days;
    }

    protected $dates = [
        'entry',
        'exit'
    ];
}
