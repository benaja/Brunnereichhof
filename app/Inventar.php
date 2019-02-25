<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventar extends Model
{
    public $table = "inventar";

    protected $fillable = ['name', 'price'];

    public function bed()
    {
        return $this->belongsToMany(Bed::class)
            ->withPivot('amount');
    }
}
