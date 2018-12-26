<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foodtype extends Model
{
    public $table = "foodtype";

    protected $fillable = ['foodname'];

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

}
