<?php

namespace App\Http\Controllers;

use Fpdf;
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

    // GET rapport/choosecustomer
    public function chooseCustomer(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $customers = Customer::all();
        $date = $request->date;

        return view('pages.admin.rapport.choose-customer', compact('customers', 'date'));
    }

    // GET rapport/addcustomer/{customrId}
    public function addCustomer(Request $request, Customer $customer)
    {
        $request->user()->authorizeRoles(['admin']);

        $startdate = new \DateTime($request->date);
        $startdate->modify("Monday this week");
        $rapport = Rapport::firstOrCreate(['startdate' => $startdate->format('Y-m-d'), 'customer_id' => $customer->id]);

        if ($rapport->isFinished == null) {
            $rapport->isFinished = 0;
            $rapport->startdate = $startdate->format('Y-m-d');
            $rapport->rapporttype = 'week';
            $rapport->save();
        }

        $customer->rapports()->save($rapport);

        return redirect('/rapport/' . $rapport->id);
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
            $rapport->save();
        }

        return $rapport;
    }

    // POST rapport
    public function create(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $customer = Customer::find($request->customer);

        if ($request->type == "year") { } else if ($request->type == "month") { } else {
            $lastRapport = $customer->rapports->where('rapporttype', 'week')->sortByDesc('startdate')->first();

            if ($lastRapport == null) {
                $newRapportDate = new \DateTime('last monday');
            } else {
                $lastRapportDate = new \DateTime($lastRapport->startdate);

                // Modify the date it contains
                $newRapportDate = $lastRapportDate->modify('next monday');
            }

            $rapport = Rapport::create([
                'isFinished' => 0,
                'startdate' => $newRapportDate,
                'rapporttype' => 'week'
            ]);

            $customer->rapports()->save($rapport);

            return redirect('/rapport/' . $rapport->id);
        }
    }

    // GET rapport/{id}
    public function show(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapport->customer = $rapport->customer;
        $rapport->rapportdetails = Rapportdetail::where('rapport_id', '=', $rapport->id)->get()->groupBy('employee_id')->toArray();
        $rapport->rapportdetails = array_values($rapport->rapportdetails);

        $employees = Employee::all()->sortBy('lastname')->toArray();
        $employees = array_values($employees);

        return [
            'rapport' => $rapport,
            'employees' => $employees
        ];
    }

    // GET rapport/show
    public function showAll(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $rapports = Rapport::take(100)->get()->sortBy('startdate')->groupBy('startdate');

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

        if (count($rapportWeeks) > 0) {
            $newWeek = new \DateTime($rapportWeeks[0]['date']->format('d.m.Y'));
            $newWeek->modify("+7 days");
        } else {
            $newWeek = new \DateTime("now");
            $newWeek->modify("last monday");
        }

        return view('pages.admin.rapport.show-all', compact('rapportWeeks', 'newWeek'));
    }

    public function addEmployee(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $date = new \DateTime($rapport->startdate);
        $employee = Employee::find($request->employee_id);
        $defaultProject = Project::find($request->default_project);
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

        $updatetKey = key($request->except('_token'));

        $updatedValue = (string)$request->$updatetKey;
        if ($updatedValue == '') {
            $updatedValue = null;
        }
        $rapport->$updatetKey = $updatedValue;
        $rapport->save();
        return Rapport::with('customer')->find($rapport->id);
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
    }

    public function updateMultibleRapportdetails(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        foreach ($request->rapportdetails as $newRapportdetail) {
            $rapportdetail = Rapportdetail::find($newRapportdetail['id']);
            $rapportdetail->project_id = $newRapportdetail['project_id'];
            $rapportdetail->foodtype_id = $newRapportdetail['foodtype_id'];
            $rapportdetail->save();
        }
        return;
    }

    // DELETE /rapport/{id}
    public function destroy(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapport->delete();
    }
}
