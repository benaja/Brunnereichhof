<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public $table = 'address';

    protected $fillable = ['street', 'place', 'plz', 'addition'];
}
