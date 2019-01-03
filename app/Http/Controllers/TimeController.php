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
        $this->middleware('jwt.auth');
    }

    // GET time
    public function index($date)
    {
        auth()->user()->authorizeRoles(['worker']);

        $currentDate = new \DateTime($date);

        $settings = Settings::first();
        $timerecord = Timerecord::firstOrNew(
            ['date' => $currentDate->format('Y-m-d')],
            ['user_id' => auth()->user()->id]
        );

        $isMealDefault = auth()->user()->ismealdefault;
        $totalHours = $timerecord->totalHours();

        foreach ($timerecord->hours as $hour) {
            $hour->lunch = $timerecord->lunch;
        }

        return $timerecord->hours;
    }

    // POST time
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['worker']);

        $date = new \DateTime($request->date);
        $timerecord = Timerecord::firstOrCreate(
            ['date' => $date->format('Y-m-d')],
            ['user_id' => auth()->user()->id]
        );

        $request->validate([
            'workType' => 'required|string',
            'from' => 'required|before:to',
            'to' => ['required', new ValidTime($request->from, $timerecord)],
            'hasBreak' => 'nullable|boolean',
            'lunch' => 'nullable|boolean',
            'date' => 'required|date',
            'comment' => 'nullable|string'
        ]);

        if ($request->hasBreak) {
            $request->validate([
                'breakFrom' => 'required|after:from',
                'breakTo' => 'required|after:breakFrom|before:to'
            ]);
        }

        $timerecord->lunch = $request->lunch;
        $timerecord->save();

        if ($request->hasBreak) {
            if (!$this->createHour($timerecord, $request->from, $request->breakFrom, $request->comment, $request->workType)) {
                return redirect("/time?date=" . $date->format('d.m.Y') . "&error=overlapping");
            }
            if (!$this->createHour($timerecord, $request->breakTo, $request->to, $request->comment, $request->workType)) {
                return redirect("/time?date=" . $date->format('d.m.Y') . "&error=overlapping");
            }
        } else {
            if (!$this->createHour($timerecord, $request->from, $request->to, $request->comment, $request->workType)) {
                return redirect("/time?date=" . $date->format('d.m.Y') . "&error=overlapping");
            }
        }
        auth()->user()->ismealdefault = $request->lunch;
        auth()->user()->save();
        auth()->user()->timerecords()->save($timerecord);

        $timerecord = Timerecord::find($timerecord->id);
        foreach ($timerecord->hours as $hour) {
            $hour->lunch = $timerecord->lunch;
        }

        return $timerecord->hours;
    }

    // PATCH time/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['worker']);

        $hour = Hour::find($id);
        if ($hour->timerecord->user == $request->user()) {
            $request->validate([
                'workType' => 'required|integer',
                'from' => 'required|before:to',
                'to' => ['required', new ValidTime($request->from, $hour->timerecord, $hour->id)],
                'lunch' => 'nullable|boolean',
                'commnet' => 'nullable|string'
            ]);

            $date = new \DateTime($hour->timerecord->date);

            $hour->from = $request->from;
            $hour->to = $request->to;
            $hour->comment = $request->comment;

            $worktype = Worktype::find($request->workType);
            $worktype->hours()->save($hour);

            $hour->save();

            $hour->timerecord->lunch = $request->lunch;
            $hour->timerecord->save();

            $timerecord = Timerecord::find($hour->timerecord->id);
            foreach ($timerecord->hours as $hour) {
                $hour->lunch = $timerecord->lunch;
            }

            return $timerecord->hours;
        }
    }

    // DELETE time/{id}
    public function destroy(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['worker']);

        $hour = Hour::find($id);
        $timerecordId = $hour->timerecord->id;

        if ($hour->timerecord->user == $request->user()) {
            Hour::destroy($id);
        }

        $timerecord = Timerecord::find($timerecordId);
        foreach ($timerecord->hours as $hour) {
            $hour->lunch = $timerecord->lunch;
        }

        return $timerecord->hours;
    }

    //-- helpers --//
    private function createHour($timerecord, $from, $to, $comment, $workTypeName)
    {
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
