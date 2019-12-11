<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    public $table = "employee";

    protected $fillable = ['callname', 'firstname', 'lastname', 'nationality', 'isIntern', 'isDriver', 'german_knowledge', 'english_knowledge', 'sex', 'comment', 'experience', 'isActive', 'isGuest', 'profileimage', 'allergy'];

    public function Rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function name()
    {
        return "$this->lastname $this->firstname";
    }

    public function reservationsBetweenDates($startDate, $endDate)
    {
        return $this->reservations()
            ->where('entry', '<=', $endDate->format('Y-m-d'))
            ->where('exit', '>=', $startDate->format('Y-m-d'))
            ->orderBy('entry')
            ->get();
    }
}
