<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Room;
use App\Bed;

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
