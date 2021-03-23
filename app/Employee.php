<?php

namespace App;

use App\Enums\FoodTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use SoftDeletes;

    public $table = 'employee';

    protected $with = ['user'];

    protected $fillable = [
        'callname',
        'nationality',
        'isIntern',
        'isDriver',
        'sex',
        'comment',
        'experience',
        'isActive',
        'isGuest',
        'profileimage',
        'allergy',
        'isLoginActive',
        'entryDate',
        'drivingLicence',
        'resource_planner_white_listed',
        'function',
    ];

    protected $appends = ['firstname', 'lastname', 'email', 'small_profile_image', 'profileimage_url'];

    protected $dates = ['entryDate'];

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
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
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
    public function getFirstnameAttribute()
    {
        return $this->user ? $this->user->firstname : '';
    }

    public function getLastnameAttribute()
    {
        return $this->user ? $this->user->lastname : '';
    }

    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }

    public function getProfileimageUrl()
    {
        return $this->profileImageUrl($this->profileimage);
    }

    public function getProfileimageUrlAttribute()
    {
        return $this->profileImageUrl($this->profileimage);
    }

    public function getSmallProfileImageAttribute()
    {
        return $this->profileImageUrl('small/'.$this->profileimage);
    }

    private function profileImageUrl($path)
    {
        if ($this->profileimage) {
            return Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addHours(5)
            );
        }
    }

    // user mutators
    public function setFirstnameAttribute($value)
    {
        $this->user->firstname = $value;
    }

    public function setLastnameAttribute($value)
    {
        $this->user->lastname = $value;
    }

    public function setEmailAttribute($value)
    {
        $this->user->email = $value;
    }
}
