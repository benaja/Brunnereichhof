<?php

namespace App;

use App\Enums\FoodTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    public $table = "employee";

    protected $fillable = ['callname', 'nationality', 'isIntern', 'isDriver', 'german_knowledge', 'english_knowledge', 'sex', 'comment', 'experience', 'isActive', 'isGuest', 'profileimage', 'allergy'];

    public function Rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function getFoodAmountBetweenDates($firstDate, $lastDate, $foodType = [FoodTypeEnum::Eichhof])
    {
        return $this->rapportdetails
            ->where('date', '>=', $firstDate->format('Y-m-d'))
            ->where('date', '<=', $lastDate->format('Y-m-d'))
            ->whereIn('foodtype_id', $foodType)
            ->where('hours', '>', 0)
            ->groupBy('date')
            ->count();
    }

    // user accessors
    // public function getFirstnameAttribute()
    // {
    //     return $this->user->firstname;
    // }

    // public function getLastnameAttribute()
    // {
    //     return $this->user->lastname;
    // }

    // public function getEmailAttribute()
    // {
    //     return $this->user->email;
    // }

    // user mutators
    // public function setFirstnameAttribute($value)
    // {
    //     $this->user->firstname = $value;
    // }

    // public function setLastnameAttribute($value)
    // {
    //     $this->user->lastname = $value;
    // }

    // public function setEmailAttribute($value)
    // {
    //     $this->user->email = $value;
    // }
}
