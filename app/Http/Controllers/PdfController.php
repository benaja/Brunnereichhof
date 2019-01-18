<?php

namespace App\Http\Controllers;

use Fpdf;
use App\User;
use App\Rapport;
use App\Employee;
use App\Customer;
use App\Rapportdetail;
use Illuminate\Http\Request;
use App\Enums\AuthorizationType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

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

    // GET rapport/{id}/pdf
    public function rapportWeek(Request $request, Rapport $rapport)
    {
        $this->validateToken($request->token);

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf::SetFont('Arial', 'B', 16);
        Fpdf::AddPage('L');
        Fpdf::SetFont('Arial', 'B', 18);

        $header = [
            'Wochentag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
        ];

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

        $cellHeight = 8;

        Fpdf::SetFillColor(38, 166, 154);
        Fpdf::SetDrawColor(255);
        Fpdf::SetLineWidth(.3);
        Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
        Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
        // Fpdf::AddFont('Raleway','I', 'Raleway-Italic.php');

        Fpdf::SetFont('Raleway', 'B', 20);
        Fpdf::Cell(0, 15, utf8_decode("Kunde: {$rapport->customer->customer_number} {$rapport->customer->firstname} {$rapport->customer->lastname}"), 0, 2);
        Fpdf::SetFont('Raleway', '', 18);
        $startdate = new \DateTime($rapport->startdate);
        Fpdf::Cell(0, 12, "Zeitraum: {$startdate->format('Y')} / KW {$startdate->format('W')} ({$startdate->format('d.m.Y')} - {$startdate->modify('+6 day')->format('d.m.Y')})", 0, 2);
        Fpdf::Cell(0, 12, "Totale Arbeitsstunden: {$totalTime}");
        Fpdf::Ln();


        Fpdf::SetFont('Raleway', 'B', 12);
        Fpdf::SetTextColor(255);
        // Header
        $cellWidth = 40;
        for ($i = 0; $i < count($header); $i++) {
            Fpdf::Cell($cellWidth, $cellHeight, $header[$i], 0, 0, 'L', true);
        }
        Fpdf::Ln();
        // Color and font restoration
        Fpdf::SetFillColor(242, 242, 242);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 10);
        // Data
        $fill = false;
        $marginLeft = Fpdf::getX();
        $marginTop = Fpdf::getY();
        foreach ($comments as $comment) {
            Fpdf::SetXY($marginLeft, $marginTop);
            Fpdf::MultiCell(40, $cellHeight, utf8_decode($comment), 'L', $fill);
            $marginLeft += 40;
        }
        Fpdf::Ln();
        $fill = !$fill;


        $timePerDay = [0, 0, 0, 0, 0, 0];
        foreach ($rapportdetailsGruped as $rapportdetails) {
            Fpdf::cell(40, $cellHeight, utf8_decode("{$rapportdetails[0]->employee->firstname} {$rapportdetails[0]->employee->lastname}"), 0, 'R', 'L', $fill);

            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $timePerDay[$counter] += $rapportdetail->hours;
                $counter++;
                Fpdf::cell(40, $cellHeight, $rapportdetail->hours != null ? $rapportdetail->hours . " h" : "0" . " h", 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();


            Fpdf::cell(40, $cellHeight, "", 0, 'R', 'L', $fill);
            foreach ($rapportdetails as $rapportdetail) {
                Fpdf::cell(40, $cellHeight, utf8_decode($rapportdetail->project != null ? $rapportdetail->project->name : "keine Angabe"), 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();
            $fill = !$fill;
        }
        // Closing line
        Fpdf::cell(40, $cellHeight, "Total Zeit", 0, 'R', 'L', $fill);
        foreach ($timePerDay as $time) {
            Fpdf::cell(40, $cellHeight, $time . " h", 0, 'R', 'L', $fill);
        }

        $filename = "pdf/{$rapport->customer->firstname}_{$rapport->customer->lastname}_{$startdate->modify('-6 day')->format('d-m-Y')}.pdf";
        Fpdf::Output('D', $filename);
    }

    // GET pdf/worker/month/{month}
    public function workerMonthRapport(Request $request, $month)
    {
        $this->validateToken($request->token);

        $firstDayOfMonth = new \Datetime($month);
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        $workers = [];

        foreach(User::workers()->get() as $worker){   
            if($worker->totalHours($firstDayOfMonth) > 0){
                array_push($workers, $worker);
            }
        }

        $this->getPdfDefault();
        $monthName = $this->monthNames[intval($firstDayOfMonth->format('m')) - 1];
        $this->addDocumentTitle("Hofmitarbeiter Monatsrapport: $monthName");

        // FrontPage
        $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen'];
        $lines = [];
        foreach($workers as $worker){
            $line = [
                $worker->firstname . " " . $worker->lastname,
                $worker->totalHours($firstDayOfMonth),
                array_sum($worker->getNumberOfMeals($firstDayOfMonth))
            ];
            array_push($lines, $line);
        }
        $this->generateTable($titles, $lines);

        foreach($workers as $worker){
            Fpdf::addPage('F');
            $this->addDocumentTitle("Mitarbeiter: $worker->firstname $worker->lastname");
            $this->addDocumentTitle("Monat: $monthName");
            $this->addDocumentTitle("Totale Arbeitsstunden: {$worker->totalHours($firstDayOfMonth)}h");

            $lines = [];
            $currentDay = clone $firstDayOfMonth;
            $currentDay->modify('monday this week');
            $firstCellWidth = 3;
            for($i = 0; $i < 5; $i++){
                $line = [];
                for($j = 0; $j < 7; $j++){
                    if($currentDay < $firstDayOfMonth || $currentDay > $lastDayOfMonth) {
                        array_push($line, 0);
                    } else {
                        $timerecord = $worker->timerecords->where('date', $currentDay->format('Y-m-d'))->first();
                        if($timerecord == null){
                            array_push($line, 0);
                        } else {
                            array_push($line, $timerecord->totalHours());
                        }
                    }
                    $currentDay->modify('+1 day');
                }
                if(array_sum($line) > 0){
                    $lastDayOfWeek = clone $currentDay;
                    $lastDayOfWeek->modify("+6 days");
                    array_unshift($line, "KW {$currentDay->format('W')} ({$currentDay->format('d.m.Y')} - {$lastDayOfWeek->format('d.m.Y')})");
                    array_push($lines, $line);
                }
            }
            $titles = $this->dayNames;
            array_unshift($titles, "Zeitraum");
            $this->generateTable($titles, $lines, 3);
        }
      
        Fpdf::Output('D', "Monatsrapport Hofmitarbeiter $monthName.pdf");
    }

    // GET pdf/employee/year/{year}
    public function employeeYearRapport(Request $request, $year)
    {
        $this->validateToken($request->token);

        $employee = Employee::find($request->employee_id);

        $totalHoursOfMonths = $this->getHoursOfEmployeeByYear($employee, $year);
        $totalFoodOfMonths = $this->getFoodOfEmployeeByYear($employee, $year);

        $pdf = $this->getPdfDefault();

        
        $this->addDocumentTitle("Mitarbeiter: $employee->firstname $employee->lastname");
        $this->addDocumentTitle("Jahr: $year");
        $this->addDocumentTitle("Totale Arbeitsstunden: " . array_sum($totalHoursOfMonths) . "h");
        Fpdf::Ln();

        $this->addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $year);
        $this->addDetailsForAllMonths($employee, $year, $totalHoursOfMonths);


        $filename = "Jahresrapport $year {$request->name}.pdf";
        $this->exportPage($filename);
    }

    // GET overview/employee/month/{month}
    public function employeeMonthRapport(Request $request, $month)
    {
        $this->validateToken($request->token);

        $firstdate = new \Datetime($month);

        $firstdate->modify("first day of this month");

        $lastdate = clone $firstdate;
        $lastdate->modify('last day of this month');

        $rapportdetails = Rapportdetail::where('date', '>=', $firstdate->format('Y-m-d'))
            ->where('date', '<=', $lastdate->format('Y-m-d'))->get();

        $this->getPdfDefault();
        $monthName = $this->monthNames[intval($firstdate->format('m')) - 1];
        $this->addDocumentTitle("Monatsrapport: $monthName {$firstdate->format('Y')}");
        $this->addMonthOverviewForAllEmployees($rapportdetails);
        $this->addDetailsForAllEmployees($rapportdetails, $monthName);

        $filename = "Monatsrapport $monthName {$firstdate->format('Y')}.pdf";
        $this->exportPage($filename);
    }

    // GET pdf/employees
    public function employeeList(Request $request)
    {
        $this->validateToken($request->token);

        $this->getPdfDefault();
        $employees = Employee::where('isActive', true)->get();
        $numberOfEmployee = count($employees);

        $this->addDocumentTitle("Mitarbeiterliste");
        $this->addDocumentTitle("Anzahl aktive Mitarbeiter: $numberOfEmployee");

        $titles = ['Vorname', 'Nachname', 'Ruffname', 'Nationalität', 'Fahrer', 'Deutschkenntnis...', 'Geschlecht'];
        $this->addTableHeader($titles);

        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        $doFill = false;
        $cellWidht = 270 / count($titles);
        foreach ($employees as $employee) {
            Fpdf::Cell($cellWidht, 8, utf8_decode($employee->firstname), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, utf8_decode($employee->lastname), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, utf8_decode($employee->callname), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, utf8_decode($employee->nationality), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, $employee->isDriver == 1 ? "Ja" : "Nein", 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, $employee->german_knowledge == 1 ? "Ja" : "Nein", 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, utf8_decode($employee->sex), 0, 0, 'L', $doFill);
            Fpdf::Ln();
            $doFill = !$doFill;
        }

        $fileName = "Mitarbeiterliste.pdf";
        $this->exportPage($fileName);
    }

    // GET overview/customer/year/{year}
    public function customerYearRapport(Request $request, $year)
    {
        $this->validateToken($request->token);

        $firstDate = new \DateTime("1.1.$year");
        $lastDate = new \DateTime("31.12.$year");

        $customer = Customer::find($request->customer_id);

        $weeks = $customer->rapports
            ->where('startdate', '>=', $firstDate->format('Y-m-d'))
            ->where('startdate', '<=', $lastDate->format('Y-m-d'))
            ->sortBy('startdate');

        $totalHours = DB::table('rapportdetail')
            ->leftJoin('rapport', 'rapportdetail.rapport_id', '=', 'rapport.id')
            ->leftJoin('customer', 'rapport.customer_id', '=', 'customer.id')
            ->where('customer.id', $customer->id)
            ->where('rapportdetail.date', '>=', $firstDate->format('Y-m-d'))
            ->where('rapportdetail.date', '<=', $lastDate->format('Y-m-d'))
            ->sum('rapportdetail.hours');

        $titles = ['Kalenderwoche', 'Arbeitszeit Total'];
        $this->getPdfDefault('P');

        $this->addDocumentTitle("Kunde: {$customer->firstname} {$customer->lastname}");
        $this->addDocumentTitle("Totale Arbeitsstunden: " . $totalHours . "h");
        Fpdf::Ln();
        $this->documentWidth = 190;
        $this->addTableHeader($titles);
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        $doFill = false;
        $cellWidht = 190 / count($titles);
        foreach ($weeks as $week) {
            $startDate = new \DateTime($week->startdate);
            $endDate = clone $startDate;
            $endDate->modify('+6 days');
            $hours = $week->rapportdetails->sum('hours');
            Fpdf::Cell($cellWidht, 8, "KW {$startDate->format('W')} ({$startDate->format('d.m.Y')}-{$endDate->format('d.m.Y')})", 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidht, 8, $hours . "h", 0, 0, 'L', $doFill);
            Fpdf::Ln();
            $doFill = !$doFill;
        }

        $filename = "Jahresrapport $year {$customer->firstname} {$customer->lastname}.pdf";
        $this->exportPage($filename);
    }

    // --helpers--
    private function getPdfDefault($landscape = "L")
    {
        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf::SetFont('Arial', 'B', 16);

        Fpdf::AddPage($landscape);
        Fpdf::SetFont('Arial', 'B', 18);
        Fpdf::SetFillColor(38, 166, 154);
        Fpdf::SetDrawColor(255);
        Fpdf::SetLineWidth(.3);
        Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
        Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
        // Fpdf::AddFont('Raleway','I', 'Raleway-Italic.php');

        Fpdf::SetFont('Raleway', '', 20);
        return $pdf;
    }

    private function addDocumentTitle($text)
    {
        Fpdf::SetDrawColor(255);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 20);
        Fpdf::Cell(0, 10, utf8_decode($text), 0, 2);
    }

    private function generateTable($titles, $lines, $firstCellWidth = 1)
    {
        $this->addTableHeader($titles, $firstCellWidth);

        $doFill = false;
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        foreach($lines as $line)
        {
            $this->addLine($line, $doFill, $firstCellWidth);
            $doFill = !$doFill;
        }
    }

    private function validateToken($token)
    {
        if ($token != Cache::pull('pdfToken')) {
            abort(401, 'This action is unauthorized.');
        }
    }

    private function addMonthOverview($totalHoursOfMonths, $totalFoodOfMonths, $year)
    {
        $header = ['Monat', 'Arbeitszeit Total', 'Verpflegungen'];
        $this->addTableHeader($header);
        $currentMonth = 0;
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        $doFill = false;
        $cellWidth = 270 / count($header);
        foreach ($totalHoursOfMonths as $hours) {
            if ($hours > 0) {
                Fpdf::Cell($cellWidth, 8, $this->monthNames[$currentMonth] . " " . $year, 0, 0, 'L', $doFill);
                Fpdf::Cell($cellWidth, 8, $totalHoursOfMonths[$currentMonth] . "h", 0, 0, 'L', $doFill);
                Fpdf::Cell($cellWidth, 8, $totalFoodOfMonths[$currentMonth], 0, 0, 'L', $doFill);
                Fpdf::Ln();
            }
            $doFill = !$doFill;
            $currentMonth++;
        }
    }

    private function addTableHeader($titles, $firstCellWidth = 1)
    {
        Fpdf::SetFont('Raleway', 'B', 12);
        Fpdf::SetTextColor(255);
        Fpdf::SetFillColor(38, 166, 154);

        for ($i = 0; $i < count($titles); $i++) {
            $cellWidth = $this->documentWidth / ( count($titles) + $firstCellWidth - 1);
            if($i == 0){
                $cellWidth = $cellWidth * $firstCellWidth;
            }
            Fpdf::Cell($cellWidth, 8, utf8_decode($titles[$i]), 0, 0, 'L', true);
        }
        Fpdf::Ln();
    }

    private function addLine($cells, $doFill, $firstCellWidth = 1){
        $counter = 0;
        foreach ($cells as $cell) {
            $cellWidth = $this->documentWidth / (count($cells) + $firstCellWidth - 1);
            if($counter == 0){
                $cellWidth = $cellWidth * $firstCellWidth;
            }
            Fpdf::Cell($cellWidth, 8, utf8_decode($cell), 0, 0, 'L', $doFill);
            $counter++;
        }
        Fpdf::Ln();
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

        $totalFood = $employee->rapportdetails->where('date', '>=', $fristDayOfMonth->format('Y-m-d'))->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))->where('foodtype_id', 1)->count();

        return $totalFood;
    }

    private function getTotalHoursOfTimerecords($timerecords)
    {
        $totalHours = 0;
        foreach ($timerecords as $timerecord) {
            foreach ($timerecord->hours as $hour) {
                $totalHours += $hour->duration();
            }
        }

        return $totalHours;
    }

    private function generateWorkerMonthDocument($worker, $timerecords)
    {
        Fpdf::Cell(0, 15);
        Fpdf::Ln();
        $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen'];
        $this->addTableHeader($titles);
        $employees = $rapportdetails->groupBy('employee_id');
        $doFill = false;
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        foreach ($employees as $rapportdetaisByEmployee) {
            $cellWidth = 270 / count($titles);
            $totalHours = $rapportdetaisByEmployee->sum('hours');
            $totalFood = $rapportdetaisByEmployee->where('foodtype_id', 1)->count();
            $fullName = $rapportdetaisByEmployee[0]->employee->firstname . " " . $rapportdetaisByEmployee[0]->employee->lastname;
            Fpdf::Cell($cellWidth, 8, utf8_decode($fullName), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidth, 8, utf8_decode($totalHours . "h"), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidth, 8, utf8_decode($totalFood), 0, 0, 'L', $doFill);
            Fpdf::Ln();
            $doFill = !$doFill;
        }
    }

    private function exportPage($filename)
    {
        Fpdf::Output('D', $filename);
    }

    private function addDetailsForAllEmployees($rapportdetails, $monthName)
    {
        $rapportdetailsByEmployee = $rapportdetails->groupBy('employee_id');
        $titles = ['Zeitraum', '', '', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        $cellWidth = 270 / count($titles);
        foreach ($rapportdetailsByEmployee as $details) {
            Fpdf::AddPage('L');
            $this->addDocumentTitle("Mitarbeiter: {$details[0]->employee->firstname} {$details[0]->employee->lastname}");
            $totalHours = $details->sum('hours');
            $firstOfMonth = new \DateTime($rapportdetails[0]->date);
            $firstOfMonth->modify("first day of this month");
            $this->addDetailsForMonth($details[0]->employee, $firstOfMonth, $monthName, $totalHours);
        }
    }

    private function addMonthOverviewForAllEmployees($rapportdetails)
    {
        Fpdf::Cell(0, 15);
        Fpdf::Ln();
        $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen'];
        $this->addTableHeader($titles);
        $employees = $rapportdetails->groupBy('employee_id');
        $doFill = false;
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        foreach ($employees as $rapportdetaisByEmployee) {
            $cellWidth = 270 / count($titles);
            $totalHours = $rapportdetaisByEmployee->sum('hours');
            $totalFood = $rapportdetaisByEmployee->where('foodtype_id', 1)->count();
            $fullName = $rapportdetaisByEmployee[0]->employee->firstname . " " . $rapportdetaisByEmployee[0]->employee->lastname;
            Fpdf::Cell($cellWidth, 8, utf8_decode($fullName), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidth, 8, utf8_decode($totalHours . "h"), 0, 0, 'L', $doFill);
            Fpdf::Cell($cellWidth, 8, utf8_decode($totalFood), 0, 0, 'L', $doFill);
            Fpdf::Ln();
            $doFill = !$doFill;
        }
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

        $rapportdetails = $employee->rapportdetails->where('date', '>=', $firstOfMonth->format('Y-m-d'))->where('date', '<=', $lastOfMonth->format('Y-m-d'))->sortBy('date');
        $titles = ['Zeitraum', '', '', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

        Fpdf::SetDrawColor(255);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 20);
        Fpdf::Cell(0, 10, "Monat: " . $montName, 0, 2);
        Fpdf::Cell(0, 10, "Totale Arbeitsstunden: " . $totalHours . "h", 0, 2);
        Fpdf::Cell(0, 10);
        Fpdf::Ln();
        $this->addTableHeader($titles);
        $this->addAllWeeks($firstOfMonth, $lastOfMonth, $employee);
    }

    private function addAllWeeks($firstOfMonth, $lastOfMonth, $employee)
    {
        $firstDayOfWeek = clone $firstOfMonth;
        $firstDayOfWeek->modify("Monday this week");
        $lastDayOfWeek = clone $firstDayOfWeek;
        $lastDayOfWeek->modify("+6 Days");
        if ($lastDayOfWeek == $firstOfMonth) {
            $firstDayOfWeek->modify("+1 week");
            $lastDayOfWeek->modify("+1 week");
        }
        $doFill = false;
        Fpdf::SetFont('Raleway', '', 12);
        Fpdf::SetTextColor(0);
        Fpdf::SetFillColor(242, 242, 242);
        while ($firstDayOfWeek <= $lastOfMonth) {
            $rapportdetailsOfWeek = $employee->rapportdetails->where('date', '>=', $firstDayOfWeek->format('Y-m-d'))->where('date', '<=', $lastDayOfWeek->format('Y-m-d'))->sortBy('date');
            if (count($rapportdetailsOfWeek) > 0) {
                $this->addWeek($rapportdetailsOfWeek, $firstDayOfWeek, $lastDayOfWeek, $lastOfMonth, $firstOfMonth, $doFill);
                $this->addProjectsToWeek($rapportdetailsOfWeek, $firstDayOfWeek, $lastDayOfWeek, $lastOfMonth, $firstOfMonth);
                $doFill = !$doFill;
            }
            $firstDayOfWeek->modify("+7 days");
            $lastDayOfWeek->modify("+7 days");
        }
    }

    private function addProjectsToWeek($rapportdetails, $firstDayOfWeek, $lastDayOfWeek, $lastDayOfMonth, $firstdayOfMonth)
    {
        $cellWidth = 270 / 9;
        Fpdf::Cell($cellWidth * 3, 8, "", 0, 0, 'L', true);
        $currentDay = clone $firstDayOfWeek;
        $currentDayNumber = 0;
        $rapportdetails = $this->normalizeArray($rapportdetails);
        for ($i = 0; $i < 6; $i++) {
            if ($currentDay->format('Y-m-d') == $rapportdetails[$currentDayNumber]->date && $currentDay <= $lastDayOfMonth && $currentDay >= $firstdayOfMonth) {
                if ($rapportdetails[$currentDayNumber]->project != null) {
                    $project = utf8_decode($rapportdetails[$currentDayNumber]->project->name);
                    Fpdf::Cell($cellWidth, 8, $project, 0, 0, 'L', true);
                } else {
                    Fpdf::Cell($cellWidth, 8, "", 0, 0, 'L', true);
                }
                $currentDayNumber++;
            } else {
                if ($currentDay >= $firstdayOfMonth || $currentDay <= $lastDayOfMonth) {
                    $currentDayNumber++;
                }
                Fpdf::Cell($cellWidth, 8, "", 0, 0, 'L', true);
            }
            $currentDay->modify("+1 day");
        }
        Fpdf::Ln();
    }

    private function addWeek($rapportdetails, $firstDayOfWeek, $lastDayOfWeek, $lastDayOfMonth, $firstdayOfMonth, $doFill)
    {
        $cellWidth = 270 / 9;
        Fpdf::Cell($cellWidth * 3, 8, "KW {$firstDayOfWeek->format('W')} ({$firstDayOfWeek->format('d.m.Y')} - {$lastDayOfWeek->format('d.m.Y')})", 0, 0, 'L', false);
        $currentDay = clone $firstDayOfWeek;
        $currentDayNumber = 0;
        $rapportdetails = $this->normalizeArray($rapportdetails);
        for ($i = 0; $i < 6; $i++) {
            if ($currentDay->format('Y-m-d') == $rapportdetails[$currentDayNumber]->date && $currentDay <= $lastDayOfMonth && $currentDay >= $firstdayOfMonth) {
                $hours = $rapportdetails[$currentDayNumber]->hours == null ? 0 : $rapportdetails[$currentDayNumber]->hours;
                Fpdf::Cell($cellWidth, 8, $hours . "h", 0, 0, 'L', false);
                $currentDayNumber++;
            } else {
                if ($currentDay >= $firstdayOfMonth || $currentDay <= $lastDayOfMonth) {
                    $currentDayNumber++;
                }
                Fpdf::Cell($cellWidth, 8, "", 0, 0, 'L', false);
            }
            $currentDay->modify("+1 day");
        }
        Fpdf::Ln();
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
