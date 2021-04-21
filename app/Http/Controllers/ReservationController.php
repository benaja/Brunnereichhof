<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Pivots\BedRoomPivot;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $startDate = new \DateTime($request->start);
        $endDate = new \DateTime($request->end);

        return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])
            ->where('entry', '<=', $endDate->format('Y-m-d'))
            ->where('exit', '>=', $startDate->format('Y-m-d'))
            ->get();
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $this->validate($request, [
            'entry' => 'required|date',
            'exit' => 'required|date|after_or_equal:entry',
            'room' => 'required',
            'bed' => 'required',
            'employee' => 'required',
            'force' => 'nullable|boolean',
        ]);

        $bedRoomPivot = BedRoomPivot::find($request->bed);
        $employee = Employee::find($request->employee);

        $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false);

        return DB::transaction(function () use ($request, $employee, $validationResult) {
            if ($validationResult === 'Employee is already in an other bed at this time' && $request->force) {
                $reservations = Reservation::where('employee_id', $employee->id);
                $this->clearOverlappingReservations($request, $reservations);
            }
            if ($validationResult === true || $request->force) {
                $reservation = Reservation::create([
                    'entry' => $request->entry,
                    'exit' => $request->exit,
                    'bed_room_id' => $request->bed,
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
        });
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $this->validate($request, [
            'entry' => 'required|date',
            'exit' => 'required|date|after_or_equal:entry',
            'room' => 'required',
            'bed' => 'required',
            'employee' => 'required',
        ]);

        $bedRoomPivot = BedRoomPivot::withTrashed()->find($request->bed);
        $employee = Employee::withTrashed()->find($request->employee);

        $reservation = Reservation::find($id);
        $validationResult = $this->validateDate($bedRoomPivot, $employee, $request->entry, $request->exit, false, $reservation);

        return DB::transaction(function () use ($request, $validationResult, $employee, $reservation, $bedRoomPivot) {
            if ($validationResult === 'Employee is already in an other bed at this time' && $request->force) {
                $reservations = Reservation::where('employee_id', $employee->id);
                $this->clearOverlappingReservations($request, $reservations, $reservation);
            }
            if ($validationResult === 'Bed is already booked at this time' && $request->force) {
                $reservations = Reservation::where('bed_room_id', $bedRoomPivot->id);
                $this->clearOverlappingReservations($request, $reservations, $reservation);
            }

            if ($validationResult === true || $request->force) {
                $reservation->update([
                    'entry' => $request->entry,
                    'exit' => $request->exit,
                    'bed_room_id' => $request->bed,
                ]);

                $reservation->employee()->associate($employee);
                $reservation->save();
                if ($request->force) {
                    return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->get();
                }

                return Reservation::with(['employee', 'bedRoomPivot', 'bedRoomPivot.bed', 'bedRoomPivot.room'])->find($reservation->id);
            } else {
                Reservation::withTrashed()->find($reservation->id)->restore();

                return response($validationResult, 400);
            }
        });
    }

    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        Reservation::find($id)->delete();
    }

    // GET /stats/quartering
    public function quartering(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $firstDate = Utils::firstDate($request->type, new \DateTime($request->date));
        $lastDate = Utils::lastDate($request->type, new \DateTime($request->date));

        return Reservation::where('entry', '>=', $firstDate->format('Y-m-d'))
            ->where('entry', '<=', $lastDate->format('Y-m-d'))
            ->count();
    }

    // GET /stats/room-changes
    public function roomChanges(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $firstDate = Utils::firstDate($request->type, new \DateTime($request->date));
        $lastDate = Utils::lastDate($request->type, new \DateTime($request->date));

        $reservations = Reservation::where('entry', '<=', $lastDate->format('Y-m-d'))
            ->where('exit', '>=', $firstDate->format('Y-m-d'))
            ->get();
        $roomChanges = 0;
        foreach ($reservations as $reservation) {
            $reservationExitDate = clone $reservation->exit;
            $reservationExitDate->modify('+1 day');
            $hasEmployeeChangedBed = Reservation::where('employee_id', $reservation->employee_id)
                ->where('entry', $reservationExitDate->format('Y-m-d'))
                ->count();

            if ($hasEmployeeChangedBed && $reservation->exit <= $lastDate) {
                $roomChanges++;
            }
        }

        return $roomChanges;
    }

    // --helpers--
    private function validateDate($bedRoomPivot, $employee, $entry, $exit, $abort = true, $reservation = null)
    {
        $reservations = Reservation::where('bed_room_id', $bedRoomPivot->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $entry);
        if ($reservation) {
            $reservations->where('id', '!=', $reservation->id);
        }
        $reservations = $reservations->get();
        if (count($reservations) >= $bedRoomPivot->bed->places) {
            return $this->rejectValidation('Bed is already booked at this time', $abort);
        }

        $employeeReservation = Reservation::where('employee_id', $employee->id)
            ->where('entry', '<=', $exit)
            ->where('exit', '>=', $entry);
        if ($reservation) {
            $employeeReservation->where('id', '!=', $reservation->id);
        }
        $employeeReservation = $employeeReservation->first();

        if ($employeeReservation) {
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

    private function clearOverlappingReservations($request, $originalReservationsQuery, $editableReservation = null)
    {
        if ($editableReservation) {
            $originalReservationsQuery->where('id', '!=', $editableReservation->id);
        }

        $reservationsQuery = clone $originalReservationsQuery;
        $reservation = $reservationsQuery
            ->where('entry', '<', $request->entry)
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

        $reservationsQuery = clone $originalReservationsQuery;
        $reservation = $reservationsQuery
            ->where('entry', '<=', $request->exit)
            ->where('exit', '>', $request->exit)
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

        $reservationsQuery = clone $originalReservationsQuery;
        $reservations = $reservationsQuery
            ->where('entry', '>=', $request->entry)
            ->where('exit', '<=', $request->exit)
            ->get();

        foreach ($reservations as $reservation) {
            Reservation::destroy($reservation->id);
        }
    }
}
