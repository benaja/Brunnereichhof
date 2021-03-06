<?php

namespace App\Http\Controllers\Evaluation;

use App\Helpers\Pdf;
use App\Helpers\PdfHelperFunctions;
use App\Helpers\Settings;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Timerecord;
use App\User;
use App\Worktype;
use Illuminate\Http\Request;

class WorkerPdfController extends Controller
{
    private $pdf;

    private $mealTypes = [
        'breakfast' => 'Frühstück',
        'lunch' => 'Mittagessen',
        'dinner' => 'Abendessen',
    ];

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET pdf/timerecords/wokers/{workerId}
    public function timerecords(Request $request, $workerId)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_stats']);

        if (isset($request->month)) {
            return $this->timerecordsMonthRapport($workerId, $request->month);
        } else {
            return $this->timerecordsYearRapport($workerId, $request->year);
        }
    }

    // GET pdf/timerecord/meals
    public function meals(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_stats']);

        $firstDate = Utils::firstDate($request->dateRangeType, new \DateTime($request->date));
        $lastDate = Utils::lastDate($request->dateRangeType, new \DateTime($request->date));

        if ($request->dateRangeType === 'year') {
            $pdfTitle = "Jahr: {$firstDate->format('Y')}";
            $documentTitle = $firstDate->format('Y');
        } else {
            $monthName = Settings::getMonthName($firstDate);
            $pdfTitle = "{$monthName} {$firstDate->format('Y')}";
            $documentTitle = $pdfTitle;
        }

        $this->pdf = new Pdf('P');
        $this->generateMealsPage($firstDate, $lastDate, $pdfTitle, false);

        if ($request->dateRangeType === 'year') {
            $lastDate = Utils::lastDate('month', $firstDate);
            for ($i = 0; $i < 12; $i++) {
                $monthName = Settings::getMonthName($firstDate);
                $this->generateMealsPage($firstDate, $lastDate, "{$monthName} {$firstDate->format('Y')}");
                $firstDate->modify('first day of next month');
                $lastDate->modify('last day of next month');
            }
        }

        return $this->pdf->export("Verpflegungen Hofmitarbeiter {$documentTitle}.pdf");
    }

    // Helpers
    private function timerecordsMonthRapport($workerId, $date)
    {
        $firstDayOfMonth = Utils::firstDate('month', new \DateTime($date));
        $lastDayOfMonth = Utils::lastDate('month', new \DateTime($date));

        $workers = User::workers()
            ->with([
                'timerecords' => function ($query) use ($firstDayOfMonth, $lastDayOfMonth) {
                    $query->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
                    ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
                    ->with('hours.worktype');
                },
                'transactions' => fn ($query) => PdfHelperFunctions::openTransactionsQuery($query),
            ])->when($workerId !== 'all', function ($query) use ($workerId) {
                $query->where('id', $workerId);
            })->withTrashed()
                ->orderby('lastname')
                ->get();

        if ($workerId === 'all') {
            $workers = $workers->filter(function ($worker) use ($firstDayOfMonth) {
                return $worker->totalHoursByMonth($firstDayOfMonth) > 0;
            });
        }

        $this->pdf = new Pdf();
        $monthName = Settings::getMonthName($firstDayOfMonth);
        if ($workerId == 'all') {
            $this->pdf->documentTitle("Hofmitarbeiter Monatsrapport: $monthName");

            // FrontPage
            $titles = ['Mitarbeiter', 'Arbeitszeit Total', 'Verpflegungen (Total)'];
            $lines = [];
            foreach ($workers as $worker) {
                $line = [
                    $worker->lastname.' '.$worker->firstname,
                    $worker->totalHoursByMonth($firstDayOfMonth),
                    array_sum($worker->getNumberOfMealsByMonth($firstDayOfMonth)),
                ];
                array_push($lines, $line);
            }
            $this->pdf->table($titles, $lines);
        }

        foreach ($workers as $worker) {
            if ($workerId == 'all') {
                $this->pdf->addNewPage('L');
            }
            $this->monthRapportForSingleWorker($worker, $firstDayOfMonth, $monthName, true);
        }

        if ($workerId != 'all') {
            $filename = "Monatrapport {$workers[0]->lastname} {$workers[0]->firstname} $monthName.pdf";
        } else {
            $filename = "Monatsrapport Hofmitarbeiter $monthName.pdf";
        }

        return $this->pdf->export($filename);
    }

    private function timerecordsYearRapport($workerId, $date)
    {
        $firstDayOfYear = Utils::firstDate('year', new \DateTime($date));
        $lastDayOfYear = Utils::lastDate('year', new \DateTime($date));

        $worker = User::with(['timerecords' => function ($query) use ($firstDayOfYear, $lastDayOfYear) {
            $query->where('date', '>=', $firstDayOfYear->format('Y-m-d'))
                ->where('date', '<=', $lastDayOfYear->format('Y-m-d'))
                ->with('hours.worktype');
        }])->find($workerId);
        $this->pdf = new Pdf();
        $this->pdf->documentTitle('Jahresrapport Hofmitarbeiter');
        $this->pdf->documentTitle($worker->fullName());
        $this->pdf->documentTitle("Totale Stunden: {$worker->totalHoursByYear($firstDayOfYear)}");

        $worktpyes = Worktype::all();
        foreach ($worktpyes as $worktype) {
            $this->pdf->documentTitle("{$worktype->name_de}: {$worker->totalHoursByYear($firstDayOfYear, $worktype->id)}h", $this->pdf->textSize);
        }

        $meals = $worker->getNumberOfMealsByYear($firstDayOfYear);
        $this->pdf->documentTitle("Frühstück: {$meals['breakfast']}, Zmittagessen: {$meals['lunch']}, Abendessen: {$meals['dinner']}", $this->pdf->textSize);
        $this->pdf->newLine();

        $lines = [];
        $firstDayOfMonth = clone $firstDayOfYear;
        $totalHoursByMonth = [];
        for ($i = 0; $i < 12; $i++) {
            $totalHours = $worker->totalHoursByMonth($firstDayOfMonth);
            array_push($totalHoursByMonth, $totalHours);
            if ($totalHours > 0) {
                $meals = $worker->getNumberOfMealsByMonth($firstDayOfMonth);
                $mealscount = $meals['breakfast'] + $meals['lunch'] + $meals['dinner'];
                array_push($lines, [Settings::getMonthName($firstDayOfMonth), $totalHours, $mealscount]);
            }
            $firstDayOfMonth->modify('first day of next month');
        }
        $titles = ['Monat', 'Stunden', 'Verpflegungen'];
        $this->pdf->table($titles, $lines, [3]);

        $firstDayOfMonth = clone $firstDayOfYear;
        for ($i = 0; $i < 12; $i++) {
            if ($totalHoursByMonth[$i] > 0) {
                $this->pdf->addNewPage();
                $this->monthRapportForSingleWorker($worker, $firstDayOfMonth, Settings::getMonthName($firstDayOfMonth));
            }
            $firstDayOfMonth->modify('first day of next month');
        }

        return $this->pdf->export("Jahresrapport {$worker->fullName()} {$firstDayOfYear->format('Y')}.pdf");
    }

    private function generateMealsPage($firstDate, $lastDate, $titleForTime, $addNewPage = true)
    {
        $totalMeals = Timerecord::getMealsBetweenDate($firstDate, $lastDate);
        if ($totalMeals > 0 || ! $addNewPage) {
            if ($addNewPage) {
                $this->pdf->addNewPage();
            }
            $this->pdf->documentTitle('Verpflegungen Hofmitarbeiter');
            $this->pdf->documentTitle($titleForTime);
            $this->pdf->documentTitle("Totale Verpflegungen: $totalMeals");
            foreach ($this->mealTypes as $key => $mealType) {
                $meals = Timerecord::getMealsBetweenDateByType($firstDate, $lastDate, $key);
                if ($meals > 0) {
                    $this->pdf->documentTitle("$mealType: $meals");
                }
            }
            $this->mealsTable($firstDate, $lastDate);
        }
    }

    private function mealsTable($firstDate, $lastDate)
    {
        $workers = User::withTrashed()->orderBy('lastname')->get();

        $columns = [];
        foreach ($workers as $worker) {
            $totalMealsByType = $worker->getNumberOfMeals($firstDate, $lastDate);
            $totalMeals = $totalMealsByType['breakfast'] + $totalMealsByType['lunch'] + $totalMealsByType['dinner'];
            if ($totalMeals > 0) {
                array_push($columns, [$worker->fullName(), $totalMeals, $totalMealsByType['breakfast'], $totalMealsByType['lunch'], $totalMealsByType['dinner']]);
            }
        }
        $this->pdf->newLine();
        $this->pdf->table(['Hofmitarbeiter', 'Verpflegungen', 'Frühstück', 'Mittagessen', 'Abendessen'], $columns, [2, 1, 1, 1, 1]);
    }

    private function monthRapportForSingleWorker($worker, $firstDayOfMonth, $monthName, $withTransactions = false)
    {
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $this->pdf->documentTitle("Mitarbeiter: $worker->name");
        $this->pdf->documentTitle("Monat: $monthName");
        $this->pdf->documentTitle("Totale Arbeitsstunden: {$worker->totalHoursByMonth($firstDayOfMonth)}h");

        $this->pdf->textToInsertOnPageBreak = "Mitarbeiter: $worker->name \nMonat: $monthName";

        $worktpyes = Worktype::all();
        foreach ($worktpyes as $worktype) {
            $this->pdf->documentTitle("{$worktype->name_de}: {$worker->totalHoursByMonth($firstDayOfMonth, $worktype->id)}h", $this->pdf->textSize);
        }
        $meals = $worker->getNumberOfMealsByMonth($firstDayOfMonth);
        $this->pdf->documentTitle("Frühstück: {$meals['breakfast']}, Zmittagessen: {$meals['lunch']}, Abendessen: {$meals['dinner']}", $this->pdf->textSize);
        $this->pdf->newLine();

        $lines = [];
        $comments = [];
        $currentDay = clone $firstDayOfMonth;
        $currentDay->modify('monday this week');
        // loop through each weak of month
        for ($i = 0; $i < 6; $i++) {
            $line = [];
            $totalHoursOfWeek = 0;
            // loop through each day of week
            for ($j = 0; $j < 7; $j++) {
                if ($currentDay < $firstDayOfMonth || $currentDay > $lastDayOfMonth) {
                    array_push($line, null);
                } else {
                    $timerecord = $worker->timerecords->where('date', $currentDay->format('Y-m-d'))->first();
                    if (! $timerecord || count($timerecord->hours) === 0) {
                        array_push($line, 0);
                    } else {
                        $cell = '';
                        $hoursPerWorktype = [];
                        foreach ($timerecord->hours as $hour) {
                            if ($hour->comment) {
                                array_push($comments, [
                                    'text' => $hour->comment,
                                    'date' => $timerecord->date,
                                ]);
                            }
                            if (isset($hoursPerWorktype[$hour->worktype->short_name])) {
                                $hoursPerWorktype[$hour->worktype->short_name] += $hour->hours();
                            } else {
                                $hoursPerWorktype[$hour->worktype->short_name] = $hour->hours();
                            }
                        }

                        foreach ($hoursPerWorktype as $worktype => $hours) {
                            if ($cell) {
                                $cell .= ', ';
                            }
                            $cell .= $hours;
                            if ($worktype !== 'P') {
                                $cell .= "({$worktype})";
                            }
                        }
                        $hours = $timerecord->totalHours();
                        $totalHoursOfWeek += $hours;
                        array_push($line, $cell);
                    }
                }
                $currentDay->modify('+1 day');
            }
            if ($totalHoursOfWeek > 0) {
                $lastDayOfWeek = clone $currentDay;
                $lastDayOfWeek->modify('-1 day');
                $firstDayOfWeek = clone $lastDayOfWeek;
                $firstDayOfWeek->modify('-6 days');
                array_unshift($line, "KW {$firstDayOfWeek->format('W')} ({$firstDayOfWeek->format('d.m.Y')} - {$lastDayOfWeek->format('d.m.Y')})");
                array_push($lines, $line);
            }
        }
        $titles = Settings::dayNames;
        array_unshift($titles, 'Zeitraum');
        // $this->generateTable($titles, $lines, 3);
        $this->pdf->table($titles, $lines, [3]);
        if (count($comments) > 0) {
            $this->pdf->documentTitle('Kommentare');
            $this->pdf->textToInsertOnPageBreak = "Mitarbeiter: {$worker->lastname} {$worker->firstname} \nMonat: $monthName";

            foreach ($comments as $comment) {
                $date = new \DateTime($comment['date']);

                $this->pdf->comment($date, $comment);
            }
        }

        if ($withTransactions) {
            PdfHelperFunctions::openTransactionsTable($this->pdf, $worker);
        }

        $this->pdf->signaturePlaceHolder();
    }
}
