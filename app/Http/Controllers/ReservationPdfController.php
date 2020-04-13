<?php

namespace App\Http\Controllers;

use App\Bed;
use App\Employee;
use App\Reservation;
use App\Helpers\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationPdfController extends Controller
{
    private $pdf;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function pdfByEmployee(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();

        $employees = Employee::withTrashed()->find($id);
        if (!$employees) {
            $employees = Employee::with('user')
                ->withTrashed()
                ->get()
                ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } else {
            $employees = [$employees];
        }
        $usedEmployees = [];
        $counter = 0;
        foreach ($employees as $employee) {
            $date = (new \DateTime($request->date))->format('Y-m-d');
            $reservation = Reservation::where('employee_id', '=', $employee->id)
                ->where('entry', '<=', $date)
                ->where('exit', '>=', $date)->first();
            if (!in_array($employee->id, $usedEmployees) && $reservation) {
                if ($counter != 0) {
                    $this->pdf->addNewPage();
                }
                $bed = $reservation->bedRoomPivot->bed;

                $lines = [];
                $inventars = Bed::withTrashed()->find($bed->id)->inventars;
                foreach ($inventars as $inventar) {
                    $amount = $inventar->pivot->amount;
                    array_push($lines, [$amount, $inventar->name, 'CHF ' . number_format($inventar->price, 2), '', '', '']);
                }

                $this->pdf->documentTitle($reservation->employee->lastname . ' ' . $reservation->employee->firstname);

                $this->pdf->documentTitle('Raum: ' . $reservation->bedRoomPivot->room->name);
                $this->pdf->documentTitle('Standort: ' . $reservation->bedRoomPivot->room->location);
                $this->pdf->documentTitle('Bett: ' . $bed->name);
                $this->pdf->documentTitle('');
                $this->pdf->documentTitle('Bettwäsche und Bettinhalt', $this->pdf->titleSize, 'B');

                $tableHeaders = ['Anzahl', 'Was', 'Preis pro Stk.', 'Abgegeben am', 'Visiert', 'Eingezogen am'];
                $this->pdf->table($tableHeaders, $lines, [0.5, 2.5, 0.7]);
                $counter++;
            }
        }

        $employeename = count($employees) == 1 ? " $employee->lastname $employee->firstname" : "";
        return $this->pdf->export('Raumbelegung ' . (new \DateTime($request->date))->format('d-m-Y') . $employeename . '.pdf');
    }
}
