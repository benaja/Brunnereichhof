<?php

namespace App\Http\Controllers;

use App\Culture;
use Illuminate\Http\Request;

class CultureController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_read']);

        $cultures = Culture::where('isAutocomplete', 1)->get();

        return $cultures;
    }

    // POST project
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);

        return $request;
        $culture = Culture::find($request->culture->id);

        return $culture;
    }

    // GET project/{id}
    public function show($id)
    {
        //
    }

    // GET project/{id}/edit
    public function edit($id)
    {
        //
    }

    // PATCH project/{id}
    public function update(Request $request, $id)
    { }

    // DELETE project/{id}
    public function destroy($id)
    {
        //
    }
}
