<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'seats', 'number', 'comment', 'important_comment', 'fuel', 'image'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('s3')->temporaryUrl(
                $this->image,
                Carbon::now()->addMinutes(5)
            );
        }
    }
}
