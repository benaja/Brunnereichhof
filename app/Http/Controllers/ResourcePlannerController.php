<?php

namespace App\Http\Controllers;

use App\Car;
use App\Customer;
use App\Foodtype;
use App\Helpers\Pdf;
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
    public function getPublic(Request $request)
    {
        return $this->index($request);
    }

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
            $query->with(['rapportdetails.employee.languages', 'rapportdetails.project', 'cars', 'tools', 'customer.projects'])
                ->join('customer', 'customer.id', '=', 'resources.customer_id')
                ->orderBy('customer.lastname')
                ->select('resources.*');
        }]);

        return JsonResource::make($resourceDay);
    }

    public function store(ResourcePlannerDay $resourcePlannerDay, Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

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
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $data = $this->validate($request, [
            'comment' => ['string'],
            'start_time' => ['date_format:H:i'],
            'end_time' => ['date_format:H:i'],
        ]);

        $resource->update($data);

        // dump($resource->getOriginal('comment'));
        // if ($resource->plannerDay->history_enabled) {
        //     $updated = array_keys($data)[0];
        //     activity('resource-planner')->performedOn($resource)->log(Resource::namesMap[$updated].' von ');
        // }

        return ResourceResource::make($resource);
    }

    public function destroy(Resource $resource)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $resource->delete();
    }

    public function addRapportdetail(Resource $resource, Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $data = $this->validate($request, [
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'date' => ['required', 'date'],
        ]);

        $defaultFoodType = Foodtype::where('foodname', 'eichhof')->first();

        $rapportdetailExists = Rapportdetail::where('employee_id', $data['employee_id'])
            ->where('resource_id', $resource->id)
            ->first();

        abort_if($rapportdetailExists, 400, 'Employee already exists fot that day and customer');

        $rapportdetail = Rapportdetail::where('employee_id', $data['employee_id'])
            ->where('date', $resource->date)
            ->where('customer_id', $resource->customer_id)
            ->first();

        if (! $rapportdetail) {
            $date = Carbon::parse($resource->date);
            $rapportdetail = Rapportdetail::create([
                    'date' => $resource->date,
                    'day' => $date->dayOfWeekIso - 1,
                    'contract_type' => 'work_contract',
                    'customer_id' => $resource->customer->id,
                    'employee_id' => $data['employee_id'],
                    'rapport_id' => $resource->rapport->id,
                    'foodtype_id' => $defaultFoodType->id,
                    'hours' => Settings::value('resourcePlannerDefaultDuration'),
                    'resource_id' => $resource->id,
                ]);
        } else {
            $rapportdetail->update([
                'hours' => Settings::value('resourcePlannerDefaultDuration'),
                'resource_id' => $resource->id,
            ]);
        }

        $rapportdetail->load('employee.languages');

        return RapportdetailResource::make($rapportdetail);
    }

    public function deleteRapportdetail(Rapportdetail $rapportdetail)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $rapportdetail->delete();
    }

    public function addCar(Resource $resource, Car $car)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $resource->cars()->attach($car);

        return  CarResource::make($car);
    }

    public function removeCar(Resource $resource, Car $car)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $resource->cars()->detach($car);
    }

    public function addTool(Resource $resource, Tool $tool, Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $data = $this->validate($request, [
            'amount' => ['integer'],
        ]);

        $resource->tools()->attach($tool, $data);

        return  ToolResource::make($tool);
    }

    public function removeTool(Resource $resource, Tool $tool)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $resource->tools()->detach($tool);
    }

    public function updatePlannerDay(ResourcePlannerDay $resourcePlannerDay, Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $data = $this->validate($request, [
            'completed'=> ['required', 'boolean'],
            'history_enabled'=> ['required', 'boolean'],
        ]);

        $resourcePlannerDay->update($data);
    }

    public function updateToolsPivot(Resource $resource, Tool $tool, Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_write']);

        $data = $this->validate($request, [
            'amount' => ['required', 'integer'],
        ]);

        DB::table('resource_tool')
            ->where('resource_id', $resource->id)
            ->where('tool_id', $tool->id)
            ->update($data);
    }

    public function generatePdf(ResourcePlannerDay $resourcePlannerDay)
    {
        auth()->user()->authorize(['superadmin'], ['resource_planner_read']);

        $resources = $resourcePlannerDay->resources()
            ->with(['rapportdetails' => function ($query) {
                $query->with('employee.languages', 'project');
            }, 'cars', 'tools', 'customer'])
            ->join('customer', 'customer.id', '=', 'resources.customer_id')
            ->orderBy('customer.lastname')
            ->select('resources.*')
            ->get();

        $pdf = new Pdf('P');

        $formatedDate = $resourcePlannerDay->date->format('d.m.Y');

        foreach ($resources as $index => $resource) {
            if ($index !== 0) {
                $pdf->addNewPage();
            }
            $title = "Einsatzplan {$resource->customer->lastname} {$resource->customer->firstname} {$formatedDate}";
            $pdf->textToInsertOnPageBreak = $title;
            $pdf->documentTitle($title);

            $startTime = Carbon::parse($resource->start_time)->format('H:i');
            $endTime = Carbon::parse($resource->end_time)->format('H:i');
            $pdf->paragraph("Start: {$startTime} Ende: {$endTime}");

            if ($resource->comment) {
                $pdf->paragraph('Kommentar', 0, 'B');
                $pdf->paragraph($resource->comment);
            }

            $employeesTable = $resource->rapportdetails->map(fn ($rapportdetail) => [$rapportdetail->employee->name(), $rapportdetail->hours, optional($rapportdetail->project)->name]);

            $toolsTable = $resource->tools->map(fn ($tool) => [$tool->name, $tool->pivot->amount]);

            $carsTable = $resource->cars->map(fn ($car) => [$car->name, $car->seats, $car->fuel, $car->comment, $car->important_comment]);

            $pdf->newLine();
            $pdf->paragraph('Mitarbeiter', 13);
            $pdf->table(['Name', 'Stunden', 'Projekt'], $employeesTable);

            $pdf->paragraph('Werkzeuge', 13);
            $pdf->table(['Name', 'Menge'], $toolsTable);

            $pdf->paragraph('Autos', 13);
            $pdf->table(['Name', 'Sitze', 'Treibstoff', 'Bemerkung', 'Wichtige Bemerkung'], $carsTable, [1, 0.3, 0.5, 1, 1]);
        }

        return $pdf->export('Einsatzplan '.$resourcePlannerDay->date->format('d_m_Y').'.pdf');
    }
}
