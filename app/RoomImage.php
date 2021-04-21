<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomImage extends Model
{
    public $table = 'room_image';

    protected $fillable = ['room_id', 'path'];

    protected $appends = ['url'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->temporaryUrl(
            $this->path,
            Carbon::now()->addMinutes(5)
        );
    }
}
