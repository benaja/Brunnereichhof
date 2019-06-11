<?php

namespace App\Http\Controllers;

use App\Project;
use App\Rapport;
use App\Customer;
use App\Employee;
use App\Rapportdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Foodtype;

class RapportController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET rapport
    public function index(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapports = Rapport::get()->sortBy('startdate')->groupBy('startdate');

        $rapportWeeks = array();
        foreach ($rapports as $rapportGroup) {
            $date = new \DateTime($rapportGroup[0]->startdate);
            $isFinished = true;
            foreach ($rapportGroup as $rapport) {
                if ($rapport->isFinished == 0) {
                    $isFinished = false;
                }
            }
            $week = [
                'date' => $date,
                'isFinished' => $isFinished
            ];
            array_push($rapportWeeks, $week);
        }

        $rapportWeeks = array_reverse($rapportWeeks);

        return $rapportWeeks;
    }

    // GET rapport/week/{week}
    public function showWeek(Request $request, $week)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $week = new \DateTime($week);
        $week->modify('monday this week');

        $rapports = Rapport::where('startdate', $week->format('Y-m-d'))->get();

        foreach ($rapports as $rapport) {
            $rapport->customer = $rapport->customer;
        }
        return $rapports;
    }

    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $week = new \DateTime($request->week);
        $week->modify('monday this week');

        $rapport = Rapport::firstOrCreate(
            ['startdate' => $week->format('Y-m-d'), 'customer_id' => $request->customer_id]
        );

        if ($rapport->customer_id == null) {
            $rapport->customer_id = $request->customer_id;
            $rapport->startdate = $week->format('Y-m-d');
            $rapport->isFinished = false;

            // $defaultProject = Project::where('name', 'Allgemein')->first();
            // $rapport->defaultProject()->associate($defaultProject);
            $rapport->save();
        }

        return $rapport;
    }

    // GET rapport/{id}
    public function show(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapport = $this->rapportWithDetails($rapport);

        $employees = Employee::orderBy('lastname')->get();

        return [
            'rapport' => $rapport,
            'employees' => $employees
        ];
    }

    public function addEmployee(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $date = new \DateTime($rapport->startdate);
        $employee = Employee::find($request->employee_id);
        $defaultProject = Project::find($request->default_project_id);
        if (!$defaultProject) {
            $$defaultProject = Project::where('name', 'Allgemein')->first();
        }
        $defaultFoodType = Foodtype::where('foodname', 'eichhof')->first();

        $rapportdetails = $rapport->rapportdetails->where('employee_id', $employee->id);

        if (count($rapportdetails) == 0) {
            for ($i = 0; $i < 6; $i++) {
                $rapportdetail = Rapportdetail::create([
                    'date' => $date->format('Y-m-d'),
                    'day' => $i,
                ]);
                $rapportdetail->employee()->associate($employee);
                $rapportdetail->rapport()->associate($rapport);
                $rapportdetail->project()->associate($defaultProject);
                $rapportdetail->foodtype()->associate($defaultFoodType);

                $rapportdetail->save();
                $date->modify('+1 day');
            }
        } else {
            return response('employee already exists', 400);
        }

        $rapportdetails = Rapportdetail::where([
            'rapport_id' => $rapport->id,
            'employee_id' => $employee->id
        ])->get();
        return $rapportdetails;
    }

    public function removeEmployee(Rapport $rapport, Employee $employee)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        Rapportdetail::where([
            'rapport_id' => $rapport->id,
            'employee_id' => $employee->id
        ])->delete();

        return 'success';
    }

    // PATCH rapport/{id}
    public function update(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        if ($request->id) {
            $rapport->update([
                'comment_mo' => $request->comment_mo,
                'comment_tu' => $request->comment_tu,
                'comment_we' => $request->comment_we,
                'comment_th' => $request->comment_th,
                'comment_fr' => $request->comment_fr,
                'comment_sa' => $request->comment_sa,
                'isFinished' => $request->isFinished
            ]);
            foreach ($request->rapportdetails as $rapprotdetailsByCustomer) {
                foreach ($rapprotdetailsByCustomer as $newRapportdetail) {
                    $rapportdetail = Rapportdetail::find($newRapportdetail['id']);
                    $rapportdetail->update([
                        'hours' => $newRapportdetail['hours'],
                        'foodtype_id' => $newRapportdetail['foodtype_id'],
                        'project_id' => $newRapportdetail['project_id']
                    ]);
                }
            }
        } else {
            $updatetKey = key($request->except('_token'));

            $updatedValue = (string)$request->$updatetKey;
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $updatetKey = key($request->except('_token'));

        $updatedValue = (string)$request->$updatetKey;
        if ($updatedValue == '') {
            $updatedValue = null;
        }
        if ($updatetKey == 'project_id') {
            $project = Project::find($updatedValue);
            $rapportdetail->project()->associate($project);
        } else if ($updatetKey == 'foodtype_id') {
            $foodtype = Foodtype::find($updatedValue);
            $rapportdetail->foodtype()->associate($foodtype);
        } else {
            $rapportdetail->$updatetKey = $updatedValue;
        }
        $rapportdetail->save();

        return [
            'foodtype_ok' => $rapportdetail->foodtype_ok
        ];
    }

    public function updateMultibleRapportdetails(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapportdetails = [];
        foreach ($request->rapportdetails as $newRapportdetail) {
            $rapportdetail = Rapportdetail::find($newRapportdetail['id']);
            $rapportdetail->project_id = $newRapportdetail['project_id'];
            $rapportdetail->foodtype_id = $newRapportdetail['foodtype_id'];
            $rapportdetail->save();
            array_push($rapportdetails, $rapportdetail);
        }
        return $rapportdetails;
    }

    public function daytotal($date)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapportdetailsByEmployee = Rapportdetail::with('employee')->where('date', '=', $date)->get()->groupBy('employee_id')->sortBy(function ($rapportdetails) {
            return $rapportdetails[0]->employee->lastname;
        }, SORT_NATURAL | SORT_FLAG_CASE);
        $dayTotals = [];
        foreach ($rapportdetailsByEmployee as $rapportdetails) {
            if ($rapportdetails->sum('hours') > 0) {
                array_push($dayTotals, [
                    'employee' => $rapportdetails[0]->employee,
                    'hours' => $rapportdetails->sum('hours')
                ]);
            }
        }
        return $dayTotals;
    }

    // DELETE /rapport/{id}
    public function destroy(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapport->delete();
    }

    private function rapportWithDetails($rapport)
    {
        $rapport->customer = $rapport->customer;
        $rapport->rapportdetails = Rapportdetail::where('rapport_id', '=', $rapport->id)->get()->groupBy('employee_id')->toArray();
        $rapport->rapportdetails = array_values($rapport->rapportdetails);
        return $rapport;
    }
}
