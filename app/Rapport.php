<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    public $table = "rapport";

    protected $fillable = ['isFinished', 'startdate', 'rapporttype', 'comment'];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function rapportdetails(){
        return $this->hasMany(Rapportdetail::class);
    }
}
