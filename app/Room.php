<?php

namespace App;

use App\Pivots\BedRoomPivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    public $table = 'room';

    protected $fillable = ['name', 'location', 'comment', 'number', 'isActive'];

    public function beds()
    {
        return $this->belongsToMany(Bed::class)->withPivot('id', 'deleted_at')->withTrashed();
    }

    public function bedRoomPivots()
    {
        return $this->hasMany(BedRoomPivot::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function activeHistory()
    {
        return $this->hasMany(RoomActiveHistory::class);
    }
}
