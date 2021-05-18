<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Employee;
use App\Enums\UserTypeEnum;
use App\Foodtype;
use App\Http\Controllers\Controller;
use App\Http\Resources\RapportResource;
use App\Project;
use App\Rapport;
use App\Rapportdetail;
use App\Resource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RapportController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET rapport
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['rapport_read']);

        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            return Rapport::where('customer_id', auth()->user()->customer->id)->orderBy('startdate')->get();
        // return auth()->user()->customer->rapports->sortBy('startdate');
        } else {
            if ($request->get('per_page') > 0) {
                $paginatedResult = Rapport::orderBy('startdate', 'desc')
                    ->groupBy('startdate')
                    ->paginate($request->get('per_page'));

                $rapports = Rapport::whereIn('startdate', $paginatedResult->pluck('startdate'))
                    ->orderBy('startdate', 'desc')
                    ->get()
                    ->groupBy('startdate');
            } else {
                $rapports = Rapport::orderBy('startdate', 'desc')->get()->groupBy('startdate');
            }

            $rapportWeeks = collect();
            foreach ($rapports as $rapportGroup) {
                $date = Carbon::parse($rapportGroup[0]->startdate);
                $isFinished = true;
                $hours = 0;
                foreach ($rapportGroup as $rapport) {
                    if ($rapport->isFinished == 0) {
                        $isFinished = false;
                    }
                    $hours += $rapport->hours();
                }
                $week = [
                    'date' => $date->format('Y-m-d'),
                    'hours' => $hours,
                    'isFinished' => $isFinished,
                ];
                $rapportWeeks->push($week);
            }

            if ($request->get('per_page') > 0) {
                $paginatedResult->setCollection($rapportWeeks);

                return RapportResource::collection($paginatedResult);
            }

            return RapportResource::collection($rapportWeeks);
        }
    }

    // GET rapport/week/{week}
    public function showWeek(Request $request, $week)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_read']);

        $week = new \DateTime($week);
        $week->modify('monday this week');

        $rapports = Rapport::with('customer')->where('startdate', $week->format('Y-m-d'))->get();

        return $rapports;
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        $week = new \DateTime($request->week);
        $week->modify('monday this week');

        $rapport = Rapport::firstOrCreate([
            'startdate' => $week->format('Y-m-d'),
            'customer_id' => $request->customer_id,
        ]);

        if ($rapport->customer_id == null) {
            $rapport->customer_id = $request->customer_id;
            $rapport->startdate = $week->format('Y-m-d');
            $rapport->isFinished = false;

            $rapport->save();
        }

        return $rapport;
    }

    // GET rapport/{id}
    public function show(Request $request, Rapport $rapport)
    {
        $this->authorize('view', $rapport);

        $rapport = $this->rapportWithDetails($rapport, true);

        if (auth()->user()->customer) {
            return $rapport;
        }

        $employees = Employee::with('user')
            ->withTrashed()
            ->where('isGuest', false)
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'rapport' => $rapport,
            'employees' => $employees,
        ];
    }

    public function addEmployee(Request $request, Rapport $rapport)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        $date = Carbon::parse($rapport->startdate);
        $employee = Employee::find($request->employee_id);
        $defaultProject = Project::find($request->default_project_id);
        if (! $defaultProject) {
            $$defaultProject = Project::where('name', 'Allgemein')->first();
        }
        $defaultFoodType = Foodtype::where('foodname', 'eichhof')->first();

        $rapportdetails = $rapport->rapportdetails->where('employee_id', $employee->id);

        if (count($rapportdetails) == 0) {
            for ($i = 0; $i < 6; $i++) {
                $resource = Resource::firstOrCreate([
                    'date' => $date,
                    'rapport_id' => $rapport->id,
                    'customer_id' => $rapport->custoemr_id,
                ]);

                $rapportdetail = Rapportdetail::create([
                    'date' => $date,
                    'day' => $i,
                    'contract_type' => 'work_contract',
                    'customer_id' => $rapport->customer_id,
                    'resource_id' => $resource->id,
                ]);
                $rapportdetail->employee()->associate($employee);
                $rapportdetail->rapport()->associate($rapport);
                $rapportdetail->project()->associate($defaultProject);
                $rapportdetail->foodtype()->associate($defaultFoodType);

                $rapportdetail->save();
                $date->addDay();
            }
        } else {
            return response('employee already exists', 400);
        }

        $rapportdetails = Rapportdetail::with('employee')
            ->where([
                'rapport_id' => $rapport->id,
                'employee_id' => $employee->id,
            ])->get();

        return $rapportdetails;
    }

    public function removeEmployee(Rapport $rapport, $employeeId)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        Rapportdetail::where([
            'rapport_id' => $rapport->id,
            'employee_id' => $employeeId,
        ])->delete();

        return 'success';
    }

    // PATCH rapport/{id}
    public function update(Request $request, Rapport $rapport)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        if ($request->id) {
            $rapport->update([
                'comment_mo' => $request->comment_mo,
                'comment_tu' => $request->comment_tu,
                'comment_we' => $request->comment_we,
                'comment_th' => $request->comment_th,
                'comment_fr' => $request->comment_fr,
                'comment_sa' => $request->comment_sa,
                'isFinished' => $request->isFinished,
            ]);
            foreach ($request->rapportdetails as $rapprotdetailsByCustomer) {
                foreach ($rapprotdetailsByCustomer as $newRapportdetail) {
                    $rapportdetail = Rapportdetail::find($newRapportdetail['id']);
                    $rapportdetail->update([
                        'hours' => $newRapportdetail['hours'],
                        'foodtype_id' => $newRapportdetail['foodtype_id'],
                        'project_id' => $newRapportdetail['project_id'],
                        'contract_type' => $newRapportdetail['contract_type'],
                    ]);
                }
            }
        } else {
            $updatetKey = key($request->except('_token'));

            $updatedValue = (string) $request->$updatetKey;
            if ($updatedValue == '') {
                $updatedValue = null;
            }
            $rapport->$updatetKey = $updatedValue;
            $rapport->save();
        }

        return $this->rapportWithDetails(Rapport::find($rapport->id));
    }

    public function updateRapportdetail(Request $request, Rapportdetail $rapportdetail)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write', 'resource_planner_write']);

        $updatetKey = key($request->except('_token'));

        $updatedValue = (string) $request->$updatetKey;
        if ($updatedValue == '') {
            $updatedValue = null;
        }
        if ($updatetKey == 'project_id') {
            $project = Project::find($updatedValue);
            $rapportdetail->project()->associate($project);
        } elseif ($updatetKey == 'foodtype_id') {
            $foodtype = Foodtype::find($updatedValue);
            $rapportdetail->foodtype()->associate($foodtype);
        } else {
            $rapportdetail->$updatetKey = $updatedValue;
        }
        $rapportdetail->save();

        // Log::info("Updated Rapportdetail $rapportdetail->id with the key: $updatetKey");
        return [
            'foodtype_ok' => $rapportdetail->foodtype_ok,
        ];
    }

    public function updateMultibleRapportdetails(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        $rapportdetails = [];
        foreach ($request->rapportdetails as $newRapportdetail) {
            $rapportdetail = Rapportdetail::find($newRapportdetail['id']);
            $rapportdetail->project_id = $newRapportdetail['project_id'];
            $rapportdetail->foodtype_id = $newRapportdetail['foodtype_id'];
            $rapportdetail->contract_type = $newRapportdetail['contract_type'];
            $rapportdetail->save();
            array_push($rapportdetails, $rapportdetail);
        }

        return $rapportdetails;
    }

    public function daytotal($date)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_read']);

        $rapportdetailsByEmployee = Rapportdetail::with('employee')->where('date', '=', $date)->get()->groupBy('employee_id')->sortBy(function ($rapportdetails) {
            return $rapportdetails[0]->employee->lastname;
        }, SORT_NATURAL | SORT_FLAG_CASE);
        $dayTotals = [];
        foreach ($rapportdetailsByEmployee as $rapportdetails) {
            if ($rapportdetails->sum('hours') > 0) {
                array_push($dayTotals, [
                    'employee' => $rapportdetails[0]->employee,
                    'hours' => $rapportdetails->sum('hours'),
                ]);
            }
        }

        return $dayTotals;
    }

    // DELETE /rapport/{id}
    public function destroy(Request $request, Rapport $rapport)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        foreach ($rapport->rapportdetails as $rapportdetail) {
            $rapportdetail->delete();
        }

        $rapport->resources()->delete();

        $rapport->delete();
    }

    private function rapportWithDetails($rapport, $withEmployees = false)
    {
        $rapport->customer = $rapport->customer;
        if ($withEmployees) {
            $rapport->rapportdetails = Rapportdetail::with(['Employee', 'Project'])
                ->where('rapport_id', '=', $rapport->id)
                ->get()
                ->groupBy('employee_id')
                ->toArray();
        } else {
            $rapport->rapportdetails = Rapportdetail::where('rapport_id', '=', $rapport->id)
                ->get()
                ->groupBy('employee_id')
                ->toArray();
        }
        $rapport->rapportdetails = array_values($rapport->rapportdetails);

        return $rapport;
    }
}
