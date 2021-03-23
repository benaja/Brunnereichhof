<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worktype extends Model
{
    use SoftDeletes;

    public $table = 'worktype';

    public $timestamps = false;

    protected $fillable = ['name', 'name_de', 'color', 'short_name', 'manually'];

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function workInputTypes()
    {
        return $this->hasMany(WorkInputType::class);
    }
}
