<?php

namespace App\Http\Controllers;

use App\Rapportdetail;
use App\Employee;
use App\Pivots\BedRoomPivot;
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
            'employeeHoursByMonth' => $this->employeeHoursByMonth(),
            'employeeTotalNumbers' => $this->employeeTotalNumbers(),
            'workerHoursByMonth' => $this->workerHoursByMonth(),
            'workerTotalNumbers' => $this->workerTotalNumbers()
        ];
    }

    public function roomdispositioner()
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $beds = BedRoomPivot::join('reservation', function ($join) {
            $join->on('reservation.bed_room_id', '=', 'bed_room.id');
        })->where('reservation.entry', '<=', (new \DateTime())->format('Y-m-d'))
            ->where('reservation.exit', '>=', (new \DateTime())->format('Y-m-d'))->get();;

        $allBeds = BedRoomPivot::with('bed')->get()->toArray();
        $amountOfAllBeds = array_sum(array_map(function ($bedRoomPivot) {
            return $bedRoomPivot['bed']['places'];
        }, $allBeds));
        $stats = [
            'freePlaces' => $amountOfAllBeds - count($beds),
            'usedPlaces' => count($beds),
            'totalPlaces' => $amountOfAllBeds
        ];

        return $stats;
    }

    private function employeeHoursByMonth()
    {
        $firstOfThisMonth = new \DateTime('first day of this month');
        $lastOfThisMonth = new \DateTime('last day of this month');

        $totalHoursOfYear = [];

        for ($i = 0; $i < 12; $i++) {
            $totalHours = Rapportdetail::where('date', '>=', $firstOfThisMonth->format('Y-m-d'))
                ->where('date', '<=', $lastOfThisMonth->format('Y-m-d'))->sum('hours');

            $monthName = $firstOfThisMonth->format('m');
            $monthName = $this->monthNames[intval($monthName) - 1];

            $month = [
                'hours' => round($totalHours),
                'name' => $monthName
            ];

            array_push($totalHoursOfYear, $month);
            $firstOfThisMonth->modify('-1 day')->modify('first day of this month');
            $lastOfThisMonth->modify('-31 days')->modify('last day of this month');
        }

        $totalHoursOfYear = array_reverse($totalHoursOfYear);

        return $totalHoursOfYear;
    }

    private function workerHoursByMonth()
    {
        $firstOfThisMonth = new \DateTime('first day of this month');
        $lastOfThisMonth = new \DateTime('last day of this month');

        $monthValues = [];

        for ($i = 0; $i < 12; $i++) {
            $timerecords = Timerecord::where('date', '>=', $firstOfThisMonth->format('Y-m-d'))
                ->where('date', '<=', $lastOfThisMonth->format('Y-m-d'))->get();

            $hours = 0;
            foreach ($timerecords as $timerecord) {
                $hours += $timerecord->totalHours();
            }

            $monthName = $firstOfThisMonth->format('m');
            $monthName = $this->monthNames[intval($monthName) - 1];

            $month = [
                'hours' => round($hours),
                'name' => $monthName
            ];

            array_push($monthValues, $month);
            $firstOfThisMonth->modify('-1 day')->modify('first day of this month');
            $lastOfThisMonth->modify('-31 days')->modify('last day of this month');
        }

        $monthValues = array_reverse($monthValues);

        return $monthValues;
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

    private function workerTotalNumbers()
    {
        $firstOfThisYear = new \DateTime('first day of January this year');
        $lastOfThisYear = new \DateTime('last day of December this year');

        $timerecords = Timerecord::where('date', '>=', $firstOfThisYear->format('Y-m-d'))
            ->where('date', '<=', $lastOfThisYear->format('Y-m-d'))->get();

        $hours = 0;
        foreach ($timerecords as $timerecord) {
            $hours += $timerecord->totalHours();
        }

        $response = [
            'hours' => round($hours, 2)
        ];
        return $response;
    }
}
