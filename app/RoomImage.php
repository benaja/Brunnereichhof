<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    public $table = "room_image";

    protected $fillable = ['room_id', 'path'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
