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

        return $request->inventars;

        $inventars = Inventar::findMany($request->inventars);

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
