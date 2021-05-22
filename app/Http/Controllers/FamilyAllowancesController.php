<?php

namespace App\Http\Controllers;

use App\FamilyAllowance;
use App\Http\Requests\FamilyAllowanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyAllowancesController extends Controller
{
    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_read']);

        $familyAllowances = FamilyAllowance::where('has_family_allowance', true)
            ->where(function ($query) {
                $query->has('worker')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('isActive', true);
                    });
            })
            ->with('familyAllowanceable')
            ->get();

        return JsonResource::make($familyAllowances);
    }

    public function update(FamilyAllowance $familyAllowance, FamilyAllowanceRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['family_allowance_write']);

        return JsonResource::make($request->update($familyAllowance));
    }
}
