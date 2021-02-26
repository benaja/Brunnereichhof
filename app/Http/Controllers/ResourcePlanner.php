<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Resources\CustomerResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResourcePlanner extends Controller
{
    public function getDay($date) {
        $date = Carbon::parse($date);

        $customers = Customer::whereHas('rapportdetails', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->with(['rapportdetails' => function ($query) use ($date) {
                $query->where('date', $date);
            }])
            ->get();

        return CustomerResource::collection($customers);
    }
}
