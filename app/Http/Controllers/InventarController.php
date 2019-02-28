<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Inventar;

class InventarController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET /inventars
    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        return Inventar::all();
    }

    // POST /inventars
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $this->validate($request, [
            'name' => 'required|max:100',
            'price' => 'required|numeric'
        ]);

        $inventar = Inventar::create([
            'name' => $request->name,
            'price' => $request->price
        ]);

        return $inventar;
    }

    // GET /inventars/$id
    public function show($id)
    {
        //
    }

    // PATCH /inventars/$id
    public function update(Request $request, $id)
    {
        //
    }

    // DELETE /inventars/$id
    public function destroy($id)
    {
        //
    }
}
