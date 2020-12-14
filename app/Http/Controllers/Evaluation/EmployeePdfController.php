<?php

namespace App\Http\Controllers\Evaluation;

use App\Employee;
use App\Helpers\Pdf;
use App\Rapportdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Enums\FoodTypeEnum;
use App\Helpers\Settings as HelpersSettings;
use App\Helpers\Utils;
use App\Settings;
use App\Transaction;
use Carbon\Carbon;

class EmployeePdfController extends Controller
{
    private $monthNames = [
        "Januar", "Februar", "März", "April", "Mai", "Juni",
        "Juli", "August", "September", "Oktober", "November", "Dezember"
    ];
    private $pdf;
    private $rapportFoodTypeEnabled = false;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET pdf/rapports/employees/{employeeId}
    public function yearRapport(Request $request, $employeeId)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $this->rapportFoodTypeEnabled = Settings::value('rapportFoodTypeEnabled');

        $employee = Employee::find($employeeId);
        $firstDayOfYear = Utils::firstDate('year', $request->date);

        $totalHoursOfMonths = $this->getHoursOfEmployeeByYear($employee, $firstDayOfYear);

        $totalFoodOfMonths = 0;
        if ($this->rapportFoodTypeEnabled) {
            $totalFoodOfMonths = $this->getFoodOfEmployeeByYear($employee, $firstDayOfYear);
        }

        $this->pdf = new Pdf();

        $this->pdf->documentTitle("Mitarbeiter: {$employee->name()}");
        $this->pdf->documentTitle("Jahr: {$firstDayOfYear->format('Y')}");
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . array_sum($totalHoursOfMonths) . "h");
        $this->pdf->newLine();

        $this->addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $firstDayOfYear);
        $this->addDetailsForAllMonths($employee, $firstDayOfYear, $totalHoursOfMonths);

        return $this->pdf->export("Jahresrapport {$firstDayOfYear->format('Y')} {$employee->lastname} {$employee->firstname}.pdf");
    }

    // GET pdf/rapports/employees
    public function monthRapport(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $this->rapportFoodTypeEnabled = Settings::value('rapportFoodTypeEnabled');
        $firstDate = Utils::firstDate('month', $request->date);
        $lastDate = Utils::lastDate('month', $request->date);

        $rapportdetails = Rapportdetail::with('employee.user')
            ->with(['employee.transactions' => function ($query) {
                $query->where('entered', 0)->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type_id');
            }])
            ->where('date', '>=', $firstDate->format('Y-m-d'))
            ->where('date', '<=', $lastDate->format('Y-m-d'))
            ->join('employee', 'employee.id', '=', 'rapportdetail.employee_id')
            ->get()
            ->sortBy('employee.user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $this->pdf = new Pdf();
        $monthName = HelpersSettings::getMonthName($firstDate);
        $pdfTitle = "Monatsrapport: $monthName {$firstDate->format('Y')}";
        $this->pdf->documentTitle($pdfTitle);
        $this->pdf->textToInsertOnPageBreak = $pdfTitle;
        $this->addMonthOverviewForAllEmployees($rapportdetails);
        $this->addDetailsForAllEmployees($rapportdetails, $monthName);

        $filename = "Monatsrapport $monthName {$firstDate->format('Y')}.pdf";
        return $this->pdf->export($filename);
    }

    // GET pdf/employees
    public function employeeList(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $this->pdf = new Pdf();
        $employees = Employee::where('isActive', true)->where('isGuest', false)->get()->sortBy('lastname', SORT_NATURAL | SORT_FLAG_CASE);
        $numberOfEmployee = count($employees);

        $this->pdf->documentTitle("Mitarbeiterliste");
        $this->pdf->documentTitle("Anzahl aktive Mitarbeiter: $numberOfEmployee");

        $titles = [
            'Nachname',
            'Vorname',
            'Ruffname',
            'Fahrer',
            'Führerschein',
            'Deutschkenntnisse',
            'Geschlecht',
            'Arbeitseintrittsjahr'
        ];
        $lines = [];
        foreach ($employees as $employee) {
            array_push($lines, [
                $employee->lastname,
                $employee->firstname,
                $employee->callname,
                $employee->isDriver == 1 ? "Ja" : "Nein",
                $employee->drivingLicence ? "Ja" : "Nein",
                $employee->german_knowledge == 1 ? "Ja" : "Nein",
                $employee->sex == "man" ? "Mann" : "Frau",
                $employee->entryDate ? $employee->entryDate->format('Y') : ''
            ]);
        }
        $this->pdf->table($titles, $lines, [1, 1, 0.7, 0.4, 0.7, 0.9, 0.6, 1]);
        return $this->pdf->export("Mitarbeiterliste.pdf");
    }

    // GET pdf/foods/employees
    public function foodRapport(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $firstDate = Utils::firstDate($request->dateRangeType, $request->date);
        $lastDate = Utils::lastDate($request->dateRangeType, $request->date);
        $employees = Employee::with('user')
            ->withTrashed()
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($request->dateRangeType === 'year') {
            $title = "Jahr: {$firstDate->format('Y')}";
        } else {
            $monthName = HelpersSettings::getMonthName($firstDate);
            $title = "$monthName {$firstDate->format('Y')}";
        }

        $this->pdf = new Pdf('P');
        $this->generateFoodPage($employees, $firstDate, $lastDate, $title, false);

        if ($request->dateRangeType === 'year') {
            $firstDayOfMonth = clone $firstDate;
            for ($i = 0; $i < 12; $i++) {
                $lastDayOfMonth = Utils::lastDate('month', $firstDayOfMonth);
                $monthName = HelpersSettings::getMonthName($firstDayOfMonth);
                $this->generateFoodPage($employees, $firstDayOfMonth, $lastDayOfMonth, "$monthName {$firstDayOfMonth->format('Y')}");
                $firstDayOfMonth->modify('first day of next month');
            }
            
            return $this->pdf->export("Verpflegungen Mitarbeiter {$firstDate->format('Y')}.pdf");
        }
        return $this->pdf->export("Verpflegungen Mitarbeiter $title.pdf"); 
    }

    // --helpers--
    private function addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $date)
    {
        $header = ['Monat', 'Arbeitszeit Total'];
        if ($this->rapportFoodTypeEnabled) {
            array_push($header, 'Verpflegungen');
        }
        $currentMonth = 0;
        $lines = [];
        foreach ($totalHoursOfMonths as $hours) {
            if ($hours > 0) {
                $line = [
                    $this->monthNames[$currentMonth] . " " . $date->format('Y'),
                    $totalHoursOfMonths[$currentMonth] . "h"
                ];
                if ($this->rapportFoodTypeEnabled) {
                    array_push($line, $totalFoodOfMonths[$currentMonth]);
                }
                array_push($lines, $line);
            }
            $currentMonth++;
        }
        $this->pdf->table($header, $lines);
    }

    private function getHoursOfEmployeeByYear($employee, $year)
    {
        $totalHours = array();
        $firstOfMonth = Utils::firstDate('year', $year);
        for ($i = 0; $i < 12; $i++) {
            $hours = $this->getHoursOfEmployeeByMonth($firstOfMonth, $employee);
            array_push($totalHours, $hours);
            $firstOfMonth->modify("+1 month");
        }

        return $totalHours;
    }

    private function getHoursOfEmployeeByMonth($fristDayOfMonth, $employee)
    {
        $lastDayOfMonth = Utils::lastDate('month', $fristDayOfMonth);
        return $employee->rapportdetails->where('date', '>=', $fristDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->sum('hours');
    }

    private function getFoodOfEmployeeByYear($employee, $date)
    {
        $totalFood = array();
        $firstOfMonth = Utils::firstDate('month', $date);
        for ($i = 0; $i < 12; $i++) {
            $food = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee);
            array_push($totalFood, $food);
            $firstOfMonth->modify("+1 month");
        }

        return $totalFood;
    }

    private function getFoodOfEmployeeByMonth($firstDayOfMonth, $employee, $foodType = [FoodTypeEnum::Eichhof, FoodTypeEnum::Customer])
    {
        $lastDayOfMonth = Utils::lastDate('month', $firstDayOfMonth);
        return $employee->getFoodAmountBetweenDates($firstDayOfMonth, $lastDayOfMonth, $foodType);
    }

    private function addDetailsForAllEmployees($rapportdetails, $monthName)
    {
        $rapportdetailsByEmployee = $rapportdetails->groupBy('employee_id');
        foreach ($rapportdetailsByEmployee as $details) {
            $this->pdf->addNewPage('L');
            $totalHours = $details->sum('hours');
            $firstOfMonth = Utils::firstDate('month', new \DateTime($rapportdetails[0]->date));
            $this->addDetailsForMonth($details[0]->employee, $firstOfMonth, $monthName, $totalHours, true);
        }
    }

    private function addMonthOverviewForAllEmployees($rapportdetails)
    {
        $this->pdf->newLine();
        $titles = ['Mitarbeiter', 'Arbeitszeit Total'];
        if ($this->rapportFoodTypeEnabled) {
            array_push($titles, 'Verpflegungen');
        }
        $employees = $rapportdetails->groupBy('employee_id');

        $lines = [];
        foreach ($employees as $rapportdetailsByEmployee) {
            $totalHours = $rapportdetailsByEmployee->sum('hours');
            $line = [
                $rapportdetailsByEmployee[0]->employee->name(),
                $totalHours . 'h'
            ];
            if ($this->rapportFoodTypeEnabled) {
                $totalFood = $rapportdetailsByEmployee
                    ->where('hours', '>', 0)
                    ->whereIn('foodtype_id', [FoodTypeEnum::Eichhof, FoodTypeEnum::Customer])
                    ->groupBy('date')
                    ->count();
                array_push($line, $totalFood);
            }
            array_push($lines, $line);
        }
        $this->pdf->table($titles, $lines);
    }

    private function addDetailsForAllMonths($employee, $date, $totalHoursOfMonths)
    {
        $firstOfMonth = Utils::firstDate('month', $date);
        for ($i = 0; $i < 12; $i++) {
            if ($totalHoursOfMonths[$i] > 0) {
                $this->pdf->AddNewPage('L');
                $this->addDetailsForMonth($employee, $firstOfMonth, $this->monthNames[$i], $totalHoursOfMonths[$i]);
            }
            $firstOfMonth->modify("+1 month");
        }
    }

    private function addDetailsForMonth($employee, $firstOfMonth, $monthName, $totalHours, $withTransactions = false)
    {
        $lastOfMonth = Utils::lastDate('month', $firstOfMonth);
        $titles = ['Zeitraum', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        $this->pdf->textToInsertOnPageBreak = "Mitarbeiter:{$employee->name()} \nMonat: $monthName";

        $this->pdf->documentTitle("Mitarbeiter: {$employee->name()}");
        $this->pdf->documentTitle("Monat: " . $monthName);
        $this->pdf->documentTitle("Totale Arbeitsstunden: " . $totalHours . "h");
        if ($this->rapportFoodTypeEnabled) {
            $totalFoodEichhof = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee, [FoodTypeEnum::Eichhof]);
            $totalFoodCustomer = $this->getFoodOfEmployeeByMonth($firstOfMonth, $employee, [FoodTypeEnum::Customer]);
            $this->pdf->documentTitle("Verpflegungen auf dem Eichhof: $totalFoodEichhof");
            $this->pdf->documentTitle("Verpflegungen bei Kunde: $totalFoodCustomer");
        }
        $this->pdf->newLine();
        $this->addAllWeeks($titles, $firstOfMonth, $lastOfMonth, $employee, $withTransactions);
    }

    private function addAllWeeks($titles, $firstOfMonth, $lastOfMonth, $employee, $withTransactions)
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
            $firstDay = $firstDayOfWeek >= $firstOfMonth ? $firstDayOfWeek : $firstOfMonth;
            $lastday = $lastDayOfWeek <= $lastOfMonth ? $lastDayOfWeek : $lastOfMonth;
            $rapportdetailsOfWeek = $employee->rapportdetails->where('date', '>=', $firstDay->format('Y-m-d'))->where('date', '<=', $lastday->format('Y-m-d'))->sortBy('date');
            if (count($rapportdetailsOfWeek) > 0) {
                $weeks = $this->getWeek($rapportdetailsOfWeek, $firstDayOfWeek, $lastDayOfWeek);
                array_push($lines, $weeks);
            }
            $firstDayOfWeek->modify("+7 days");
            $lastDayOfWeek->modify("+7 days");
        }
        $this->pdf->table($titles, $lines, [3]);

        if ($withTransactions && count($employee->transactions) > 0) {
            $lines = [];
            foreach($employee->transactions as $transaction) {
                array_push($lines, [$transaction->amount, $transaction->date->format('d.m.Y'), $transaction->name, $transaction->comment]);
            }
            if (count($lines) > 1) {
                $openAmount = $employee->transactions->sum('amount');
                array_push($lines, ['Total: '. $openAmount]);
            }
            $this->pdf->SetX(10);
            $this->pdf->documentTitle('Vorauszahlungen');
            $this->pdf->table(['Menge', 'Datum', 'Vorschuss Typ', 'Bemerkung'], $lines, [1, 1, 1, 2], ['lastLineBold' => count($lines) > 1]);
        }

        $this->pdf->signaturePlaceHolder();
    }

    private function getWeek($rapportdetails, $firstDayOfWeek, $lastDayOfWeek)
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

    private function generateFoodPage($employees, $firstDate, $lastDate, $titleDate, $addNewPage = true)
    {
        $totalFood = Rapportdetail::foodAmountBetweenDates($firstDate, $lastDate);
        if ($totalFood > 0 || !$addNewPage) {
            if ($addNewPage) $this->pdf->addNewPage();
            $this->pdf->textToInsertOnPageBreak = "Verpflegungen Mitarbeiter auf dem Eichhof \n$titleDate \nTotale Verpflegungen: $totalFood";
            $this->pdf->documentTitle($this->pdf->textToInsertOnPageBreak);
            $this->pdf->documentTitle("");
            $this->foodTable($employees, $firstDate, $lastDate);
        }
    }

    
    private function foodTable($employees, $firstDate, $lastDate)
    {
        $columns = [];
        foreach ($employees as $employee) {
            $totalFood = $employee->getFoodAmountBetweenDates($firstDate, $lastDate);
            if ($totalFood > 0) {
                array_push($columns, [
                    $employee->name(),
                    $totalFood
                ]);
            }
        }
        $this->pdf->table(['Mitarbeiter', 'Verpflegungen'], $columns);
    }
}
