<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worktype extends Model
{
    public $table = "worktype";

    protected $fillable = ['name'];

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }
}
