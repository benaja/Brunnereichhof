<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rapportdetail extends Model
{
    public $table = "rapportdetail";

    protected $fillable = ['hours', 'comment', 'day', 'foodtype_id', 'date'];

    public function rapport(){
        return $this->belongsTo(Rapport::class);     
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function foodtype(){
        return $this->belongsTo(Foottype::class);
    }
}
