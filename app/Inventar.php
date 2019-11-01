<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventar extends Model
{
    use SoftDeletes;
    
    public $table = "inventar";

    protected $fillable = ['name', 'price'];

    public function bed()
    {
        return $this->belongsToMany(Bed::class)
            ->withPivot('amount', 'amount_2');
    }
}
