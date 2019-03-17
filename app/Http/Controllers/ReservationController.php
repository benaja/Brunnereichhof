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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        // if (isset(request('all'))) {
        //     $reservation::with('employee')->get();
        // }

        return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->get();
    }

    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $this->validate($request, [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'room' => 'required',
            'bed' => 'required',
            'employee' => 'required',
            'force' => 'nullable|boolean'
        ]);

        $bedRoomPivot = BedRoomPivot::find($request->bed);
        $employee = Employee::find($request->employee);

        $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->from, $request->to, false);

        if ($validationResult == 'Employee is already in an other bed at this time' && $request->force) {
            $this->clearOverlappingReservations($request, $employee);
        }
        if ($validationResult === true || $request->force) {
            $reservation = Reservation::create([
                'entry' => $request->from,
                'exit' => $request->to,
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

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

        if ($validationResult == 'Employee is already in an other bed at this time' && $request->force) {
            $this->clearOverlappingReservations($request, $employee);
        }
        if ($validationResult === true || $request->force) {

            $reservation = Reservation::create([
                'entry' => $request->entry,
                'exit' => $request->exit,
                'bed_room_id' => $request->bed
            ]);

            $reservation->employee()->associate($employee);
            $reservation->save();
            return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
        } else {
            $reservation = $reservation->save();
            // return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
            return response($validationResult, 400);
        }
    }

    public function destroy($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        Reservation::find($id)->delete();
    }

    // --helpers--
    private function validateDate($bedRoomPivot, $employee, $from, $to, $abort = true)
    {
        $reservation = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('entry', '>=', $from)
            ->where('entry', '<=', $to)
            ->get();

        if (count($reservation) >= $bedRoomPivot->bed->places) {
            return $this->rejectValidation('Bed is already booked at this time', $abort);
        }

        $reservation = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('exit', '>=', $from)
            ->where('exit', '<=', $to)
            ->get();

        if (count($reservation) >= $bedRoomPivot->bed->places) {
            return $this->rejectValidation('Bed is already booked at this time', $abort);
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $to)
            ->where('exit', '>=', $from)
            ->first();

        if ($reservation) {
            return $this->rejectValidation('Employee is already in an other bed at this time', $abort);
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $to)
            ->where('exit', '>=', $to)
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

    private function clearOverlappingReservations($request, $employee)
    {
        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $request->from)
            ->where('exit', '>=', $request->from)
            ->first();
        if ($reservation) {
            $newExitDate = (new \DateTime($request->from))->modify('-1 day');
            if ($newExitDate < $reservation->entry) {
                Reservation::destroy($reservation->id);
            } else {
                $reservation->exit = $newExitDate;
                $reservation->save();
            }
            $reservation = null;
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $request->to)
            ->where('exit', '>=', $request->to)
            ->first();
        if ($reservation) {
            $newEntryDate = (new \DateTime($request->to))->modify('+1 day');
            if ($newEntryDate < $request->exit) {
                Reservation::destroy($reservation->id);
            } else {
                $reservation->entry = $newEntryDate;
                $reservation->save();
            }
            $reservation = null;
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '>=', $request->from)
            ->where('exit', '<=', $request->to)
            ->first();
        if ($reservation) {
            Reservation::destroy($reservation->id);
        }
    }
}
