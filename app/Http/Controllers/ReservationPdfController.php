<?php

namespace App\Http\Controllers;

use App\Bed;
use App\Employee;
use App\Reservation;
use App\Helpers\Pdf;
use App\Helpers\Utils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationPdfController extends Controller
{
    private $pdf;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function pdfByEmployee(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();
        $date = (new \DateTime($request->date))->format('Y-m-d');

        $employees = Employee::with(['reservations' => function ($query) use ($date) {
            $query->where('entry', '<=', $date)
                ->where('exit', '>=', $date)
                ->with('bedRoomPivot.bed.inventars')
                ->with('bedRoomPivot.room')
                ->with('employee');
        }])->withTrashed()
            ->when($request->employeeId, function($query, $employeeId) {
                $query->where('id', $employeeId);
            })
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $counter = 0;
        foreach ($employees as $employee) {
            if (count($employee->reservations) > 0) {
                $reservation = $employee->reservations[0];
                if ($counter != 0) {
                    $this->pdf->addNewPage();
                }
                $bed = $reservation->bedRoomPivot->bed;

                $lines = [];
                $inventars = $bed->inventars;
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

    public function sleepOversPerEmployee(Request $request) {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $firstDate = Utils::firstDate($request->dateRangeType, $request->date);
        $lastDate = Utils::lastDate($request->dateRangeType, $request->date);

        $reservations = Reservation::where('entry', '<=', $lastDate->format('Y-m-d'))
            ->where('exit', '>=', $firstDate->format('Y-m-d'))
            ->get();

        $sleepOversByEmployee = [];
        foreach($reservations as $reservation) {
            $sleepOvers = $reservation->sleepOver($firstDate, $lastDate);
            if (isset($sleepOversByEmployee[$reservation->employee_id])) {
                $sleepOversByEmployee[$reservation->employee_id] += $sleepOvers;
            } else {
                $sleepOversByEmployee[$reservation->employee_id] = $sleepOvers;
            }
        }

        $columns = [];
        $employees = Employee::withTrashed()
            ->whereIn('id', array_keys($sleepOversByEmployee))
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
            
        foreach($employees as $employee) {
            array_push($columns, [$employee->name(), $sleepOversByEmployee[$employee->id]]);
        }
        
        $documentTitle = "Übernachtungen pro Mitarbeiter \n";
        if ($request->dateRangeType === 'year') {
            $documentTitle .= "Jahr: {$firstDate->format('Y')}";
        } else {
            $documentTitle .= "Monat: {$firstDate->format('m.Y')}";
        }

        $pdf = new Pdf();
        $pdf->documentTitle($documentTitle);
        $pdf->table(['Mitarbeiter', 'Übernachtungen'], $columns);
        if ($request->dateRangeType === 'year') {
            return $pdf->export("Übernachtungen pro Mitarbeiter {$firstDate->format('Y')}.pdf");
        } else {
            return $pdf->export("Übernachtungen pro Mitarbeiter {$firstDate->format('m-Y')}.pdf");
        }
    }
}
