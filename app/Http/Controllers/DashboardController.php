<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rapportdetail;
use App\Employee;

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

    public function totalHoursByMonth()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $firstOfThisMonth = new \DateTime('first day of this month');
        $lastOfThisMonth = new \DateTime('last day of this month');

        $totalHoursOfYear = [];

        for ($i = 0; $i < 12; $i++) {
            $totalHours = Rapportdetail::where('date', '>=', $firstOfThisMonth->format('Y-m-d'))
                ->where('date', '<=', $lastOfThisMonth->format('Y-m-d'))->sum('hours');

            $monthName = $firstOfThisMonth->format('m');
            $monthName = $this->monthNames[intval($monthName) - 1];

            $month = [
                'hours' => $totalHours,
                'name' => $monthName
            ];

            array_push($totalHoursOfYear, $month);
            $firstOfThisMonth->modify('-1 day')->modify('first day of this month');
            $lastOfThisMonth->modify('-31 days')->modify('last day of this month');
        }

        $totalHoursOfYear = array_reverse($totalHoursOfYear);

        return $totalHoursOfYear;
    }

    public function totalNumbers()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $firstOfThisYear = new \DateTime('first day of this year');
        $lastOfThisYear = new \DateTime('last day of this year');

        $totalHours = Rapportdetail::where('date', '>=', $firstOfThisYear->format('Y-m-d'))
            ->where('date', '<=', $lastOfThisYear->format('Y-m-d'))->sum('hours');

        $employeesAmount = Employee::where('isActive', 1)->count();

        $response = [
            'hours' => $totalHours,
            'acitveEmployees' => $employeesAmount
        ];
        return $response;
    }
}
