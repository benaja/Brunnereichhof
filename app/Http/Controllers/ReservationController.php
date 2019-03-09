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

        $reservation = Reservation::create([
            'entry' => $request->from,
            'exit' => $request->to,
            'bed_room_id' => $request->bed
        ]);

        $employee = Employee::find($request->employee);
        // $bedRoom = BedRoomPivot::find($request->bed);

        $reservation->employee()->associate($employee);
        $reservation->save();

        return $reservation;
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
}
