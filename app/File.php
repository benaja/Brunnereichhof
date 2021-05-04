<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['url'];

    public function filable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        if ($this->path) {
            return Storage::disk('s3')->temporaryUrl(
                $this->path,
                now()->addHours(5)
            );
        }
    }
}
