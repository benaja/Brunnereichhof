<?php

namespace App\Http\Controllers;

use App\Child;
use App\Http\Requests\ChildRequest;
use Illuminate\Http\Request;

class ChildrenController extends Controller
{
    public function store(ChildRequest $request)
    {
        return $request->store();
    }

    public function update(Child $child, ChildRequest $request)
    {
        return $request->update($child);
    }

    public function destroy(Child $child)
    {
        $child->delete();
    }
}
