<?php

namespace App\Http\Controllers;

use App\FamilyAllowance;
use App\Http\Requests\FamilyAllowanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyAllowancesController extends Controller
{
    public function update(FamilyAllowance $familyAllowance, FamilyAllowanceRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return JsonResource::make($request->update($familyAllowance));
    }
}
