<?php

namespace App\Http\Controllers;

use App\Bed;
use App\Room;
use App\Helpers\Pdf;
use App\Reservation;
use App\Helpers\Settings;
use App\Helpers\Utils;
use App\Pivots\BedRoomPivot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\RoomImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        if (isset($request->deleted)) return Room::with('beds')->onlyTrashed()->get();
        else if (isset($request->all)) return Room::with('beds')->withTrashed()->get();
        else return Room::with('beds')->get();
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

        DB::transaction(function () use ($request) {
            $room = Room::create([
                'name' => $request->name,
                'location' => $request->location,
                'comment' => $request->comment,
                'number' => $request->number
            ]);

            $beds = json_decode($request->beds, true);
            $bedIds = array_map(function ($bed) {
                return $bed['id'];
            }, $beds);

            foreach ($bedIds as $bedId) {
                $bed = Bed::find($bedId);
                $room->beds()->attach($bed);
            }

            $this->storeImages($request->file('images'), $room->id);

            return $room;
        });
    }

    public function show($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $room = Room::withTrashed()->with(['beds.inventars', 'images'])->find($id);

        return $room;
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        if (isset($request->deleted_at)) {
            $room = Room::withTrashed()->find($id);
            $room->restore();
            return response('success');
        }

        $room = Room::withTrashed()->find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $room->$updatetKey = $updatedValue;
        $room->save();

        return $room;
    }

    public function uploadImages(Request $request, $roomId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        return $this->storeImages($request->file('images'), $roomId);
    }

    public function deleteImage($imageId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $image = RoomImage::find($imageId);
        Storage::disk('s3')->delete($image->path);
        $image->delete();
    }

    public function addBed($roomId, $bedId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        return BedRoomPivot::create([
            'bed_id' => $bedId,
            'room_id' => $roomId
        ]);
    }

    public function removeBed($roomId, $pivotId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $doday = new \DateTime();
        $reservations = Reservation::where('bed_room_id', $pivotId)
            ->where(function ($query) use ($doday) {
                $query->where('entry', '>=', $doday->format('Y-m-d'));
                $query->orWhere('exit', '>=', $doday->format('Y-m-d'));
            })
            ->get();
        if (count($reservations) > 0) {
            return response('Bed is currently in use', 400);
        }

        BedRoomPivot::find($pivotId)->delete();
    }

    public function getBeds(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        if (isset($request->entry)) {
            return Room::withTrashed()
                ->find($id)
                ->bedRoomPivots
                ->filter(function ($bedPivot) use ($request) {
                    $reservations = $bedPivot->reservations()
                    ->where('entry', '<=', $request->exit)
                    ->where('exit', '>=', $request->entry)
                    ->get();
                    return count($reservations) < $bedPivot->bed->places;
                })->map(function($bedPivot) {
                    $bed = $bedPivot->bed;
                    $bed['pivot'] = [
                        'id' => $bedPivot->id
                    ];
                    return $bed;
                })->values();
        }

        return Room::find($id)->beds;
    }

    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $room = DB::table('room')
            ->join('bed_room', 'room.id', 'bed_room.room_id')
            ->join('reservation', 'reservation.bed_room_id', 'bed_room.id')
            ->where('reservation.exit', '>=', (new \DateTime())->format('Y-m-d'))
            ->where('reservation.deleted_at', null)
            ->where('room.id', $id)
            ->first();

        if ($room !== null) {
            return response('Room is currently in use.', 400);
        }

        $room = Room::find($id);
        $room->delete();
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
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

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
        return $pdf->export("Raum-Auswertung {$date->format('d.m.Y')}.pdf");
    }

    public function reservations(Request $request, $roomId) {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $startDate = Utils::firstDate($request->dateRangeType, $request->date);
        $endDate = Utils::lastDate($request->dateRangeType, $request->date);

        return $this->getReservationsByRoomAndTime($roomId, $startDate, $endDate);
    }

    public function reservationsPdf(Request $request, $roomId) {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();
        $room = Room::find($roomId);

        $firstDate = Utils::firstDate($request->dateRangeType, $request->date);
        $lastDate = Utils::lastDate($request->dateRangeType, $request->date);
        
        $this->pdf->documentTitle("Reservationen für Raum: {$room->name}");
        if ($request->dateRangeType === 'year') {
            $this->pdf->documentTitle("Jahr: {$firstDate->format('Y')}");
        } else {
            $monthName = Settings::getMonthName($firstDate);
            $this->pdf->documentTitle("{$monthName} {$firstDate->format('Y')}");
        }

        $reservations = $this->getReservationsByRoomAndTime($roomId, $firstDate, $lastDate);
        $this->reservationsPdfTable($reservations);

        $monthName = isset($monthName) ? $monthName : '';
        return $this->pdf->export("Reservationen für Raum {$room->name} {$monthName} {$firstDate->format('Y')}.pdf");
    }

    public function reservationsPdfByYear(Request $request, $roomId, $date)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();
        $dateTime = new \DateTime($date);

        $room = Room::find($roomId);
        $this->pdf->documentTitle("Reservationen für Raum: {$room->name}");
        $this->pdf->documentTitle("Jahr: {$dateTime->format('Y')}");
        $reservations = $this->getReservationsByYear($roomId, $date);
        $this->reservationsPdfTable($reservations);
        return $this->pdf->export("Reservationen für Raum {$room->name} {$dateTime->format('Y')}.pdf");
    }

    public function reservationsPdfByMonth(Request $request, $roomId, $date)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();
        $dateTime = new \DateTime($date);

        $room = Room::find($roomId);
        $monthName = Settings::getMonthName($dateTime);
        $this->pdf->documentTitle("Reservationen für Raum: {$room->name}");
        $this->pdf->documentTitle("{$monthName} {$dateTime->format('Y')}");
        $reservations = $this->getReservationsByMonth($roomId, $date);
        $this->reservationsPdfTable($reservations);
        return $this->pdf->export("Reservationen für Raum {$room->name} {$monthName} {$dateTime->format('Y')}.pdf");
    }

    // GET /pdf/sleep-over/rooms
    public function sleepOver(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        $this->pdf = new Pdf();
        $firstDate = Utils::firstDate($request->type, new \DateTime($request->date));
        $lastDate = Utils::lastDate($request->type, new \DateTime($request->date));
        $documentTitle = "Übernachtungen pro Zimmer \n";

        if ($request->type === 'year') {
            $documentTitle .= "Jahr: {$firstDate->format('Y')}";
        } else if ($request->type === 'month') {
            $documentTitle .= "Monat: {$firstDate->format('m.Y')}";
        } else {
            $documentTitle .= "Woche: {$firstDate->format('W')} ({$firstDate->format('d.m.Y')} - {$lastDate->format('d.m.Y')})";
        }
        $this->pdf->documentTitle($documentTitle);
        $this->pdf->textToInsertOnPageBreak = $documentTitle;

        $rooms = Room::whereIn('id', $request->rooms)->orderby('number')->get();
        $lines = [];
        $totalSleepOver = 0;

        foreach ($rooms as $room) {
            $reservations = $this->getReservationsByRoomAndTime($room->id, $firstDate, $lastDate);
            $sleepOver = Reservation::getSleepOver($reservations, $firstDate, $lastDate);
            array_push($lines, [$room->number, $room->name, $sleepOver]);
            $totalSleepOver += $sleepOver;
        }

        $this->pdf->documentTitle("Totale Übernachtungen: $totalSleepOver");
        $this->pdf->table(['Nummer', 'Zimmer', 'Übernachtungen'], $lines, [0.3, 1, 1]);

        if ($request->type === 'year') {
            return $this->pdf->export("Übernachtungen pro Zimmer {$firstDate->format('Y')}.pdf");
        } else if ($request->type === 'month') {
            return $this->pdf->export("Übernachtungen pro Zimmer {$firstDate->format('m-Y')}.pdf");
        }
        return $this->pdf->export("Übernachtungen pro Zimmer {$firstDate->format('W')} ({$firstDate->format('d-m-Y')} - {$lastDate->format('d-m-Y')}).pdf");
    }

    private function reservationsPdfTable($reservations)
    {
        $this->pdf->newLine();
        $headers = ['Eintritt', 'Austritt', 'Mitarbeiter', 'Bett'];
        $columns = [];
        foreach ($reservations as $reservation) {
            array_push($columns, [
                (new \DateTime($reservation->entry))->format('d.m.Y'),
                (new \DateTime($reservation->exit))->format('d.m.Y'),
                "{$reservation->employee->lastname} {$reservation->employee->lastname}",
                $reservation->bedRoomPivot->bed->name
            ]);
        }
        $this->pdf->table($headers, $columns);
    } 

    private function getReservationsByRoomAndTime($roomId, $firstDate, $lastdate)
    {
        return Reservation::with('employee')
            ->with('BedRoomPivot.Bed')
            ->join('bed_room', 'bed_room.id', '=', 'reservation.bed_room_id')
            ->where('bed_room.room_id', $roomId)
            ->where('entry', '<=', $lastdate->format('Y-m-d'))
            ->where('exit', '>=', $firstDate->format('Y-m-d'))
            ->orderBy('entry')
            ->select('reservation.*')
            ->get();
    }

    private function getRoomsforEvaluation(\DateTime $date)
    {
        $rooms = Room::all();
        foreach ($rooms as $room) {
            $room->bed_room_pivots = $room->bedRoomPivots()
                ->withTrashed()
                ->where(function ($query) use ($date) {
                    $query->where('deleted_at', '>', $date->format('Y-m-d') . ' 23:59:59')
                        ->orWhere('deleted_at', null);
                })
                ->where(function ($query) use ($date) {
                    $query->where('created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                        ->orWhere('created_at', null);
                })
                ->with(['Reservations' => function ($query) use ($date) {
                    $query->with('Employee');
                    $query->where('reservation.entry', '<=', $date->format('Y-m-d'));
                    $query->where('reservation.exit', '>=', $date->format('Y-m-d'));
                }])->with('Bed')
                ->get()->toArray();
        }
        return $this->sortAndFormatRoomsForEvaluation($rooms->toArray());
    }

    private function sortAndFormatRoomsForEvaluation($rooms)
    {
        foreach ($rooms as &$room) {
            $room['bedsWithReservation'] = [];
            foreach ($room["bed_room_pivots"] as &$bedRoomPivot) {
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

    private function storeImages($images, $roomId)
    {
        if ($images) {
            $createdImages = [];
            foreach ($images as $image) {
                $imagePath = Storage::disk('s3')->put('rooms', $image);
                $newImage = RoomImage::create([
                    'path' => $imagePath,
                    'room_id' => $roomId
                ]);
                array_push($createdImages, $newImage);
            }
            return $createdImages;
        }
    }
}
