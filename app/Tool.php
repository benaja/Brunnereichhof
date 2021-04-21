<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Tool extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'amount', 'image'];

    protected $appends = ['image_url', 'small_image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('s3')->temporaryUrl(
                $this->image,
                Carbon::now()->addMinutes(5)
            );
        }
    }

    public function getSmallImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('s3')->temporaryUrl(
                'small/'.$this->image,
                Carbon::now()->addMinutes(5)
            );
        }
    }
}
