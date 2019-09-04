<?php

namespace App\Http\Controllers;

use App\Bed;
use App\Room;
use App\Helpers\Pdf;
use App\Reservation;
use App\Pivots\BedRoomPivot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Room::with('beds')->get();
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

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
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Room::with('beds.inventars')->find($id);
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $room = Room::find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $room->$updatetKey = $updatedValue;
        $room->save();

        return $room;
    }

    public function addBed($roomId, $bedId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $pivot = BedRoomPivot::create();
        $pivot->room()->associate($roomId);
        $pivot->bed()->associate($bedId);
        $pivot->save();

        return $pivot;
    }

    public function removeBed($roomId, $pivotId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        DB::table('bed_room')->where('id', $pivotId)->delete();
    }

    public function getBeds(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

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
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        Room::destroy($id);
    }

    // GET /rooms/reservation/{date}
    public function evaluation($date)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $date = new \DateTime($date);

        return $this->getRoomsforEvaluation($date);
    }

    // GET /rooms/evaluation/{date}/pdf
    public function evaluationPdf(Request $request, $date)
    {
        Pdf::validateToken($request->token);

        $date = new \DateTime($date);
        $rooms = $this->getRoomsforEvaluation($date);
        $pdf = new Pdf('P');

        $pdf->documentTitle('Raum-Auswertung');
        $pdf->documentTitle("Datum: {$date->format('d.m.Y')}");

        foreach ($rooms as $room) {
            $pdf->newLine();

            $options = [
                'linesOnSamePage' => count($room['bedsWithReservation']) - 1,
                'rows' => 2
            ];
            $pdf->paragraph("{$room['number']} / {$room['name']} ({$room['location']})", 0, 'B', $options);

            foreach ($room['bedsWithReservation'] as $reservation) {
                unset($options['linesOnSamePage']);
                if (isset($reservation['employee'])) {
                    $pdf->paragraph("{$reservation['employee']['lastname']} {$reservation['employee']['firstname']} ({$reservation['bed']['name']})", 0, '', $options);
                } else if ($request->showFreeBeds === "true") {
                    $pdf->paragraph("{$reservation['bedName']} (Freie Plätze: {$reservation['freePlaces']})", 0, 'I', $options);
                }
            }
        }
        $pdf->export("Raum-Auswertung {$date->format('d.m.Y')}.pdf");
    }

    private function getRoomsforEvaluation(\DateTime $date)
    {
        $rooms = Room::with(array('BedRoomPivot.Reservations' => function ($query) use ($date) {
            $query->with('Employee');
            // $query->join('reservation', 'reservation.bed_room_id', '=', 'bed_room.id');
            $query->where('reservation.entry', '<=', $date->format('Y-m-d'));
            $query->where('reservation.exit', '>=', $date->format('Y-m-d'));
        }))->with('BedRoomPivot.Bed')
            ->orderBy('number')
            ->get()
            ->toArray();

        return $this->sortAndFormatRoomsForEvaluation($rooms);
    }

    private function sortAndFormatRoomsForEvaluation($rooms)
    {
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
    }
}
