<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Inventar;
use Illuminate\Http\Request;

class InventarController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET /inventars
    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Inventar::all();
    }

    // POST /inventars
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);
        $this->validate($request, [
            'name' => 'required|max:100',
            'price' => 'required|numeric',
        ]);

        $inventar = Inventar::create([
            'name' => $request->name,
            'price' => $request->price,
        ]);

        return $inventar;
    }

    // GET /inventars/$id
    public function show($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read']);

        return Inventar::find($id);
    }

    // PATCH /inventars/$id
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        $inventar = Inventar::find($id);
        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $inventar->$updatetKey = $updatedValue;
        $inventar->save();

        return $inventar;
    }

    // DELETE /inventars/$id
    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_write']);

        Inventar::destroy($id);
    }
}
