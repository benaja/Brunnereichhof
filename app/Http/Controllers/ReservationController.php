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
            'employee' => 'required'
        ]);

        $bedRoomPivot = BedRoomPivot::find($request->bed);
        $employee = Employee::find($request->employee);

        $this->validateDate($bedRoomPivot, $employee, $request->from, $request->to);

        $reservation = Reservation::create([
            'entry' => $request->from,
            'exit' => $request->to,
            'bed_room_id' => $request->bed
        ]);

        // $bedRoom = BedRoomPivot::find($request->bed);

        $reservation->employee()->associate($employee);
        $reservation->save();

        return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
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

        if ($this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false)) {

            $reservation = Reservation::create([
                'entry' => $request->entry,
                'exit' => $request->exit,
                'bed_room_id' => $request->bed
            ]);

            // $bedRoom = BedRoomPivot::find($request->bed);

            $reservation->employee()->associate($employee);
            $reservation->save();
            return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
        } else {
            $reservation->save();
            return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
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
            if ($abort) {
                abort(400, 'Bed is already booked at this time');
            } else {
                return 'Bed is already booked at this time';
            }
        }

        $reservation = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('exit', '>=', $from)
            ->where('exit', '<=', $to)
            ->get();

        if (count($reservation) >= $bedRoomPivot->bed->places) {
            if ($abort) {
                abort(400, 'Bed is already booked at this time');
            } else {
                return 'Bed is already booked at this time';
            }
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '>=', $from)
            ->where('entry', '<=', $to)
            ->first();

        if ($reservation) {
            if ($abort) {
                abort(400, 'Employee is already in an other bed at this time');
            } else {
                return 'Employee is already in an other bed at this time';
            }
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('exit', '>=', $from)
            ->where('exit', '<=', $to)
            ->first();

        if ($reservation) {
            if ($abort) {
                abort(400, 'Employee is already in an other bed at this time');
            } else {
                return 'Employee is already in an other bed at this time';
            }
        }
        return true;
        // $reservation = Reservation::where('employee_id', $employee->id)
    }
}
