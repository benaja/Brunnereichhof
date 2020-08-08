<?php

namespace App\Http\Controllers;

use App\Rapportdetail;
use App\Employee;
use App\Pivots\BedRoomPivot;
use App\Stats;
use App\Timerecord;

class DashboardController extends Controller
{
    private $monthNames = [
        "Jan.", "Feb.", "März", "Apr.", "Mai", "Juni",
        "Juli", "Aug.", "Sep.", "Okt.", "Nov.", "Dez."
    ];

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function allStats()
    {
        auth()->user()->authorize(['superadmin']);

        return [
            'employeeHoursByMonth' => Stats::values('employeeHoursByMonth'),
            'employeeTotalNumbers' => $this->employeeTotalNumbers(),
            'workerHoursByMonth' => Stats::values('workerHoursByMonth'),
            'workerTotalNumbers' => Stats::values('workerTotalNumbers'),
            'updatedAt' => Stats::values('lastCronJob')
        ];
    }

    public function roomdispositioner()
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $beds = BedRoomPivot::join('reservation', function ($join) {
            $join->on('reservation.bed_room_id', '=', 'bed_room.id');
        })
            ->join('room', 'room.id', '=', 'bed_room.room_id')
            ->where('room.deleted_at', null)
            ->where('reservation.entry', '<=', (new \DateTime())->format('Y-m-d'))
            ->where('reservation.exit', '>=', (new \DateTime())->format('Y-m-d'))
            ->where('reservation.deleted_at', null)
            ->get();;

        $allBeds = BedRoomPivot::with(['bed', 'room'])->get()->toArray();
        $amountOfAllBeds = array_sum(array_map(function ($bedRoomPivot) {
            if ($bedRoomPivot['room'] && $bedRoomPivot['room']['deleted_at']) return 0;
            return $bedRoomPivot['bed'] && $bedRoomPivot['bed']['places'];
        }, $allBeds));
        $stats = [
            'freePlaces' => $amountOfAllBeds - count($beds),
            'usedPlaces' => count($beds),
            'totalPlaces' => $amountOfAllBeds
        ];

        return $stats;
    }

    private function employeeTotalNumbers()
    {
        $firstOfThisYear = new \DateTime('first day of January this year');
        $lastOfThisYear = new \DateTime('last day of December this year');

        $totalHours = Rapportdetail::where('date', '>=', $firstOfThisYear->format('Y-m-d'))
            ->where('date', '<=', $lastOfThisYear->format('Y-m-d'))->sum('hours');

        $employeesAmount = Employee::where('isActive', 1)->count();

        $response = [
            'hours' => round($totalHours, 2),
            'activeEmployees' => $employeesAmount
        ];
        return $response;
    }
}
