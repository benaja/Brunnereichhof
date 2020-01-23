<?php

namespace App\Http\Controllers;

use Fpdf;
use App\User;
use App\Rapport;
use App\Employee;
use App\Customer;
use App\Helpers\Pdf;
use App\Rapportdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\FoodTypeEnum;
use App\Worktype;

class PdfController extends Controller
{
    private $monthNames = [
        "Januar", "Februar", "März", "April", "Mai", "Juni",
        "Juli", "August", "September", "Oktober", "November", "Dezember"
    ];
    private $dayNames = [
        'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'
    ];
    private $documentWidth = 270;
    private $pdf;

    // GET pdf/employee/year/{year}
    public function employeeYearRapport(Request $request, $employeeId, $year)
    {
        Pdf::validateToken($request->token);

        $employee = Employee::find($employeeId);
        $year = (new \DateTime($year))->format('Y');

        $totalHoursOfMonths = $this->getHoursOfEmployeeByYear($employee, $year);
        $totalFoodOfMonths = $this->getFoodOfEmployeeByYear($employee, $year);

        $this->pdf = new Pdf();

        $this->pdf->documentTitle("Mitarbeiter: {$employee->name()}");
        $this->pdf->documentTitle("Jahr: $year");
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . array_sum($totalHoursOfMonths) . "h");
        $this->pdf->newLine();

        $this->addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $year);
        $this->addDetailsForAllMonths($employee, $year, $totalHoursOfMonths);

        $this->pdf->export("Jahresrapport $year {$employee->lastname} {$employee->firstname}.pdf");
    }

    // GET overview/employee/month/{month}
    public function employeeMonthRapport(Request $request, $month)
    {
        Pdf::validateToken($request->token);

        $firstdate = new \Datetime($month);

        $firstdate->modify("first day of this month");

        $lastdate = clone $firstdate;
        $lastdate->modify('last day of this month');

        $rapportdetails = Rapportdetail::where('date', '>=', $firstdate->format('Y-m-d'))
            ->where('date', '<=', $lastdate->format('Y-m-d'))
            ->join('employee', 'employee.id', '=', 'rapportdetail.employee_id')
            ->orderBy('lastname')
            ->get();

        $this->pdf = new Pdf();
        $monthName = $this->monthNames[intval($firstdate->format('m')) - 1];
        $this->pdf->documentTitle("Monatsrapport: $monthName {$firstdate->format('Y')}");
        $this->addMonthOverviewForAllEmployees($rapportdetails);
        $this->addDetailsForAllEmployees($rapportdetails, $monthName);

        $filename = "Monatsrapport $monthName {$firstdate->format('Y')}.pdf";
        $this->pdf->export($filename);
    }

    // GET pdf/employees
    public function employeeList(Request $request)
    {
        Pdf::validateToken($request->token);

        $this->pdf = new Pdf();
        $employees = Employee::where('isActive', true)->where('isGuest', false)->get()->sortBy('lastname', SORT_NATURAL | SORT_FLAG_CASE);
        $numberOfEmployee = count($employees);

        $this->pdf->documentTitle("Mitarbeiterliste");
        $this->pdf->documentTitle("Anzahl aktive Mitarbeiter: $numberOfEmployee");

        $titles = ['Nachname', 'Vorname', 'Ruffname', 'Nationalität', 'Fahrer', 'Deutschkenntnis...', 'Geschlecht'];
        $lines = [];
        foreach ($employees as $employee) {
            array_push($lines, [
                $employee->lastname,
                $employee->firstname,
                $employee->callname,
                $employee->nationality,
                $employee->isDriver == 1 ? "Ja" : "Nein",
                $employee->german_knowledge == 1 ? "Ja" : "Nein",
                $employee->sex == "man" ? "Mann" : "Frau"
            ]);
        }
        $this->pdf->table($titles, $lines);
        $this->pdf->export("Mitarbeiterliste.pdf");
    }

    // --helpers--
    private function addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $year)
    {
        $header = ['Monat', 'Arbeitszeit Total', 'Verpflegungen'];
        $currentMonth = 0;
        $lines = [];
        foreach ($totalHoursOfMonths as $hours) {
            if ($hours > 0) {
                array_push($lines, [
                    $this->monthNames[$currentMonth] . " " . $year,
                    $totalHoursOfMonths[$currentMonth] . "h",
                    $totalFoodOfMonths[$currentMonth]
                ]);
            }
            $currentMonth++;
        }
        $this->pdf->table($header, $lines);
    }

    private function getHoursOfEmployeeByYear($employee, $year)
    {
        $totalHours = array();
        $firstOfMonth = new \DateTime("1.1.$year");
        for ($i = 0; $i < 12; $i++) {
            $hours = $this->getHoursOfEmployeeByMonth($firstOfMonth, $employee);
            array_push($totalHours, $hours);
            $firstOfMonth->modify("+1 month");
        }

        return $totalHours;
    }

    private function getHoursOfEmployeeByMonth($fristDayOfMonth, $employee)
    {
        $lastDayOfMonth = clone $fristDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $totalHours = $employee->rapportdetails->where('date', '>=', $fristDayOfMonth->format('Y-m-d'))->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))->sum('hours');

        return $totalHours;
    }

    private function getFoodOfEmployeeByYear($employee, $year)
    {
        $totalFood = array();
        $firstOfMonth = new \DateTime("1.1.$year");
        for ($i = 0; $i < 12; $i++) {
            $food = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee);
            array_push($totalFood, $food);
            $firstOfMonth->modify("+1 month");
        }

        return $totalFood;
    }

    private function getFoodOfEmployeeByMonth($firstDayOfMonth, $employee, $foodType = [FoodTypeEnum::Eichhof, FoodTypeEnum::Customer])
    {
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        return $employee->getFoodAmountBetweenDates($firstDayOfMonth, $lastDayOfMonth, $foodType);
    }

    private function addDetailsForAllEmployees($rapportdetails, $monthName)
    {
        $rapportdetailsByEmployee = $rapportdetails->groupBy('employee_id');
        foreach ($rapportdetailsByEmployee as $details) {
            $this->pdf->addNewPage('L');
            $totalHours = $details->sum('hours');
            $firstOfMonth = new \DateTime($rapportdetails[0]->date);
            $firstOfMonth->modify("first day of this month");
            $this->addDetailsForMonth($details[0]->employee, $firstOfMonth, $monthName, $totalHours);
        }
    }

    private function addMonthOverviewForAllEmployees($rapportdetails)
    {
        $this->pdf->newLine();
        $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen'];
        $employees = $rapportdetails->groupBy('employee_id');

        $lines = [];
        foreach ($employees as $rapportdetailsByEmployee) {
            $totalHours = $rapportdetailsByEmployee->sum('hours');
            $totalFood = $rapportdetailsByEmployee
                ->where('hours', '>', 0)
                ->whereIn('foodtype_id', [FoodTypeEnum::Eichhof, FoodTypeEnum::Customer])
                ->groupBy('date')
                ->count();
            array_push($lines, [
                $rapportdetailsByEmployee[0]->employee->name(),
                $totalHours . 'h',
                $totalFood
            ]);
        }
        $this->pdf->table($titles, $lines);
    }

    private function addDetailsForAllMonths($employee, $year, $totalHoursOfMonths)
    {
        $firstOfMonth = new \DateTime("1.1.$year");
        for ($i = 0; $i < 12; $i++) {
            if ($totalHoursOfMonths[$i] > 0) {
                $this->pdf->AddNewPage('L');
                $this->addDetailsForMonth($employee, $firstOfMonth, $this->monthNames[$i], $totalHoursOfMonths[$i]);
            }
            $firstOfMonth->modify("+1 month");
        }
    }

    private function addDetailsForMonth($employee, $firstOfMonth, $montName, $totalHours)
    {
        $lastOfMonth = clone $firstOfMonth;
        $lastOfMonth->modify("last day of this month");

        $titles = ['Zeitraum', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

        $this->pdf->documentTitle("Mitarbeiter: {$employee->name()}");
        $this->pdf->documentTitle("Monat: " . $montName);
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . $totalHours . "h");
        $totalFoodEichhof = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee, [FoodTypeEnum::Eichhof]);
        $totalFoodCustomer = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee, [FoodTypeEnum::Customer]);
        $this->pdf->documentTitle("Verpflegungen auf dem Eichhof: $totalFoodEichhof");
        $this->pdf->documentTitle("Verpflegungen bei Kunde: $totalFoodCustomer");
        $this->pdf->newLine();
        $this->addAllWeeks($titles, $firstOfMonth, $lastOfMonth, $employee);
    }

    private function addAllWeeks($titles, $firstOfMonth, $lastOfMonth, $employee)
    {
        $firstDayOfWeek = clone $firstOfMonth;
        $firstDayOfWeek->modify("Monday this week");
        $lastDayOfWeek = clone $firstDayOfWeek;
        $lastDayOfWeek->modify("+6 Days");
        if ($lastDayOfWeek == $firstOfMonth) {
            $firstDayOfWeek->modify("+1 week");
            $lastDayOfWeek->modify("+1 week");
        }
        $lines = [];
        while ($firstDayOfWeek <= $lastOfMonth) {
            // $firstDayOfWeek
            $firstDay = $firstDayOfWeek >= $firstOfMonth ? $firstDayOfWeek : $firstOfMonth;
            $lastday = $lastDayOfWeek <= $lastOfMonth ? $lastDayOfWeek : $lastOfMonth;
            $rapportdetailsOfWeek = $employee->rapportdetails->where('date', '>=', $firstDay->format('Y-m-d'))->where('date', '<=', $lastday->format('Y-m-d'))->sortBy('date');
            if (count($rapportdetailsOfWeek) > 0) {
                $weeks = $this->getWeek($rapportdetailsOfWeek, $firstDayOfWeek, $lastDayOfWeek, $lastOfMonth, $firstOfMonth);
                // $projects = $this->getProjectsToWeek($rapportdetailsOfWeek, $firstDayOfWeek, $lastDayOfWeek, $lastOfMonth, $firstOfMonth);
                array_push($lines, $weeks);
                // array_push($lines, $projects);
            }
            $firstDayOfWeek->modify("+7 days");
            $lastDayOfWeek->modify("+7 days");
        }
        $this->pdf->table($titles, $lines, [3]);
        $this->pdf->signaturePlaceHolder();
    }

    private function getProjectsToWeek($rapportdetails, $firstDayOfWeek, $lastDayOfWeek, $lastDayOfMonth, $firstdayOfMonth)
    {
        $cells = [''];
        $currentDay = clone $firstDayOfWeek;
        $currentDayNumber = 0;
        $rapportdetails = $this->normalizeArray($rapportdetails);
        for ($i = 0; $i < 6; $i++) {
            if ($currentDay->format('Y-m-d') == $rapportdetails[$currentDayNumber]->date && $currentDay <= $lastDayOfMonth && $currentDay >= $firstdayOfMonth) {
                if ($rapportdetails[$currentDayNumber]->project != null) {
                    $project = $rapportdetails[$currentDayNumber]->project->name;
                    array_push($cells, $project);
                } else {
                    array_push($cells, '');
                }
                $currentDayNumber++;
            } else {
                if ($currentDay >= $firstdayOfMonth || $currentDay <= $lastDayOfMonth) {
                    $currentDayNumber++;
                }
                array_push($cells, '0');
            }
            $currentDay->modify("+1 day");
        }
        return $cells;
    }

    private function getWeek($rapportdetails, $firstDayOfWeek, $lastDayOfWeek, $lastDayOfMonth, $firstdayOfMonth)
    {
        $cells = ["KW {$firstDayOfWeek->format('W')} ({$firstDayOfWeek->format('d.m.Y')} - {$lastDayOfWeek->format('d.m.Y')})"];

        $currentDay = clone $firstDayOfWeek;
        $rapportdetails = $rapportdetails->groupBy('date');
        for ($i = 0; $i < 6; $i++) {
            if (isset($rapportdetails[$currentDay->format('Y-m-d')])) {
                $rapportdetailsThisDay = $rapportdetails[$currentDay->format('Y-m-d')];
                $hours = 0;
                foreach ($rapportdetailsThisDay as $rapportdetail) {
                    $hours += $rapportdetail->hours;
                }
                array_push($cells, $hours);
            } else {
                array_push($cells, '');
            }
            $currentDay->modify("+1 day");
        }
        return $cells;
    }

    private function normalizeArray($array)
    {
        $newArray = [];
        foreach ($array as $object) {
            array_push($newArray, $object);
        }

        return $newArray;
    }
}
