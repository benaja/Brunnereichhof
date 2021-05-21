<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuarterRequest;
use App\Quarter;
use Illuminate\Http\Request;

class QuartersController extends Controller
{
    public function store(QuarterRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return $request->store();
    }

    public function update(Quarter $quarter, QuarterRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return $request->update($quarter);
    }

    public function destroy(Quarter $quarter)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        $quarter->delete();
    }
}
