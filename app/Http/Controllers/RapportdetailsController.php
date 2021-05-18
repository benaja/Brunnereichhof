<?php

namespace App\Http\Controllers;

use App\Http\Requests\RapportdetailRequest;
use Illuminate\Http\Request;

class RapportdetailsController extends Controller
{
    public function store(RapportdetailRequest $request)
    {
        return $request->store();
    }
}
