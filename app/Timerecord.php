<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timerecord extends Model
{
    public $table = "timerecord";

    protected $fillable = ['date', 'lunch', 'comment'];

    public function date(){
        return new \DateTime($this->date);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function totalHours(){
        $total = 0;

        foreach($this->hours as $hour){
            $different = $hour->from()->diff($hour->to());
            $total += $different->h;
            $total += number_format((float)$different->i / 60, 2, '.', '');
            
        }
        return $total;
    }
}
