<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomActiveHistory extends Model
{
    public $table = "room_active_history";
    
    protected $fillable = ['active_from', 'active_to', 'room_id'];

    protected $dates = [
        'active_from',
        'active_to'
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }
}
