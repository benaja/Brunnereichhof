<?php

namespace App\Helpers;

use App\Rapportdetail;
use App\Timerecord;

class StatsHelper
{
    public static function employeeHoursByMonth()
    {
        $firstOfThisMonth = new \DateTime('first day of this month');
        $lastOfThisMonth = new \DateTime('last day of this month');

        $totalHoursOfYear = [];

        for ($i = 0; $i < 12; $i++) {
            $totalHours = Rapportdetail::where('date', '>=', $firstOfThisMonth->format('Y-m-d'))
                ->where('date', '<=', $lastOfThisMonth->format('Y-m-d'))->sum('hours');

            $monthName = Settings::getShortMonthName($firstOfThisMonth);
            // $monthName = $firstOfThisMonth->format('m');
            // $monthName = $this->monthNames[intval($monthName) - 1];

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

    public static function workerHoursByMonth()
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

            $monthName = Settings::getShortMonthName($firstOfThisMonth);
            // $monthName = $firstOfThisMonth->format('m');
            // $monthName = $this->monthNames[intval($monthName) - 1];

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

    public static function workerTotalNumbers()
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
