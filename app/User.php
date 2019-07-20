<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\WorkTypeEnum;
use App\Enums\UserTypeEnum;

class User extends Authenticatable
{
    use Notifiable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public $table = "user";

    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'authorization', 'username', 'isPasswordChanged', 'isDeleted', 'type_id', 'role_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function type()
    {
        return $this->belongsTo(UserType::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function customer()
    {
        return $this->hasOne('App\Customer');
    }

    public function timerecords()
    {
        return $this->hasMany(Timerecord::class);
    }

    public function authorizeRoles($userTypes, $rules = [])
    {
        if ($this->isDeleted == 1) {
            return abort(401, 'This action is unauthorized.');
        }

        if (count($rules) > 0 && $this->hasRule($rules)) return true;

        if (is_array($userTypes)) {
            //$role = $roles[0];
            //var_dump($this->authorization()->where('name',$roles)->first());
            return $this->isAnyType($userTypes) ||
                abort(401, 'This action is unauthorized.');
        }
        return $this->isType($userTypes) ||
            abort(401, 'This action is unauthorized.');
    }

    public function hasRule($rules)
    {
        return null !== $this->authorizationRules()->whereIn('name', $rules)->first();
    }

    public function isAnyType($roles)
    {
        return null !== $this->type()->whereIn('name', $roles)->first();
    }

    public function isType($role)
    {
        return null !== $this->type()->where('name', $role)->first();
    }

    public function totalHoursOfThisMonth()
    {
        $currentDate = new \DateTime('now');

        return $this->totalHours($currentDate);
    }

    public function totalHours($dateOfMonth, $worktype = null)
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
                if (isset($worktype) && $hour->worktype_id == $worktype) {
                    $totalHours += $hour->duration();
                } else if (!isset($worktype)) {
                    $totalHours += $hour->duration();
                }
            }
        }

        return $totalHours;
    }

    public function getNumberOfMeals(\DateTime $firstDayOfMonth)
    {
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $timerecords = $this->timerecords
            ->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->where('lunch', 1);

        $meals = [
            'breakfast' => count($timerecords->where('breakfast', 1)),
            'lunch' => count($timerecords->where('lunch', 1)),
            'dinner' => count($timerecords->where('dinner', 1))
        ];

        return $meals;
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

    public static function workers()
    {
        return User::where('type_id', UserTypeEnum::Worker);
    }
}
