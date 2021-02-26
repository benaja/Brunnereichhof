<?php

namespace App\Http\Controllers;

use App\Car;
use App\Customer;
use App\Foodtype;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\RapportdetailResource;
use App\Http\Resources\ResourceResource;
use App\Rapport;
use App\Rapportdetail;
use App\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResourcePlannerController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date');
        if ($date) {
            $date = Carbon::parse($date);
        } else {
            $date = Carbon::now();
        }

        $resources = Resource::where('date', $date)
            ->with(['rapportdetails.employee', 'cars', 'tools', 'customer'])
            ->get();

        return ResourceResource::collection($resources);
    }

    public function getDay($date)
    {
        $date = Carbon::parse($date);

        $customers = Customer::whereHas('resources', function ($query) use ($date) {
            $query->where('date', $date);
        })
            ->with(['resources' => function ($query) use ($date) {
                $query->with(['rapportdetails.employee', 'cars', 'tools']);
                $query->where('date', $date);
            }])
            ->get();

        return CustomerResource::collection($customers);
    }

    public function store(Customer $customer, Request $request)
    {
        $data = $this->validate($request, [
            'date' => ['required', 'date']
        ]);
        $date = Carbon::parse($data['date']);
        $firstDayOfWeek = $date->clone()->weekday(0);

        $rapport = $customer->rapports()
            ->where('startdate', $firstDayOfWeek)
            ->first();

        if (!$rapport) {
            $rapport = Rapport::create([
                'customer_id' => $customer->id,
                'startdate' => $firstDayOfWeek
            ]);
        }

        $resource = Resource::create([
            'date' => $date,
            'customer_id' => $customer->id,
            'rapport_id' => $rapport->id
        ]);

        return ResourceResource::make($resource);
    }

    public function addRapportdetail(Resource $resource, Request $request)
    {
        $data = $this->validate($request, [
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'date' => ['required', 'date']
        ]);

        $date = Carbon::parse($data['date']);

        $defaultFoodType = Foodtype::where('foodname', 'eichhof')->first();

        $rapportdetailExists = Rapportdetail::where('employee_id', $data['employee_id'])
            ->where('resource_id', $resource->id)
            ->first();

        abort_if($rapportdetailExists, 400, 'Employee already exists fot that day and customer');

        $rapportdetail = Rapportdetail::create([
            'date' => $date,
            'day' => $date->dayOfWeek,
            'contract_type' => 'work_contract',
            'customer_id' => $resource->customer->id,
            'employee_id' => $data['employee_id'],
            'rapport_id' => $resource->rapport->id,
            'foodtype_id' => $defaultFoodType->id,
            'resource_id' => $resource->id
        ]);

        $rapportdetail->load('employee');

        return RapportdetailResource::make($rapportdetail);
    }

    public function deleteRapportdetail(Rapportdetail $rapportdetail)
    {
        $rapportdetail->delete();
    }
}
