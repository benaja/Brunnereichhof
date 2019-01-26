<?php

namespace App\Http\Controllers;

use App\Culture;
use App\Customer;
use Illuminate\Http\Request;
use App\Hourrecord;

class CultureController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        $cultures = Culture::where('isAutocomplete', 1)->get();

        return $cultures;
    }

    // POST project
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

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
    {

    }

    // DELETE project/{id}
    public function destroy($id)
    {
        //
    }
}
