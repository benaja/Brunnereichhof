<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Room;
use App\Bed;
use Illuminate\Support\Facades\DB;
use App\Pivots\BedRoomPivot;
use App\Reservation;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Room::with('beds')->get();
    }

    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $this->validate($request, [
            'name' => 'required|max:100',
            'location' => 'required|max:100',
        ]);

        $room = Room::create([
            'name' => $request->name,
            'location' => $request->location,
            'comment' => $request->comment
        ]);

        $bedIds = array_map(function ($bed) {
            return $bed['id'];
        }, $request->beds);

        foreach ($bedIds as $bedId) {
            $bed = Bed::find($bedId);
            $room->beds()->attach($bed);
        }

        return $room;
    }

    public function show($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Room::with('beds.inventars')->find($id);
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $room = Room::find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $room->$updatetKey = $updatedValue;
        $room->save();

        return $room;
    }

    public function addBed($roomId, $bedId)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $room = Room::find($roomId);
        // $test = $room->beds()->attach($bedId);

        $pivot = BedRoomPivot::create();
        $pivot->room()->associate($roomId);
        $pivot->bed()->associate($bedId);
        $pivot->save();

        return $pivot;
    }

    public function removeBed($roomId, $pivotId)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        DB::table('bed_room')->where('id', $pivotId)->delete();
    }

    public function beds(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        if (isset($request->entry)) {
            $beds = Room::find($id)->beds;
            $availableBeds = [];
            $usedReservatins = [];
            foreach ($beds as $bed) {
                $notAllowedReservations1 = Reservation::join('bed_room', function ($join) use ($bed, $id) {
                    $join->on('reservation.bed_room_id', '=', 'bed_room.id')
                        ->where('bed_room.bed_id', $bed->id)
                        ->where('bed_room.room_id', $id);
                })->where('entry', '<=', $request->entry)
                    ->where('exit', '>=', $request->entry)
                    ->get();

                $notAllowedReservations2 = Reservation::join('bed_room', function ($join) use ($bed, $id) {
                    $join->on('reservation.bed_room_id', '=', 'bed_room.id')
                        ->where('bed_room.bed_id', $bed->id)
                        ->where('bed_room.room_id', $id);
                })->where('entry', '<=', $request->exit)
                    ->where('exit', '>=', $request->exit)
                    ->get();

                $notAllowedReservations3 = Reservation::join('bed_room', function ($join) use ($bed, $id) {
                    $join->on('reservation.bed_room_id', '=', 'bed_room.id')
                        ->where('bed_room.bed_id', $bed->id)
                        ->where('bed_room.room_id', $id);
                })->where('entry', '>=', $request->entry)
                    ->where('exit', '<=', $request->exit)
                    ->get();

                $notAllowedReservations = [];
                foreach ($notAllowedReservations1 as $pivot) {
                    if (!in_array($pivot, $notAllowedReservations) && !in_array($pivot, $usedReservatins)) {
                        array_push($notAllowedReservations, $pivot);
                        array_push($usedReservatins, $pivot);
                    }
                }

                foreach ($notAllowedReservations2 as $pivot) {
                    if (!in_array($pivot, $notAllowedReservations) && !in_array($pivot, $usedReservatins)) {
                        array_push($notAllowedReservations, $pivot);
                        array_push($usedReservatins, $pivot);
                    }
                }

                foreach ($notAllowedReservations3 as $pivot) {
                    if (!in_array($pivot, $notAllowedReservations) && !in_array($pivot, $usedReservatins)) {
                        array_push($notAllowedReservations, $pivot);
                        array_push($usedReservatins, $pivot);
                    }
                }

                $bedsUsed = 0;
                if (count($notAllowedReservations) > 0) {
                    $bedsUsed++;
                }
                for ($i = 0; $i < count($notAllowedReservations); $i++) {
                    for ($j = 0; $j < count($notAllowedReservations); $j++) {
                        if (
                            $notAllowedReservations[$i]->exit >= $notAllowedReservations[$j]->entry
                            && $notAllowedReservations[$i]->entry < $notAllowedReservations[$j]->entry
                        ) {
                            $bedsUsed++;
                        }
                    }
                }
                if ($bedsUsed < $bed->places) {
                    array_push($availableBeds, $bed);
                }
            }
            return $availableBeds;
        }

        return Room::find($id)->beds;
    }

    public function destroy($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        Room::destroy($id);
    }
}
