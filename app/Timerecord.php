<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timerecord extends Model
{
    use SoftDeletes;

    public $table = 'timerecord';

    protected $fillable = ['date', 'lunch', 'comment', 'breakfast', 'dinner'];

    public function date()
    {
        return new \DateTime($this->date);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    public function workInputType()
    {
        return $this->hasMany(WorkInputType::class);
    }

    public function totalHours()
    {
        $total = 0;

        foreach ($this->hours as $hour) {
            $different = $hour->from()->diff($hour->to());
            $total += $different->h;
            $total += number_format((float) $different->i / 60, 2, '.', '');
        }

        return $total;
    }

    public function worktype()
    {
        $hour = $this->hours->first();

        return $hour ? $hour->worktype : null;
    }

    public static function getMealsBetweenDate($firstDate, $lastDate)
    {
        return self::getMealsBetweenDateByType($firstDate, $lastDate, 'lunch') +
            self::getMealsBetweenDateByType($firstDate, $lastDate, 'breakfast') +
            self::getMealsBetweenDateByType($firstDate, $lastDate, 'dinner');
    }

    public static function getMealsBetweenDateByType($firstDate, $lastDate, $mealType)
    {
        return self::where('date', '>=', $firstDate->format('Y-m-d'))
            ->where('date', '<=', $lastDate->format('Y-m-d'))
            ->where($mealType, 1)
            ->get()
            ->count();
    }
}
