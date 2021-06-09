<?php

namespace App\Http\Controllers;

use App\Http\Requests\RapportdetailRequest;
use App\Rapportdetail;
use Illuminate\Http\Request;

class RapportdetailsController extends Controller
{
    public function store(RapportdetailRequest $request)
    {
        return $request->store();
    }

    public function update(Rapportdetail $rapportdetail, RapportdetailRequest $request)
    {
        $rapportdetail = $request->update($rapportdetail);

        return [
            'foodtype_ok' => $rapportdetail->foodtype_ok,
        ];
    }
}
