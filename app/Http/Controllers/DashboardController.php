<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Helpers\Settings;
use App\Hour;
use App\Pivots\BedRoomPivot;
use App\Rapportdetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function allStats(Request $request)
    {
        auth()->user()->authorize(['superadmin']);

        $startDate = Carbon::now()->subtract('year', 1)->startOfMonth();
        $endDate = Carbon::now();

        if ($request->get('dateRange') === 'all') {
            $startDate = new Carbon('2018-10-01');
        } else if ($request->get('dateRange') && $request->get('dateRange') !== 'last-12-months') {
            $startDate = Carbon::now()->year($request->get('dateRange'))->startOfYear();
            $endDate = $startDate->clone()->endOfYear();
        }

        $workerHoursByMonth = [$this->workerHoursByMonth($startDate, $endDate)];

        $workerTotalHours = Hour::where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->sum('duration');

        $employeeHoursByMonth = [$this->employeeHoursByMonth($startDate, $endDate)];

        $employeeTotalHours = Rapportdetail::where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->sum('hours');


        if ($request->get('withPreviousYear') && $request->get('withPreviousYear') === 'true' && $request->get('dateRange') !== 'all') {
            $startDateLastYear = $startDate->clone()->subtract('year', 1);
            $endDateLastYear = $endDate->clone()->subtract('year', 1);

            array_push($workerHoursByMonth, $this->workerHoursByMonth($startDateLastYear, $endDateLastYear));
            array_push($employeeHoursByMonth, $this->employeeHoursByMonth($startDateLastYear, $endDateLastYear));
        }

        return [
            'employees' => [
                'hoursByMonth' => $employeeHoursByMonth,
                'totalHours' => trim(number_format($employeeTotalHours, 2, '.', "'"), '0'),
                'active' => Employee::where('isActive', 1)->count()
            ],
            'workers' => [
                'hoursByMonth' => $workerHoursByMonth,
                'totalHours' => trim(number_format($workerTotalHours, 2, '.', "'"), '0')
            ]
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
            ->get();

        $allBeds = BedRoomPivot::with(['bed', 'room'])->get()->toArray();
        $amountOfAllBeds = array_sum(array_map(function ($bedRoomPivot) {
            if ($bedRoomPivot['room'] && $bedRoomPivot['room']['deleted_at']) {
                return 0;
            }

            return $bedRoomPivot['bed'] && $bedRoomPivot['bed']['places'];
        }, $allBeds));
        $stats = [
            'freePlaces' => $amountOfAllBeds - count($beds),
            'usedPlaces' => count($beds),
            'totalPlaces' => $amountOfAllBeds,
        ];

        return $stats;
    }

    private function workerHoursByMonth($startDate, $endDate) {
       $hoursByMonth = Hour::selectRaw("date, sum(duration) as duration, MONTH(date) as month, YEAR(date) as year")
        ->groupBy('month', 'year')
        ->orderBy('date')
        ->where('date', '>=', $startDate)
        ->where('date', '<=', $endDate)
        ->get();

        return $this->toLineChart($hoursByMonth, $startDate, $endDate);
    }

    private function employeeHoursByMonth($startDate, $endDate) {
        $hoursByMonth = Rapportdetail::selectRaw('date, sum(hours) as duration, MONTH(date) as month, YEAR(date) as year')
            ->groupBy('month', 'year')
            ->orderBy('date')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        return $this->toLineChart($hoursByMonth, $startDate, $endDate);
    }

    private function toLineChart($items, $startDate, $endDate) {
        $items = $items->reduce(function ($prev, $curr) {
            $prev[$this->monthWithYear(new Carbon($curr->date))] = $curr;
            return $prev;
        }, []);

        $chart = collect();
        for($i = $startDate->clone(); $i->lessThanOrEqualTo($endDate); $i->addMonth()) {
            if (isset($items[$this->monthWithYear($i)])) {
                $chart->push([
                    'name' => Settings::getShortMonthName($i),
                    'hours' => round($items[$this->monthWithYear($i)]->duration, 2)
                ]);
            } else {
                $chart->push([
                    'name' => Settings::getShortMonthName($i),
                    'hours' => 0
                ]);
            }
        }

        return $chart;
    }

    private function monthWithYear($date) {
        return $date->year . '-' . $date->month;
    }
}
