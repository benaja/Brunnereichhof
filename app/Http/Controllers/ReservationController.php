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

        // BedRoomPivot::where('entry', '>=', $request->from)->where('entry', '<=', $re)
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
        //
    }

    public function destroy($id)
    {
        //
    }

    private function validateDate($bedRoomPivot, $employee, $from, $to)
    {
        $reservation = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('entry', '>=', $from)
            ->where('entry', '<=', $to)
            ->get();

        if (count($reservation) >= $bedRoomPivot->bed->places) {
            abort(400, 'Bed is already booked at this time');
        }

        $reservation = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('exit', '>=', $from)
            ->where('exit', '<=', $to)
            ->get();

        if (count($reservation) >= $bedRoomPivot->bed->places) {
            abort(400, 'Bed is already booked at this time');
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '>=', $from)
            ->where('entry', '<=', $to)
            ->first();

        if ($reservation) {
            abort(400, 'Employee is already in an other bed at this time');
        }

        $reservation = Reservation::where('employee_id', $employee->id)
            ->where('exit', '>=', $from)
            ->where('exit', '<=', $to)
            ->first();

        if ($reservation) {
            abort(400, 'Employee is already in an other bed at this time');
        }
        // $reservation = Reservation::where('employee_id', $employee->id)
    }
}
