<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foottype extends Model
{
    public $table = "foodtype";

    protected $fillable = ['foodname'];

    public function rapportdetails(){
        return $this->hasMany(Rapportdetail::class);
    }

}
