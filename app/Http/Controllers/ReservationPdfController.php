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

    public function pdfByEmployee(Request $request, $id)
    {
        // $this->validateToken($request->token);

        $this->getPdfDefault();

        $employee = Employee::find($id);
        $reservation = $employee->reservations->where('entry', '<=', $request->date)->where('exit', '>=', $request->date)->first();
        $bed = $reservation->bedRoomPivot->bed;

        $otherReservations = $reservation->bedRoomPivot->reservations->where('entry', '<=', $request->date)->where('exit', '>=', $request->date);

        $inventars = [];
        $invs = Bed::find($bed->id)->inventars;
        foreach($invs as $inventar) {
            if (count($otherReservations) == 1) {
                array_push($inventars, [
                    'name' => $inventar->name,
                    'amount' => $inventar->pivot->amount
                ]);
            } else {
                array_push($inventars, [
                    'name' => $inventar->name,
                    'amount' => $inventar->pivot->amount_2
                ]);
            }
        }

        

        dd($inventars);
        return $reservation;
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

    private function addDocumentTitle($text)
    {
        Fpdf::SetDrawColor(255);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 20);
        Fpdf::Cell(0, 10, utf8_decode($text), 0, 2);
    }

    private function validateToken($token)
    {
        if ($token != Cache::pull('pdfToken')) {
            abort(401, 'This action is unauthorized.');
        }
    }
}
