<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\WorkTypeEnum;

class User extends Authenticatable
{
    use Notifiable;

    public $table = "user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'authorization', 'username', 'isPasswordChanged'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function customer()
    {
        return $this->hasOne('App\Customer');
    }

    public function timerecords()
    {
        return $this->hasMany(Timerecord::class);
    }

    public function authorizeRoles($roles)
    {
        if (is_array($roles)) {
            //$role = $roles[0];
            //var_dump($this->authorization()->where('name',$roles)->first());
            return $this->hasAnyRole($roles) ||
                abort(401, 'This action is unauthorized.');
        }
        return $this->hasRole($roles) ||
            abort(401, 'This action is unauthorized.');
    }

    public function hasAnyRole($roles)
    {
        return null !== $this->authorization()->whereIn('name', $roles)->first();
    }

    public function hasRole($role)
    {
        return null !== $this->authorization()->where('name', $role)->first();
    }

    public function totalHoursOfThisMonth()
    {
        $currentDate = new \DateTime('now');

        return $this->totalHours($currentDate);
    }

    public function totalHours($dateOfMonth)
    {
        $totalHours = 0;
        $firstDayOfMonth = $dateOfMonth;
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        $timerecords = $this->timerecords
            ->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'));

        foreach ($timerecords as $timerecord) {
            foreach ($timerecord->hours as $hour) {
                if ($hour->worktype_id == WorkTypeEnum::ProductiveHours) {
                    $totalHours += $hour->duration();
                }
            }
        }

        return $totalHours;
    }

    public function getNumberOfLunches(\DateTime $firstDayOfMonth)
    {
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $timerecords = $this->timerecords
            ->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->where('lunch', 1);

        return (count($timerecords));
    }

    public function holydaysPlant(\DateTime $today)
    {
        $lastDayOfYear = clone $today;
        $lastDayOfYear->modify('last day of december this year');
        $totalHolidays = 0;

        $timerecords = $this->timerecords
            ->where('date', '>=', $today->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfYear->format('Y-m-d'));

        foreach ($timerecords as $timerecord) {
            $hours = $timerecord->hours->where('worktype_id', WorkTypeEnum::Holydays);
            $totalHolidays += count($hours);
        }

        return $totalHolidays;
    }

    public function holydaysDone(\DateTime $today)
    {
        $firstDayOfYear = clone $today;
        $firstDayOfYear->modify('first day of january this year');
        $totalHolidays = 0;

        $timerecords = $this->timerecords
            ->where('date', '>=', $firstDayOfYear->format('Y-m-d'))
            ->where('date', '<', $today->format('Y-m-d'));

        foreach ($timerecords as $timerecord) {
            $hours = $timerecord->hours->where('worktype_id', WorkTypeEnum::Holydays);
            $totalHolidays += count($hours);
        }

        return $totalHolidays;
    }
}
