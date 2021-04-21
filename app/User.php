<?php

namespace App;

use App\Enums\UserTypeEnum;
use App\Enums\WorkTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

    public $table = 'user';

    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'authorization', 'username', 'isPasswordChanged', 'type_id', 'role_id', 'isActive', 'passwordResetToken',
    ];

    protected $hidden = [
        'password', 'remember_token', 'passwordResetToken',
    ];

    public function fullName()
    {
        return "{$this->lastname} {$this->firstname}";
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'type_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function timerecords()
    {
        return $this->hasMany(Timerecord::class);
    }

    public function hours()
    {
        return $this->hasManyThrough(Hour::class, Timerecord::class);
    }

    public function authorize($userTypes, $rules = [])
    {
        if ($this->deleted_at !== null) {
            return abort(401, 'This action is unauthorized.');
        }

        if (count($rules) > 0 && $this->hasRule($rules)) {
            return true;
        }

        if (is_array($userTypes)) {
            //$role = $roles[0];
            //var_dump($this->authorization()->where('name',$roles)->first());
            return $this->isAnyType($userTypes) ||
                abort(403, 'This action is forbidden.');
        }

        return $this->isType($userTypes) ||
            abort(403, 'This action is forbidden.');
    }

    public function hasRule($rules)
    {
        return $this->role && null !== $this->role->authorizationRules->whereIn('name', $rules)->first();
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

        return $this->totalHoursByMonth($currentDate);
    }

    public function totalHoursByMonth($dateOfMonth, $worktype = null)
    {
        $firstDayOfMonth = $dateOfMonth;
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        return $this->getTotalHours($firstDayOfMonth, $lastDayOfMonth, $worktype);
    }

    public function totalHoursByYear($date, $worktype = null)
    {
        $firstDayOfYear = clone $date;
        $firstDayOfYear->modify('first day of january this year');
        $lastDayOfYear = clone $date;
        $lastDayOfYear->modify('last day of december this year');

        return $this->getTotalHours($firstDayOfYear, $lastDayOfYear, $worktype);
    }

    public function getNumberOfMealsByMonth(\DateTime $firstDayOfMonth)
    {
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        return $this->getNumberOfMeals($firstDayOfMonth, $lastDayOfMonth);
    }

    public function getNumberOfMealsByYear(\DateTime $date)
    {
        $firstDayOfYear = clone $date;
        $firstDayOfYear->modify('first day of january this year');
        $lastDayOfYear = clone $date;
        $lastDayOfYear->modify('last day of december this year');

        return $this->getNumberOfMeals($firstDayOfYear, $lastDayOfYear);
    }

    public function holydaysPlant(\DateTime $today)
    {
        $lastDayOfYear = clone $today;
        $lastDayOfYear->modify('last day of december this year');

        return $this->hours()
            ->where('hours.date', '>=', $today->format('Y-m-d'))
            ->where('hours.date', '<=', $lastDayOfYear->format('Y-m-d'))
            ->where('worktype_id', WorkTypeEnum::Holydays)
            ->count();
    }

    public function holydaysDone(\DateTime $today)
    {
        $firstDayOfYear = clone $today;
        $firstDayOfYear->modify('first day of january this year');

        return $this->hours()
        ->where('hours.date', '>=', $firstDayOfYear->format('Y-m-d'))
        ->where('hours.date', '<', $today->format('Y-m-d'))
        ->where('worktype_id', WorkTypeEnum::Holydays)
        ->count();
    }

    public static function workers()
    {
        return self::where('type_id', UserTypeEnum::Worker);
    }

    private function getTotalHours($startDate, $endDate, $worktype = null)
    {
        return $this->hours()
            ->where('hours.date', '>=', $startDate)
            ->where('hours.date', '<=', $endDate)
            ->when($worktype, function ($query, $worktype) {
                $query->where('worktype_id', $worktype);
            })
            ->sum('duration');
    }

    public function getNumberOfMeals($startDate, $endDate)
    {
        $timerecords = $this->timerecords
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'));

        return [
            'breakfast' => (clone $timerecords)->where('breakfast', 1)->count(),
            'lunch' => (clone $timerecords)->where('lunch', 1)->count(),
            'dinner' => (clone $timerecords)->where('dinner', 1)->count(),
        ];
    }

    public function getTotalNumberOfMealsBetweenDates($startDate, $endDate)
    {
        $numberOfMealsByTpye = $this->getNumberOfMeals($startDate, $endDate);

        return $numberOfMealsByTpye['breakfast'] + $numberOfMealsByTpye['lunch'] + $numberOfMealsByTpye['dinner'];
    }

    private function timerecordsBetweenDates($startDate, $endDate)
    {
        return $this->timerecords->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'));
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
