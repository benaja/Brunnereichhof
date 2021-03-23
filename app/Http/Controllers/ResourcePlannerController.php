<?php

namespace App\Http\Controllers;

use App\Car;
use App\Customer;
use App\Foodtype;
use App\Http\Resources\CarResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\RapportdetailResource;
use App\Http\Resources\ResourceResource;
use App\Http\Resources\ToolResource;
use App\Rapport;
use App\Rapportdetail;
use App\Resource;
use App\ResourcePlannerDay;
use App\Settings;
use App\Tool;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

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

        $resourceDay = ResourcePlannerDay::firstOrCreate(['date' => $date]);

        $resourceDay->load(['resources' => function ($query) {
            $query->with(['rapportdetails.employee.languages', 'cars', 'tools', 'customer.projects'])
                ->join('customer', 'customer.id', '=', 'resources.customer_id')
                ->orderBy('customer.lastname')
                ->select('resources.*');
        }]);

        return JsonResource::make($resourceDay);
    }

    public function store(ResourcePlannerDay $resourcePlannerDay, Request $request)
    {
        $data = $this->validate($request, [
            'customer_id' => ['required', 'integer', 'exists:customer,id'],
        ]);
        $firstDayOfWeek = $resourcePlannerDay->date->clone()->weekday(0);

        $customer = Customer::find($data['customer_id']);

        $rapport = $customer->rapports()
            ->where('startdate', $firstDayOfWeek)
            ->first();

        if (! $rapport) {
            $rapport = Rapport::create([
                'customer_id' => $customer->id,
                'startdate' => $firstDayOfWeek,
            ]);
        }

        $resource = Resource::create([
            'date' => $resourcePlannerDay->date,
            'customer_id' => $customer->id,
            'rapport_id' => $rapport->id,
            'start_time' => Settings::value('resourcePlannerStartTime'),
            'end_time' => Settings::value('resourcePlannerEndTime'),
            'resource_planner_day_id' => $resourcePlannerDay->id,
        ]);

        $resource->load(['rapportdetails.employee.languages', 'cars', 'tools', 'customer']);

        return ResourceResource::make($resource);
    }

    public function update(Resource $resource, Request $request)
    {
        $data = $this->validate($request, [
            'comment' => ['string'],
            'start_time' => ['date_format:H:i'],
            'end_time' => ['date_format:H:i'],
        ]);

        $resource->update($data);

        // activity('resource-planner')->performedOn($resource)->log('Bearbeitet');

        return ResourceResource::make($resource);
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();
    }

    public function addRapportdetail(Resource $resource, Request $request)
    {
        $data = $this->validate($request, [
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'date' => ['required', 'date'],
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
            'hours' => Settings::value('resourcePlannerDefaultDuration'),
            'contract_type' => 'work_contract',
            'customer_id' => $resource->customer->id,
            'employee_id' => $data['employee_id'],
            'rapport_id' => $resource->rapport->id,
            'foodtype_id' => $defaultFoodType->id,
            'resource_id' => $resource->id,
        ]);

        $rapportdetail->load('employee.languages');

        return RapportdetailResource::make($rapportdetail);
    }

    public function deleteRapportdetail(Rapportdetail $rapportdetail)
    {
        $rapportdetail->delete();
    }

    public function addCar(Resource $resource, Car $car)
    {
        $resource->cars()->attach($car);

        return  CarResource::make($car);
    }

    public function removeCar(Resource $resource, Car $car)
    {
        $resource->cars()->detach($car);
    }

    public function addTool(Resource $resource, Tool $tool, Request $request)
    {
        $data = $this->validate($request, [
            'amount' => ['integer'],
        ]);

        $resource->tools()->attach($tool, $data);

        return  ToolResource::make($tool);
    }

    public function removeTool(Resource $resource, Tool $tool)
    {
        $resource->tools()->detach($tool);
    }

    public function updatePlannerDay(ResourcePlannerDay $resourcePlannerDay, Request $request)
    {
        $data = $this->validate($request, [
            'completed'=> ['required', 'boolean'],
            'history_enabled'=> ['required', 'boolean'],
        ]);

        $resourcePlannerDay->update($data);
    }

    public function updateToolsPivot(Resource $resource, Tool $tool, Request $request)
    {
        $data = $this->validate($request, [
            'amount' => ['required', 'integer'],
        ]);

        DB::table('resource_tool')
            ->where('resource_id', $resource->id)
            ->where('tool_id', $tool->id)
            ->update($data);
    }
}
