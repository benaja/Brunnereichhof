<?php

namespace App\Http\Controllers;

use App\Child;
use App\Http\Requests\ChildRequest;
use Illuminate\Http\Request;

class ChildrenController extends Controller
{
    public function store(ChildRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return $request->store();
    }

    public function update(Child $child, ChildRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return $request->update($child);
    }

    public function destroy(Child $child)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        $child->delete();
    }
}
