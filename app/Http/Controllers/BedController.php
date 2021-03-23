<?php

namespace App\Http\Controllers;

use App\Bed;
use App\Http\Controllers\Controller;
use App\Inventar;
use App\Pivots\BedRoomPivot;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Bed::all();
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $this->validate($request, [
            'name' => 'required|max:100',
            'width' => 'required|max:100',
            'places' => 'required|numeric',
        ]);

        $bed = Bed::create([
            'name' => $request->name,
            'width' => $request->width,
            'places' => $request->places,
            'comment' => $request->comment,
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
            $attachInentars[$key]['amount'] = $request->inventars[$count]['pivot']['amount'];
            $attachInentars[$key]['amount_2'] = $request->inventars[$count]['pivot']['amount_2'];
            $count++;
        }

        $bed->inventars()->attach($attachInentars);

        return Bed::with('inventars')->find($bed->id);
    }

    public function show($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Bed::with('inventars')->find($id);
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $bed = Bed::find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $bed->$updatetKey = $updatedValue;
        $bed->save();

        return $bed;
    }

    public function addInventar(Request $request, $bedId, $inventarId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $bed = Bed::find($bedId);
        $inventar = $bed->inventars->where('id', $inventarId)->first();
        if ($inventar) {
            if ($request->addAmount2) {
                $inventar->pivot->amount_2++;
            } else {
                $inventar->pivot->amount++;
                $inventar->pivot->amount_2 = $inventar->pivot->amount;
            }
            $inventar->pivot->save();
        } else {
            $bed->inventars()->attach([
                $inventarId => ['amount' => 1, 'amount_2' => 1],
            ]);
            $bed->save();
        }

        return Bed::with('inventars')->find($bedId);
    }

    public function removeInventar(Request $request, $bedId, $inventarId)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $bed = Bed::find($bedId);
        $inventar = $bed->inventars->where('id', $inventarId)->first();
        if ($request->removeAmount2 == 'true') {
            if ($inventar->pivot->amount_2 > 0) {
                $inventar->pivot->amount_2--;
                $inventar->pivot->save();
            }
        } else {
            if ($inventar->pivot->amount_2 == 1) {
                $bed->inventars()->detach($inventarId);
            } else {
                $inventar->pivot->amount--;
                $inventar->pivot->amount_2 = $inventar->pivot->amount;
                $inventar->pivot->save();
            }
        }

        return Bed::with('inventars')->find($bedId);
    }

    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        return Bed::destroy($id);
    }
}
