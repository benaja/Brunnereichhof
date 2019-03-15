<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bed;
use App\Inventar;

class BedController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Bed::all();
    }

    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $this->validate($request, [
            'name' => 'required|max:100',
            'width' => 'required|max:100',
            'places' => 'required|numeric'
        ]);

        $bed = Bed::create([
            'name' => $request->name,
            'width' => $request->width,
            'places' => $request->places
        ]);

        $inventarIds = array_map(function ($inventar) {
            return $inventar['id'];
        }, $request->inventars);

        $inventars = Inventar::findMany($inventarIds);

        $attachInentars = [];
        foreach ($inventars as $inventar) {
            $attachInentars[$inventar['id']] = [];
        }

        $count = 0;
        foreach ($attachInentars as $key => $attachInventar) {
            $attachInentars[$key]['amount'] = $request->inventars[$count]['amount'];
            $attachInentars[$key]['amount_2'] = $request->inventars[$count]['amount_2'];
            $count++;
        }

        $bed->inventars()->attach($attachInentars);
        return Bed::with('inventars')->find($bed->id);
    }

    public function show($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Bed::with('inventars')->find($id);
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $bed = Bed::find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $bed->$updatetKey = $updatedValue;
        $bed->save();

        return $bed;
    }

    public function addInventar(Request $request, $bedId, $inventarId)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $bed = Bed::find($bedId);
        if ($bed->inventars->where('id', $inventarId)) {
            $bed->inventars->pivot->amount++;
            $bed->inventars->pivot->save();
        } else {
            $bed->inventars()->attach($inventarId);
            $bed->save();
        }

        return Bed::with('inventars')->find($bedId);
    }

    public function destroy($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Bed::destroy($id);
    }
}
