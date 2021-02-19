<?php

namespace App\Http\Controllers;

use App\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResourcePlanner extends Controller
{
    public function getDay($date) {
        $date = Carbon::parse($date);

        Customer::with(['timerecords'])
    }
}
