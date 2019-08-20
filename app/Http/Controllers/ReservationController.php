<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reservation;
use App\Employee;
use App\Pivots\BedRoomPivot;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorize(['admin', 'superadmin'], ['roomdispositioner_read']);

        return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->get();
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['admin', 'superadmin'], ['roomdispositioner_write']);

        $this->validate($request, [
            'entry' => 'required|date',
            'exit' => 'required|date|after_or_equal:entry',
            'room' => 'required',
            'bed' => 'required',
            'employee' => 'required',
            'force' => 'nullable|boolean'
        ]);

        $bedRoomPivot = BedRoomPivot::find($request->bed);
        $employee = Employee::find($request->employee);

        $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);

        while ($validationResult === 'Employee is already in an other bed at this time' && $request->force) {
            $reservations = Reservation::where('employee_id', $employee->id)->get();
            $this->clearOverlappingReservations($request, $reservations);
            $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);
        }
        if ($validationResult === true || $request->force) {
            $reservation = Reservation::create([
                'entry' => $request->entry,
                'exit' => $request->exit,
                'bed_room_id' => $request->bed
            ]);

            $reservation->employee()->associate($employee);
            $reservation->save();

            if ($request->force) {
                return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->get();
            }
            return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
        } else {
            return response($validationResult, 400);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['admin', 'superadmin'], ['roomdispositioner_write']);

        $this->validate($request, [
            'entry' => 'required|date',
            'exit' => 'required|date|after_or_equal:entry',
            'room' => 'required',
            'bed' => 'required',
            'employee' => 'required'
        ]);

        // BedRoomPivot::where('entry', '>=', $request->from)->where('entry', '<=', $re)
        $bedRoomPivot = BedRoomPivot::find($request->bed);
        $employee = Employee::find($request->employee);

        $reservation = Reservation::find($id);
        $reservation->delete();

        $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);

        while ($validationResult === 'Employee is already in an other bed at this time' && $request->force) {
            $reservations = Reservation::where('employee_id', $employee->id)->get();
            $this->clearOverlappingReservations($request, $reservations);
            $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);
        }
        while ($validationResult === 'Bed is already booked at this time' && $request->force) {
            $reservations = Reservation::where('bed_room_id', $bedRoomPivot->id)->get();
            $this->clearOverlappingReservations($request, $reservations);
            $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);
        }

        if ($validationResult === true || $request->force) {

            $reservation = Reservation::create([
                'entry' => $request->entry,
                'exit' => $request->exit,
                'bed_room_id' => $request->bed
            ]);

            $reservation->employee()->associate($employee);
            $reservation->save();
            if ($request->force) {
                return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->get();
            }
            return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
        } else {
            $reservation = $reservation->save();
            // return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
            return response($validationResult, 400);
        }
    }

    public function destroy($id)
    {
        auth()->user()->authorize(['admin', 'superadmin'], ['roomdispositioner_write']);

        Reservation::find($id)->delete();
    }

    // --helpers--
    private function validateDate($bedRoomPivot, $employee, $entry, $exit, $abort = true)
    {
        $allReservations = [];
        $reservations = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $entry)
            ->get();

        foreach ($reservations as $reservation) {
            if (!in_array($reservation, $allReservations)) {
                array_push($allReservations, $reservation);
            }
        }

        $reservations = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $exit)
            ->get();

        foreach ($reservations as $reservation) {
            if (!in_array($reservation, $allReservations)) {
                array_push($allReservations, $reservation);
            }
        }

        if (count($allReservations) >= $bedRoomPivot->bed->places) {
            $bedsUsed = 1;
            for ($i = 0; $i < count($allReservations); $i++) {
                for ($j = 0; $j < count($allReservations); $j++) {
                    if (
                        $allReservations[$i]->exit >= $allReservations[$j]->entry
                        && $allReservations[$i]->entry <= $allReservations[$j]->entry
                        && $i != $j
                    ) {
                        $bedsUsed++;
                    }
                }
            }
            if ($bedsUsed >= $bedRoomPivot->bed->places) {
                return $this->rejectValidation('Bed is already booked at this time', $abort);
            }
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $entry)
            ->first();

        if ($reservation) {
            return $this->rejectValidation('Employee is already in an other bed at this time', $abort);
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $exit)
            ->first();

        if ($reservation) {
            return $this->rejectValidation('Employee is already in an other bed at this time', $abort);
        }
        return true;
    }

    private function rejectValidation($errorMessage, $abort)
    {
        if ($abort) {
            abort(400, $errorMessage);
        } else {
            return $errorMessage;
        }
    }

    private function clearOverlappingReservations($request, $reservations)
    {
        $reservation = $reservations->where('entry', '<=', $request->entry)
            ->where('exit', '>=', $request->entry)
            ->first();
        if ($reservation) {
            $newExitDate = (new \DateTime($request->entry))->modify('-1 day');
            if ($newExitDate < $reservation->entry) {
                Reservation::destroy($reservation->id);
            } else {
                $reservation->exit = $newExitDate;
                $reservation->save();
            }
            $reservation = null;
        }

        $reservation = $reservations->where('entry', '<=', $request->exit)
            ->where('exit', '>=', $request->exit)
            ->first();
        if ($reservation) {
            $newEntryDate = (new \DateTime($request->exit))->modify('+1 day');
            if ($newEntryDate < $request->exit) {
                Reservation::destroy($reservation->id);
            } else {
                $reservation->entry = $newEntryDate;
                $reservation->save();
            }
            $reservation = null;
        }

        $reservation = $reservations->where('entry', '>=', $request->entry)
            ->where('exit', '<=', $request->exit)
            ->first();
        if ($reservation) {
            Reservation::destroy($reservation->id);
        }
    }
}
