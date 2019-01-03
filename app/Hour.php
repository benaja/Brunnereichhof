<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    public $table = "hours";

    protected $fillable = ['from', 'to', 'comment'];

    public function from()
    {
        return new \DateTime($this->from);
    }

    public function to()
    {
        return new \DateTime($this->to);
    }

    public function getMarginTop()
    {
        $different = $this->from()->diff(new \DateTime("00:00"));
        return ($different->h * 41) + (41 / 60 * $different->i);
    }

    public function getHeight()
    {
        $different = $this->from()->diff($this->to());
        return ($different->h * 41) + (41 / 60 * $different->i);
    }

    public function timerecord()
    {
        return $this->belongsTo(Timerecord::class);
    }

    public function worktype()
    {
        return $this->belongsTo(Worktype::class);
    }

    public function duration()
    {
        $different = $this->from()->diff($this->to());
        $minutesInHours = 100 / 60 * $different->i;
        return $different->h . "." . number_format((float)$minutesInHours, 0, '.', '');
    }
}
