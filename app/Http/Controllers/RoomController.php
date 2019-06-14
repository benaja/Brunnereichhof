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
            'number' => 'required|integer',
            'comment' => 'nullable|string|max:1000'
        ]);

        $room = Room::create([
            'name' => $request->name,
            'location' => $request->location,
            'comment' => $request->comment,
            'number' => $request->number
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

            foreach ($beds as $bed) {
                $usedBeds1 = BedRoomPivot::join('reservation', function ($join) {
                    $join->on('reservation.bed_room_id', '=', 'bed_room.id');
                })->where('reservation.bed_room_id', '=', $bed->pivot->id)
                    ->where('entry', '<=', $request->exit)
                    ->where('exit', '>=', $request->entry)
                    ->get()->toArray();

                $usedBeds2 = BedRoomPivot::join('reservation', function ($join) {
                    $join->on('reservation.bed_room_id', '=', 'bed_room.id');
                })->where('reservation.bed_room_id', '=', $bed->pivot->id)
                    ->where('entry', '<=', $request->exit)
                    ->where('exit', '>=', $request->exit)
                    ->get()->toArray();

                $usedBedsWithDubilcates = array_merge($usedBeds1, $usedBeds2);
                $usedBeds = [];
                foreach ($usedBedsWithDubilcates as $usedBed) {
                    if (!in_array($usedBed, $usedBeds)) {
                        array_push($usedBeds, $usedBed);
                    }
                }
                $bedsUsed = 0;
                if (count($usedBeds) == 1) {
                    $bedsUsed = 1;
                } else if (count($usedBeds) > 1) {
                    $bedsUsed = 1;
                    for ($i  = 0; $i < count($usedBeds); $i++) {
                        $pivot = $bed->pivot->id;
                        if ($usedBeds[$i]['bed_room_id'] == $pivot) {
                            for ($j = 0; $j < count($usedBeds); $j++) {
                                if (
                                    $usedBeds[$i]['exit'] >= $usedBeds[$j]['entry']
                                    && $usedBeds[$i]['entry'] <= $usedBeds[$j]['entry']
                                    && $i != $j
                                ) {
                                    $bedsUsed++;
                                }
                            }
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

    public function getRoomsWithBooking($date)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $date = new \DateTime($date);

        $rooms = Room::with(array('BedRoomPivot.Reservations' => function ($query) use ($date) {
            $query->with('Employee');
            // $query->join('reservation', 'reservation.bed_room_id', '=', 'bed_room.id');
            $query->where('reservation.entry', '<=', $date->format('Y-m-d'));
            $query->where('reservation.exit', '>=', $date->format('Y-m-d'));
        }))->with('BedRoomPivot.Bed')
            ->orderBy('number')
            ->get()
            ->toArray();

        foreach ($rooms as &$room) {
            $room['bedsWithReservation'] = [];
            foreach ($room["bed_room_pivot"] as &$bedRoomPivot) {
                $freePlacesInBed = $bedRoomPivot["bed"]["places"] - count($bedRoomPivot["reservations"]);
                if ($freePlacesInBed > 0) {
                    $empyReservation = [
                        'freePlaces' => $freePlacesInBed,
                        'bedName' => $bedRoomPivot["bed"]["name"]
                    ];
                    array_push($room['bedsWithReservation'], $empyReservation);
                }
                foreach ($bedRoomPivot["reservations"] as $reservation) {
                    $reservation['bed'] = $bedRoomPivot['bed'];
                    array_push($room['bedsWithReservation'], $reservation);
                }
            }
            usort($room['bedsWithReservation'], function ($a, $b) {
                if (isset($a['employee']) && isset($b['employee'])) {
                    $comparison = strcasecmp($a['employee']['lastname'], $b['employee']['lastname']);
                    return $comparison;
                } else if (isset($a['employee'])) return -1;
                else if (isset($b['employee'])) return 1;
                else return 0;
            });
        }

        return $rooms;


        // $reservations = Reservation::with('Employee')
        //     ->with('BedRoomPivot.Bed')
        //     ->with('BedRoomPivot.Room')
        //     ->where('entry', '<=', $date->format('Y-m-d'))
        //     ->where('exit', '>=', $date->format('Y-m-d'))
        //     ->get()
        //     ->groupBy('BedRoomPivot.room_id')
        //     ->toArray();

        // usort($reservations, function ($a, $b) {
        //     return $a[0]['bed_room_pivot']['room']['number'] <=> $b[0]['bed_room_pivot']['room']['number'];
        // });

        // return $reservations;
    }
}
