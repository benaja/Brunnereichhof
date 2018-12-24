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

        if ($request->type == "year") {

        } else if ($request->type == "month") {

        } else {
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
        $rapport->rapportdetails = $rapport->rapportdetails->groupBy('employee_id');

        $employees = Employee::where('isActive', 1)->get();

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
        $commonProject = Project::where('name', 'Allgemein')->first();

        $rapportdetails = $rapport->rapportdetails->where('employee_id', $employee->id);

        if (count($rapportdetails) == 0) {
            for ($i = 0; $i < 6; $i++) {
                $rapportdetail = Rapportdetail::create([
                    'date' => $date->format('Y-m-d'),
                    'day' => $i,
                ]);
                $rapportdetail->employee()->associate($employee);
                $rapportdetail->rapport()->associate($rapport);
                $rapportdetail->project()->associate($commonProject);

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

    public function updateComments(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $rapport->comment_mo = $request->comment_mo;
        $rapport->comment_tu = $request->comment_tu;
        $rapport->comment_we = $request->comment_we;
        $rapport->comment_th = $request->comment_th;
        $rapport->comment_fr = $request->comment_fr;
        $rapport->comment_sa = $request->comment_sa;
    }

    // PATCH rapport/{id}
    public function update(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);


        $updatetKey = key($request->except('_token'));

        $updatedValue = (string)$request->$updatetKey;
        if($updatedValue == ''){
            $updatedValue = null;
        }
        $rapport->$updatetKey = $updatedValue;
        $rapport->save();
        // if($request->type == 'hours'){
        //     $rapportdetail = $rapport->rapportdetails->where('employee_id', $request->employeeId)->where('day', $request->day)->first();

        //     $rapportdetail->hours = $request->hours;
        //     $rapportdetail->save();
        // }else if($request->type == 'food'){
        //     $rapportdetail = $rapport->rapportdetails->where('employee_id', $request->employeeId)->where('day', $request->day)->first();
        //     $rapportdetail->foodtype_id = $request->foodType;
        //     $rapportdetail->save();
        // }else if($request->type == 'addEmployee'){
            
        // }else if($request->type == 'projectAll'){
        //     $rapportdetails = $rapport->rapportdetails->where('day', $request->day);

        //     foreach($rapportdetails as $rapportdetail){
        //         $project = Project::find($request->projectId);
        //         $rapportdetail->project()->associate($project);
        //         $rapportdetail->save();
        //     }
        // }else if($request->type == "project"){
        //     $rapportdetail = $rapport->rapportdetails->where('employee_id', $request->employeeId)->where('day', $request->day)->first();
        //     $project = Project::find($request->projectId);
        //     $rapportdetail->project()->associate($project);
        //     $rapportdetail->save();
        // }else if($request->type == "comment"){
        //     $commentDay = $request->commentDay;
        //     $rapport->$commentDay = $request->comment;
        //     $rapport->save();
        // }else if($request->type == "deleteEmployee"){
        //     $rapportdetails = Rapportdetail::where('rapport_id', $rapport->id)->where('employee_id', $request->employeeId)->delete();
        // }else if($request->type == "isFinished"){
        //     $rapport->isFinished = $request->data;
        //     $rapport->save();
        // }

    }

    // DELETE /rapport/{id}
    public function destroy(Request $request, Rapport $rapport)
    {
        $request->user()->authorizeRoles(['admin']);

        $rapport->delete();
    }

    // POST rapport/convertdate
    public function convertDate(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $date = new \DateTime($request->date);

        $lastMonday = $date->modify("Monday this week")->format('d.m.Y');
        $nextSonday = $date->modify("+6 Days")->format('d.m.Y');
        $week = $date->format('W');

        $response = [
            'lastMonday' => $lastMonday,
            'nextSonday' => $nextSonday,
            'week' => $week

        ];

        return $response;
    }

    // GET rapport/{id}/pdf
    public function generatePdf(Request $request, Rapport $rapport)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf::SetFont('Arial', 'B', 16);
        Fpdf::AddPage('L');
        Fpdf::SetFont('Arial', 'B', 18);

        $header = [
            'Wochentag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
        ];

        $comments = ['Bemerkung', $rapport->comment_mo, $rapport->comment_tu, $rapport->comment_we, $rapport->comment_th, $rapport->comment_fr, $rapport->comment_sa];
        $rapportdetailsGruped = $rapport->rapportdetails->groupBy('employee_id');

        $totalTime = 0;
        foreach ($rapportdetailsGruped as $rapportdetails) {
            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $totalTime += $rapportdetail->hours;
                $counter++;
            }
        }

        $cellHeight = 8;

        Fpdf::SetFillColor(42, 168, 42);
        Fpdf::SetDrawColor(255);
        Fpdf::SetLineWidth(.3);
        Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
        Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
        // Fpdf::AddFont('Raleway','I', 'Raleway-Italic.php');

        Fpdf::SetFont('Raleway', 'B', 20);
        Fpdf::Cell(0, 15, utf8_decode("Kunde: {$rapport->customer->customer_number} {$rapport->customer->firstname} {$rapport->customer->lastname}"), 0, 2);
        Fpdf::SetFont('Raleway', '', 18);
        $startdate = new \DateTime($rapport->startdate);
        Fpdf::Cell(0, 12, "Zeitraum: {$startdate->format('Y')} / KW {$startdate->format('W')} ({$startdate->format('d.m.Y')} - {$startdate->modify('+6 day')->format('d.m.Y')})", 0, 2);
        Fpdf::Cell(0, 12, "Totale Arbeitsstunden: {$totalTime}");
        Fpdf::Ln();


        Fpdf::SetFont('Raleway', 'B', 12);
        Fpdf::SetTextColor(255);
        // Header
        $cellWidth = 40;
        for ($i = 0; $i < count($header); $i++) {
            Fpdf::Cell($cellWidth, $cellHeight, $header[$i], 0, 0, 'L', true);
        }
        Fpdf::Ln();
        // Color and font restoration
        Fpdf::SetFillColor(242, 242, 242);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 10);
        // Data
        $fill = false;
        $marginLeft = Fpdf::getX();
        $marginTop = Fpdf::getY();
        foreach ($comments as $comment) {
            Fpdf::SetXY($marginLeft, $marginTop);
            Fpdf::MultiCell(40, $cellHeight, utf8_decode($comment), 'L', $fill);
            $marginLeft += 40;
        }
        Fpdf::Ln();
        $fill = !$fill;


        $timePerDay = [0, 0, 0, 0, 0, 0];
        foreach ($rapportdetailsGruped as $rapportdetails) {
            Fpdf::cell(40, $cellHeight, utf8_decode("{$rapportdetails[0]->employee->firstname} {$rapportdetails[0]->employee->lastname}"), 0, 'R', 'L', $fill);

            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $timePerDay[$counter] += $rapportdetail->hours;
                $counter++;
                Fpdf::cell(40, $cellHeight, $rapportdetail->hours != null ? $rapportdetail->hours . " h" : "0" . " h", 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();


            Fpdf::cell(40, $cellHeight, "", 0, 'R', 'L', $fill);
            foreach ($rapportdetails as $rapportdetail) {
                Fpdf::cell(40, $cellHeight, utf8_decode($rapportdetail->project != null ? $rapportdetail->project->name : "keine Angabe"), 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();
            $fill = !$fill;
        }
        // Closing line
        Fpdf::cell(40, $cellHeight, "Total Zeit", 0, 'R', 'L', $fill);
        foreach ($timePerDay as $time) {
            Fpdf::cell(40, $cellHeight, $time . " h", 0, 'R', 'L', $fill);
        }

        $filename = "pdf/{$rapport->customer->firstname}_{$rapport->customer->lastname}_{$startdate->modify('-6 day')->format('d-m-Y')}.pdf";
        Fpdf::Output($filename, 'F');

        readfile($filename);
    }
}
