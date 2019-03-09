<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Room;
use App\Bed;
use Illuminate\Support\Facades\DB;

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
        $room->beds()->attach($bedId);
    }

    public function removeBed($roomId, $pivotId)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        DB::table('bed_room')->where('id', $pivotId)->delete();
    }

    public function beds($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Room::find($id)->beds;
    }

    public function destroy($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        Room::destroy($id);
    }
}
