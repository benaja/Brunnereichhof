<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Foodtype;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\RapportdetailResource;
use App\Rapport;
use App\Rapportdetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResourcePlannerController extends Controller
{
    public function getDay($date) {
        $date = Carbon::parse($date);

        $customers = Customer::whereHas('rapportdetails', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->with(['rapportdetails' => function ($query) use ($date) {
                $query->with('employee');
                $query->where('date', $date);
            }])
            ->get();

        return CustomerResource::collection($customers);
    }

    public function addRapportdetail(Customer $customer, Request $request) {
        $data = $this->validate($request, [
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'date' => ['required', 'date']
        ]);

        $date = Carbon::parse($data['date']);
        $firstDayOfWeek = $date->clone()->weekday(1);

        dump($data['date']);

        $defaultFoodType = Foodtype::where('foodname', 'eichhof')->first();

        $rapport = $customer->rapports()
            ->where('startdate', $firstDayOfWeek)
            ->first();

        if (!$rapport) {
            $rapport = Rapport::create([
                'customer_id' => $customer->id,
                'startdate' => $firstDayOfWeek
            ]);
        }

        $rapportdetail = Rapportdetail::create([
            'date' => $date,
            'day' => $date->dayOfWeek,
            'contract_type' => 'work_contract',
            'customer_id' => $customer->id,
            'employee_id' => $data['employee_id'],
            'rapport_id' => $rapport->id,
            'foodtype_id' => $defaultFoodType->id
        ]);

        $rapportdetail->load('employee');

        return RapportdetailResource::make($rapportdetail);
        // $rapportdetail->employee()->associate($employee);
        // $rapportdetail->rapport()->associate($rapport);
        // $rapportdetail->project()->associate($defaultProject);
        // $rapportdetail->foodtype()->associate($defaultFoodType);

        


    }
}
