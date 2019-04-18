<?php

namespace App\Http\Controllers;

use Fpdf;
use App\User;
use App\Rapport;
use App\Employee;
use App\Customer;
use App\Helpers\Pdf;
use App\Rapportdetail;
use App\Enums\WorkTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\FoodTypeEnum;

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

    // GET rapport/{id}/pdf
    public function rapportWeek(Request $request, Rapport $rapport)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf();

        $header = ['Wochentag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        $comments = ['Bemerkung', $rapport->comment_mo, $rapport->comment_tu, $rapport->comment_we, $rapport->comment_th, $rapport->comment_fr, $rapport->comment_sa];
        $rapportdetailsGruped = $rapport->rapportdetails->groupBy('employee_id');

        $totalTime = 0;
        foreach ($rapportdetailsGruped as $rapportdetails) {
            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $totalTime += $rapportdetail->hours;
                $counter++;
            }
        }

        $startdate = new \DateTime($rapport->startdate);

        $this->pdf->documentTitle("Kunde: {$rapport->customer->customer_number} {$rapport->customer->firstname} {$rapport->customer->lastname}");
        $this->pdf->documentTitle("Zeitraum: {$startdate->format('Y')} / KW {$startdate->format('W')} ({$startdate->format('d.m.Y')} - {$startdate->modify('+6 day')->format('d.m.Y')})");
        $this->pdf->documentTitle("Totale Arbeitsstunden: {$totalTime}");
        $this->pdf->newLine();

        $timePerDay = ['Totale Stunden', 0, 0, 0, 0, 0, 0];
        $lines = [$comments];
        foreach ($rapportdetailsGruped as $rapportdetails) {
            $cells = [$rapportdetails[0]->employee->name()];

            $counter = 1;
            foreach ($rapportdetails as $rapportdetail) {
                $cell = $rapportdetail->hours ? $rapportdetail->hours : 0;
                // $cell = $hasNonCommonProject ? $cell . "\n" : $cell;
                if ($rapportdetail->project && $rapportdetail->project->name != "Allgemein") {
                    $cell = "{$cell} ({$rapportdetail->project->name})";
                }
                array_push($cells, $cell);
                $timePerDay[$counter] += $rapportdetail->hours;
                $counter++;
            }
            array_push($lines, $cells);
        }

        array_push($lines, $timePerDay);

        $this->pdf->table($header, $lines, [], ['lastLineBold' => true]);

        $this->pdf->export("pdf/{$rapport->customer->firstname}_{$rapport->customer->lastname}_{$startdate->modify('-6 day')->format('d-m-Y')}.pdf");
    }

    // GET pdf/worker/month/{month}
    public function workerMonthRapport(Request $request, $month)
    {
        Pdf::validateToken($request->token);

        $firstDayOfMonth = new \Datetime($month);
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        $workers = [];
        $workerId = $request->workerId;

        if (isset($request->workerId)) {
            array_push($workers, User::find($request->workerId));
        } else {
            foreach (User::workers()->get()->sortBy('lastname') as $worker) {
                if ($worker->totalHours($firstDayOfMonth) > 0) {
                    array_push($workers, $worker);
                }
            }
        }

        $this->pdf = new Pdf();
        $monthName = $this->monthNames[intval($firstDayOfMonth->format('m')) - 1];
        if (!isset($request->workerId)) {
            $this->pdf->documentTitle("Hofmitarbeiter Monatsrapport: $monthName");

            // FrontPage
            $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen (Total)'];
            $lines = [];
            foreach ($workers as $worker) {
                $line = [
                    $worker->lastname . " " . $worker->firstname,
                    $worker->totalHours($firstDayOfMonth),
                    array_sum($worker->getNumberOfMeals($firstDayOfMonth))
                ];
                array_push($lines, $line);
            }
            $this->pdf->table($titles, $lines);
            // $this->generateTable($titles, $lines);
        }

        foreach ($workers as $worker) {
            if (!isset($request->workerId)) {
                Fpdf::addPage('F');
            }
            $this->pdf->documentTitle("Mitarbeiter: $worker->lastname $worker->firstname");
            $this->pdf->documentTitle("Monat: $monthName");
            $this->pdf->documentTitle("Totale Arbeitsstunden: {$worker->totalHours($firstDayOfMonth)}h");
            $this->pdf->documentTitle("Produktiv: {$worker->totalHours($firstDayOfMonth, WorkTypeEnum::ProductiveHours)}h", $this->pdf->textSize);
            $this->pdf->documentTitle("Ferien: {$worker->totalHours($firstDayOfMonth, WorkTypeEnum::Holydays)}h", $this->pdf->textSize);
            $this->pdf->documentTitle("Krank: {$worker->totalHours($firstDayOfMonth, WorkTypeEnum::Sick)}h", $this->pdf->textSize);
            $this->pdf->documentTitle("Unfall: {$worker->totalHours($firstDayOfMonth, WorkTypeEnum::Accident)}h", $this->pdf->textSize);
            $meals = $worker->getNumberOfMeals($firstDayOfMonth);
            $this->pdf->documentTitle("Frühstück: {$meals['breakfast']}, Zmittagessen: {$meals['lunch']}, Abendessen: {$meals['dinner']}", $this->pdf->textSize);
            $this->pdf->newLine();

            $lines = [];
            $comments = [];
            $currentDay = clone $firstDayOfMonth;
            $currentDay->modify('monday this week');
            $firstCellWidth = 3;
            for ($i = 0; $i < 6; $i++) {
                $line = [];
                $totalHoursOfWeek = 0;
                for ($j = 0; $j < 7; $j++) {
                    if ($currentDay < $firstDayOfMonth || $currentDay > $lastDayOfMonth) {
                        array_push($line, null);
                    } else {
                        $timerecord = $worker->timerecords->where('date', $currentDay->format('Y-m-d'))->first();
                        if ($timerecord == null) {
                            array_push($line, 0);
                        } else {
                            foreach ($timerecord->hours as $hour) {
                                if ($hour->comment) {
                                    array_push($comments, [
                                        'text' => $hour->comment,
                                        'date' => $timerecord->date
                                    ]);
                                }
                            }
                            $hours = $timerecord->totalHours();
                            $totalHoursOfWeek += $hours;
                            $worktypeId = $timerecord->worktype() ? $timerecord->worktype()->id : null;
                            $worktype = "";
                            if ($worktypeId == WorkTypeEnum::Accident) {
                                $worktype = "(U)";
                            } else if ($worktypeId == WorkTypeEnum::Sick) {
                                $worktype = "(K)";
                            } else if ($worktypeId == WorkTypeEnum::Holydays) {
                                $worktype = "(F)";
                            }
                            array_push($line, "{$hours} {$worktype}");
                        }
                    }
                    $currentDay->modify('+1 day');
                }
                if ($totalHoursOfWeek > 0) {
                    $lastDayOfWeek = clone $currentDay;
                    $lastDayOfWeek->modify("-1 day");
                    $firstDayOfWeek = clone $lastDayOfWeek;
                    $firstDayOfWeek->modify("-6 days");
                    array_unshift($line, "KW {$firstDayOfWeek->format('W')} ({$firstDayOfWeek->format('d.m.Y')} - {$lastDayOfWeek->format('d.m.Y')})");
                    array_push($lines, $line);
                }
            }
            $titles = $this->dayNames;
            array_unshift($titles, "Zeitraum");
            // $this->generateTable($titles, $lines, 3);
            $this->pdf->table($titles, $lines, [3]);
            if (count($comments) > 0) {
                $this->pdf->newLine();
                $this->pdf->documentTitle('Kommentare');
                $textSize = $this->pdf->textSize;
                if (count($comments) > 6) {
                    $textSize -= 2;
                }

                foreach ($comments as $comment) {
                    $date = new \DateTime($comment['date']);

                    Fpdf::SetFont('Raleway', 'B', $textSize);
                    Fpdf::Cell(35, 6, utf8_decode($date->format('d.m.Y')), 0, 0, 'L', false);


                    Fpdf::SetFont('Raleway', '', $textSize);
                    Fpdf::MultiCell($this->documentWidth - 50, 6, utf8_decode($comment['text']), 1, 'L', false);
                }
            }
            $this->pdf->signaturePlaceHolder();
        }

        if (isset($request->workerId)) {
            $filename = "Monatrapport {$workers[0]->lastname} {$workers[0]->firstname} $monthName.pdf";
        } else {
            $filename = "Monatsrapport Hofmitarbeiter $monthName.pdf";
        }
        $this->pdf->export($filename);
    }

    // GET pdf/employee/year/{year}
    public function employeeYearRapport(Request $request, $year)
    {
        Pdf::validateToken($request->token);

        $employee = Employee::find($request->employee_id);

        $totalHoursOfMonths = $this->getHoursOfEmployeeByYear($employee, $year);
        $totalFoodOfMonths = $this->getFoodOfEmployeeByYear($employee, $year);

        $this->pdf = new Pdf();

        $this->pdf->documentTitle("Mitarbeiter: {$employee->name()}");
        $this->pdf->documentTitle("Jahr: $year");
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . array_sum($totalHoursOfMonths) . "h");
        $this->pdf->newLine();

        $this->addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $year);
        $this->addDetailsForAllMonths($employee, $year, $totalHoursOfMonths);

        $this->pdf->export("Jahresrapport $year {$request->name}.pdf");
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
            ->where('date', '<=', $lastdate->format('Y-m-d'))->get();

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
        $employees = Employee::where('isActive', true)->get()->sortBy('lastname');
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

    // GET overview/customer/year/{year}
    public function customerYearRapport(Request $request, $year)
    {
        Pdf::validateToken($request->token);

        $firstDate = new \DateTime("1.1.$year");
        $lastDate = new \DateTime("31.12.$year");

        $customer = Customer::find($request->customer_id);

        $totalHours = DB::table('rapportdetail')
            ->leftJoin('rapport', 'rapportdetail.rapport_id', '=', 'rapport.id')
            ->leftJoin('customer', 'rapport.customer_id', '=', 'customer.id')
            ->where('customer.id', $customer->id)
            ->where('rapportdetail.date', '>=', $firstDate->format('Y-m-d'))
            ->where('rapportdetail.date', '<=', $lastDate->format('Y-m-d'))
            ->sum('rapportdetail.hours');

        $firstDate->modify("first day of this week");
        $weeks = $customer->rapports
            ->where('startdate', '>=', $firstDate->format('Y-m-d'))
            ->where('startdate', '<=', $lastDate->format('Y-m-d'))
            ->sortBy('startdate');

        $this->pdf = new Pdf('P');

        $this->pdf->documentTitle("Kunde: {$customer->firstname} {$customer->lastname}");
        $this->pdf->documentTitle("Jahr: $year");
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . $totalHours . "h");
        $this->pdf->newLine();

        $titles = ['Kalenderwoche', 'Arbeitszeit Total'];
        $lines = [];
        foreach ($weeks as $week) {
            $startDate = new \DateTime($week->startdate);
            $endDate = clone $startDate;
            $endDate->modify('+6 days');
            if ($startDate->format("Y") != $year) {
                $startDate->modify("first day of january");
                $startDate->modify("+1 year");
            }
            if ($endDate->format("Y") != $year) {
                $endDate->modify("last day of december");
                $endDate->modify("-1 year");
            }
            $hours = $week->rapportdetails->where('date', '>=', $startDate->format('Y-m-d'))
                ->where('date', '<=', $endDate->format('Y-m-d'))->sum('hours');
            array_push($lines, [
                "KW {$startDate->format('W')} ({$startDate->format('d.m.Y')}-{$endDate->format('d.m.Y')})",
                $hours . "h"
            ]);
        }
        $this->pdf->table($titles, $lines);
        $this->pdf->export("Jahresrapport $year {$customer->firstname} {$customer->lastname}.pdf");
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

    private function getFoodOfEmployeeByMonth($fristDayOfMonth, $employee)
    {
        $lastDayOfMonth = clone $fristDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $totalFood = $employee->rapportdetails->where('date', '>=', $fristDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->where('foodtype_id', FoodTypeEnum::Eichhof)
            ->where('hours', '>', 0)
            ->groupBy('date')
            ->count();

        return $totalFood;
    }

    private function addDetailsForAllEmployees($rapportdetails, $monthName)
    {
        $rapportdetailsByEmployee = $rapportdetails->groupBy('employee_id');
        foreach ($rapportdetailsByEmployee as $details) {
            Fpdf::AddPage('L');
            $this->pdf->documentTitle("Mitarbeiter: {$details[0]->employee->name()}");
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
            $totalFood = $rapportdetailsByEmployee->where('foodtype_id', FoodTypeEnum::Eichhof)
                ->where('hours', '>', 0)->groupBy('date')->count();
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
                Fpdf::AddPage('L');
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

        $this->pdf->documentTitle("Monat: " . $montName);
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . $totalHours . "h");
        $totalFood = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee);
        $this->pdf->documentTitle("Verpflegungen auf dem Eichhof: $totalFood");
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
