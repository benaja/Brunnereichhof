<?php

namespace App\Http\Controllers;

use Fpdf;
use App\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Bed;

class ReservationPdfController extends Controller
{
    private $documentWidth = 270;
    private $textSize = 11;
    private $titleSize = 15;

    public function pdfByEmployee(Request $request, $id)
    {
        $this->validateToken($request->token);

        $this->getPdfDefault();

        $employees = Employee::find($id);
        if (!$employees) {
            $employees = Employee::all()->sortBy('lastname');
        } else {
            $employees = [$employees];
        }
        $usedEmployees = [];
        $counter = 0;
        foreach($employees as $employee) {
            $reservation = $employee->reservations->where('entry', '<=', (new \DateTime($request->date))->modify('+1 day')->format('Y-m-d'))->where('exit', '>=', $request->date)->first();
            if (!in_array($employee->id, $usedEmployees) && $reservation) {
                if ($counter != 0) {
                    Fpdf::AddPage('L');
                }
                $bed = $reservation->bedRoomPivot->bed;

                $otherReservations = $reservation->bedRoomPivot->reservations->where('entry', '<=', (new \DateTime($request->date))->modify('+1 day')->format('Y-m-d'))->where('exit', '>=', $request->date);

                $lines = [];
                $inventars = Bed::find($bed->id)->inventars;
                foreach($inventars as $inventar) {
                    $amount = $inventar->pivot->amount;
                    if (count($otherReservations) == 2) {
                        $amount = $inventar->pivot->amount_2;
                    }
                    array_push($lines, [ $amount, $inventar->name, 'CHF ' . number_format($inventar->price, 2), '', '', '']);
                }

                $names = [];
                foreach($otherReservations as $reservation) {
                    array_push($names, $reservation->employee->lastname . ' ' . $reservation->employee->firstname);
                    array_push($usedEmployees, $reservation->employee->id);
                }
                $this->addDocumentTitle(implode (", ", $names));

                $this->addDocumentTitle('Raum: ' . $reservation->bedRoomPivot->room->name);
                $this->addDocumentTitle('Bett: ' . $bed->name);
                $this->addDocumentTitle('');
                $this->addDocumentTitle('Bettwäsche und Bettinhalt', $this->titleSize, 'B');

                $tableHeaders = ['Anzahl', 'Was', 'Preis pro Stk.', 'Abgegeben am', 'Visiert', 'Eingezogen am'];
                $this->generateTable($tableHeaders, $lines, 0.5, 2.5, 0.7);
                $counter++;
            }
        }

        $employeename = count($employees) == 1 ? " $employee->lastname $employee->firstname" : "";
        $date = new \DateTime($request->date);

        $this->exportPage('Raumbelegung ' . (new \DateTime($request->date))->format('d-m-Y') . $employeename . '.pdf');
    }

    // --helpers--
    private function getPdfDefault($landscape = "L")
    {
        Fpdf::AddPage($landscape);
        Fpdf::SetFillColor(38, 166, 154);
        Fpdf::SetDrawColor(255);
        Fpdf::SetLineWidth(.3);
        Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
        Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
        // Fpdf::AddFont('Raleway','I', 'Raleway-Italic.php');

        Fpdf::SetFont('Raleway', '', 20);
    }

    private function addDocumentTitle($text, $textSize = 0, $fontWeight = '')
    {
        if ($textSize == 0) {
            $textSize = $this->titleSize;
        }
        Fpdf::SetDrawColor(255);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', $fontWeight, $textSize);
        Fpdf::Cell(0, $textSize / 1.8, utf8_decode($text), 0, 2);
    }

    private function generateTable($titles, $lines, $firstCellWidth = 1, $secondCellWidth = 1, $thirdCellWidth = 1) {
        Fpdf::SetDrawColor(173, 173, 173);
        Fpdf::SetLineWidth(.0001);
        $this->addTableHeader($titles, $firstCellWidth, $secondCellWidth, $thirdCellWidth);

        Fpdf::SetFont('Raleway', '', $this->textSize);
        Fpdf::SetTextColor(0);

        foreach ($lines as $line) {
            $this->addLine($line, $firstCellWidth, $secondCellWidth, $thirdCellWidth);
        }
    }

    private function addTableHeader($titles, $firstCellWidth = 1, $secondCellWidth = 1, $thirdCellWidth = 1)
    {
        Fpdf::SetFont('Raleway', 'B', $this->textSize);

        for ($i = 0; $i < count($titles); $i++) {
            $cellWidth = $this->documentWidth / (count($titles) + $firstCellWidth + $secondCellWidth + $thirdCellWidth - 3);
            if ($i == 0) {
                $cellWidth = $cellWidth * $firstCellWidth;
            } else if ($i == 1) {
                $cellWidth = $cellWidth * $secondCellWidth;
            } else if ($i == 2) {
                $cellWidth = $cellWidth * $thirdCellWidth;
            }
            Fpdf::Cell($cellWidth, 8, utf8_decode($titles[$i]), 1, 0, 'L');
        }
        Fpdf::Ln();
    }

    private function addLine($cells, $firstCellWidth = 1, $secondCellWidth = 1, $thirdCellWidth = 1)
    {
        $counter = 0;
        foreach ($cells as $cell) {
            $cellWidth = $this->documentWidth / (count($cells) + $firstCellWidth + $secondCellWidth + $thirdCellWidth - 3);
            if ($counter == 0) {
                $cellWidth = $cellWidth * $firstCellWidth;
            } else if ($counter == 1) {
                $cellWidth = $cellWidth * $secondCellWidth;
            } else if ($counter == 2) {
                $cellWidth = $cellWidth * $thirdCellWidth;
            }
            Fpdf::Cell($cellWidth, 8, utf8_decode($cell), 1, 0, 'L');
            $counter++;
        }
        Fpdf::Ln();
    }

    private function exportPage($filename)
    {
        Fpdf::Output('D', $filename);
    }

    private function validateToken($token)
    {
        if ($token != Cache::pull('pdfToken')) {
            abort(401, 'This action is unauthorized.');
        }
    }
}
