<?php

namespace App\Http\Controllers\Evaluation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Pdf;
use App\User;
use App\Worktype;
use App\Helpers\Settings;

class WorkerPdfController extends Controller
{
    private $pdf;

    public function workerMonthRapport(Request $request, $workerId, $month)
    {
        Pdf::validateToken($request->token);

        $firstDayOfMonth = new \DateTime($month);
        $firstDayOfMonth->modify('first day of this month');
        $workers = [];

        if ($workerId != 'all') {
            array_push($workers, User::find($workerId));
        } else {
            foreach (User::workers()->withTrashed()->orderby('lastname')->get() as $worker) {
                if ($worker->totalHoursByMonth($firstDayOfMonth) > 0) {
                    array_push($workers, $worker);
                }
            }
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
                    $worker->lastname . " " . $worker->firstname,
                    $worker->totalHoursByMonth($firstDayOfMonth),
                    array_sum($worker->getNumberOfMealsByMonth($firstDayOfMonth))
                ];
                array_push($lines, $line);
            }
            $this->pdf->table($titles, $lines);
        }

        foreach ($workers as $worker) {
            if ($workerId == 'all') {
                $this->pdf->addPage('F');
            }
            $this->monthRapportForSingleWorker($worker, $firstDayOfMonth, $monthName);
        }

        if ($workerId != 'all') {
            $filename = "Monatrapport {$workers[0]->lastname} {$workers[0]->firstname} $monthName.pdf";
        } else {
            $filename = "Monatsrapport Hofmitarbeiter $monthName.pdf";
        }
        $this->pdf->export($filename);
    }

    public function workerYearRapport(Request $request, $workerId, $year)
    {
        Pdf::validateToken($request->token);

        $firstDayOfYear = (new \DateTime($year))->modify('first day of january this year');

        $worker = User::find($workerId);
        $this->pdf = new Pdf();
        $this->pdf->documentTitle('Jahresrapport Hofmitarbeiter');
        $this->pdf->documentTitle($worker->fullName());
        $this->pdf->documentTitle("Totale Stunden: {$worker->totalHoursByYear($firstDayOfYear)}");

        $worktpyes = Worktype::all();
        foreach ($worktpyes as $worktype) {
            $this->pdf->documentTitle("{$worktype->name_de}: {$worker->totalHoursByYear($firstDayOfYear,$worktype->id)}h", $this->pdf->textSize);
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
                $this->pdf->addPage();
                $this->monthRapportForSingleWorker($worker, $firstDayOfMonth, Settings::getMonthName($firstDayOfMonth));
            }
            $firstDayOfMonth->modify('first day of next month');
        }
        $this->pdf->export("Jahresrapport {$worker->fullName()} {$firstDayOfYear->format('Y')}");
    }

    private function monthRapportForSingleWorker($worker, $firstDayOfMonth, $monthName)
    {
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');

        $this->pdf->documentTitle("Mitarbeiter: $worker->lastname $worker->firstname");
        $this->pdf->documentTitle("Monat: $monthName");
        $this->pdf->documentTitle("Totale Arbeitsstunden: {$worker->totalHoursByMonth($firstDayOfMonth)}h");

        $worktpyes = Worktype::all();
        foreach ($worktpyes as $worktype) {
            $this->pdf->documentTitle("{$worktype->name_de}: {$worker->totalHoursByMonth($firstDayOfMonth,$worktype->id)}h", $this->pdf->textSize);
        }
        $meals = $worker->getNumberOfMealsByMonth($firstDayOfMonth);
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
                        $worktypeShortName = "";
                        if ($timerecord->worktype() && $timerecord->worktype()->short_name) {
                            $worktypeShortName = "({$timerecord->worktype()->short_name})";
                        }
                        array_push($line, "{$hours} {$worktypeShortName}");
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
        $titles = Settings::dayNames;
        array_unshift($titles, "Zeitraum");
        // $this->generateTable($titles, $lines, 3);
        $this->pdf->table($titles, $lines, [3]);
        if (count($comments) > 0) {
            $this->pdf->newLine();
            $this->pdf->documentTitle('Kommentare');
            $this->pdf->textToInsertOnPageBreak = "Mitarbeiter: {$worker->lastname} {$worker->firstname} \nMonat: $monthName";

            foreach ($comments as $comment) {
                $date = new \DateTime($comment['date']);

                $this->pdf->comment($date, $comment);
            }
        }
        $this->pdf->signaturePlaceHolder();
    }
}
