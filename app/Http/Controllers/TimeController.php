<?php

namespace App\Http\Controllers;

use DB;
use Session;
use App\User;
use App\Hour;
use App\Worktype;
use App\Settings;
use App\Timerecord;
use App\Rules\ValidTime;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TimeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET time
    public function index(Request $request){
        $request->user()->authorizeRoles(['worker']);

        if($request->user()->isPasswordChanged == 0){
            return redirect('/profile/edit?initialChange=true');
        }

        if(isset($request->date)){
            $currentDate = new \DateTime($request->date);
        }else{
            $currentDate = new \DateTime('today');
        }

        $settings = Settings::first();
        $hasUpdated = Session::get('hasUpdated');
        $dayNamesGerman = $this->dayNamesGerman;

        $agent = new Agent();
        if($agent->isMobile()){
            $timerecord = Timerecord::firstOrNew(['date' => $currentDate->format('Y-m-d')],
                ['user_id' => $request->user()->id]);
    
            $isMealDefault = $request->user()->ismealdefault;
            $isMobile = true;
            $totalHours = $timerecord->totalHours();
    
            return view('pages.worker.index-mobile', compact("currentDate", "error", "timerecord", "isMealDefault", "settings", "hasUpdated", "isMobile", "totalHours"));
        }else{
            $tmpDate = clone $currentDate;
            $tmpDate->modify('monday this week');
            $timerecords = [];
            $totalHours = 0;
            for($i = 0; $i < 7; $i++){
                $timerecord = Timerecord::where('date', $tmpDate->format('Y-m-d'))
                ->where('user_id', $request->user()->id)
                ->first();
                if($timerecord == null){
                    $timerecord = new Timerecord();
                    $timerecord->hours = [];
                    $timerecord->date = $tmpDate->format('Y-m-d');
                }else{
                    $totalHours += $timerecord->totalHours();
                }
                array_push($timerecords, $timerecord);
                $tmpDate->modify("+1 day");
            }

            $isMealDefault = $request->user()->ismealdefault;
            $isMobile = false;

            return view('pages.worker.index', compact("currentDate", "error", "timerecords", "timerecord", "isMealDefault", "settings", "hasUpdated", "dayNamesGerman", "isMobile", "totalHours"));
        }
    }

    // POST time
    public function store(Request $request){
        $request->user()->authorizeRoles(['worker']);
        Session::put('hasUpdated', false);
        $date = new \DateTime($request->date);
        $timerecord = Timerecord::firstOrCreate(
            ['date' => $date->format('Y-m-d')],
            ['user_id' => $request->user()->id]
        );

        $request->validate([
            'workType' => 'required|string',
            'from' => 'required|before:to',
            'to' => ['required', new ValidTime($request->from, $timerecord)],
            'interrupt' => 'nullable|boolean',
            'lunch' => 'nullable|boolean',
            'date' => 'required|date',
            'commnet' => 'nullable|string'
        ]);

        if($request->interrupt == 1){
            $request->validate([
                'interruptFrom' => 'required|after:from',
                'interruptTo' => 'required|after:interruptFrom|before:to'
            ]);
        }

        $timerecord->lunch = $request->lunch;
        $timerecord->save();

        if($request->interrupt == 1){
            if(!$this->createHour($timerecord, $request->from, $request->interruptFrom, $request->comment, $request->workType)){
                return redirect("/time?date=".$date->format('d.m.Y')."&error=overlapping");
            }
            if(!$this->createHour($timerecord, $request->interruptTo, $request->to, $request->comment, $request->workType)){
                return redirect("/time?date=".$date->format('d.m.Y')."&error=overlapping");
            }
        }else{
            if(!$this->createHour($timerecord, $request->from, $request->to, $request->comment, $request->workType)){
                return redirect("/time?date=".$date->format('d.m.Y')."&error=overlapping");
            }
        }
        $request->user()->ismealdefault = $request->lunch;
        $request->user()->save();

        $request->user()->timerecords()->save($timerecord);

        return redirect("/time?date=".$date->format('d.m.Y'));
    }

    // PATCH time/{id}
    public function update(Request $request, $id){
        $request->user()->authorizeRoles(['worker']);

        $hour = Hour::find($id);
        if ($hour->timerecord->user == $request->user()) {
            $request->validate([
                'workType' => 'required|string',
                'from' => 'required|before:to',
                'to' => ['required', new ValidTime($request->from, $hour->timerecord, $hour->id)],
                'lunch' => 'nullable|boolean',
                'commnet' => 'nullable|string'
            ]);
            
            $date = new \DateTime($hour->timerecord->date);

            $hour->from = $request->from;
            $hour->to = $request->to; 
            $hour->comment = $request->comment;

            $worktype = Worktype::where('name', $request->workType)->first();
            $worktype->hours()->save($hour);

            $hour->save();

            $hour->timerecord->lunch = $request->lunch;
            $hour->timerecord->save();

            return redirect("/time?date={$date->format('d.m.Y')}");
        }
    }

    // DELETE time/{id}
    public function destroy(Request $request, $id){
        $request->user()->authorizeRoles(['worker']);

        $hour = Hour::find($id);

        if($hour->timerecord->user == $request->user()){
            Hour::destroy($id);
        }

    }

    //-- helpers --//
    private function createHour($timerecord, $from, $to, $comment, $workTypeName){
        $hour = Hour::create([
            'from' => $from,
            'to' => $to,
            'comment' => $comment
        ]);
        $worktype = Worktype::where('name', $workTypeName)->first();

        $timerecord->hours()->save($hour);
        $worktype->hours()->save($hour); 
        return true;
    }

    private $dayNamesGerman = [
        'So',
        'Mo',
        'Di',
        'Mi',
        'Do',
        'Fr',
        'Sa'
    ];
}
