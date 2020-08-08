<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pivots\BedRoomPivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Reservation extends Model
{
    use SoftDeletes;

    public $table = "reservation";

    protected $fillable = ['entry', 'exit', 'bed_room_id'];

    public function beds()
    {
        return $this->join('bed_room', 'bed_room.id', '=', 'reservation.bed_room_id')
            ->join('bed', 'bed.id', '=', 'bed_room.bed_id');
    }

    public function bedRoomPivot()
    {
        return $this->belongsTo(BedRoomPivot::class, 'bed_room_id')->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function days()
    {
        return $this->entry->diff($this->exit)->days;
    }

    protected $dates = [
        'entry',
        'exit'
    ];

    public function sleepOver($firstDay, $lastDay) {
        $sleepDays = $this->days();
        if ($firstDay > $this->entry) {
            $sleepDays -= $firstDay->diff($this->entry)->days;
        }
        if ($lastDay < $this->exit) {
            $sleepDays -= $lastDay->diff($this->exit)->days - 1;
        }
        $reservationExitDate = clone $this->exit;
        $reservationExitDate->modify('+1 day');
        // when the employee leaves the bed at the 3. and enters and other bed on 4. It should also
        // count the night from the 3. to 4.
        // But when he leaves at the 3. and dont goes to an other bed id sould not count an additional
        // night
        $hasEmployeeChangedBed = Reservation::where('employee_id', $this->employee_id)
            ->where('entry', $reservationExitDate->format('Y-m-d'))
            ->count();
        if ($hasEmployeeChangedBed && $this->exit <= $lastDay) {
           return $sleepDays + 1;
        }
        return $sleepDays;
    }

    public static function getSleepOver($reservations, $firstDay, $lastDay)
    {
        $sleepOver = 0;
        foreach ($reservations as $reservation) {
            $sleepOver += $reservation->sleepOver($firstDay, $lastDay);
        }
        return $sleepOver;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
