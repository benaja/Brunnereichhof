<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public $table = "reservation";

    protected $fillable = ['entry', 'exit'];

    public function bed()
    {
        return $this->belongsToMany(Bed::class, 'room_bed', '')
            ->withPivot('amount');
    }

    protected $dates = [
        'entry',
        'exit'
    ];
}
