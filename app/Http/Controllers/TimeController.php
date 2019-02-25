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
use App\Enums\WorkTypeEnum;
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
        auth()->user()->authorizeRoles(['worker', 'admin']);

        $currentDate = new \DateTime($date);

        $timerecord = Timerecord::firstOrNew(
            ['date' => $currentDate->format('Y-m-d'), 'user_id' => auth()->user()->id]
        );

        $isMealDefault = auth()->user()->ismealdefault;
        $totalHours = $timerecord->totalHours();
        $timerecord->horus = $timerecord->hours->load('worktype');

        foreach ($timerecord->hours as $hour) {
            $hour->breakfast = $timerecord->breakfast;
            $hour->lunch = $timerecord->lunch;
            $hour->dinner = $timerecord->dinner;
        }

        return [$timerecord];
    }

    // GET time/week
    public function week($date)
    {
        auth()->user()->authorizeRoles(['worker', 'admin']);

        $date = new \DateTime($date);
        $date->modify('monday this week');

        $timerecords = [];
        for ($i = 0; $i < 7; $i++) {
            $timerecord = Timerecord::firstOrNew(
                ['date' => $date->format('Y-m-d'), 'user_id' => auth()->user()->id]
            );
            foreach ($timerecord->hours as $hour) {
                $hour = $hour->load('worktype');
                $hour->breakfast = $timerecord->breakfast;
                $hour->lunch = $timerecord->lunch;
                $hour->dinner = $timerecord->dinner;
            }

            array_push($timerecords, $timerecord);
            $date->modify('+1 day');
        }

        return $timerecords;
    }

    // POST time
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['worker', 'admin']);

        $date = new \DateTime($request->date);
        $timerecord = Timerecord::firstOrCreate(
            ['date' => $date->format('Y-m-d'), 'user_id' => auth()->user()->id]
        );

        $request->validate([
            'worktype' => 'required|string',
            'from' => 'required|before_or_equal:to',
            'to' => ['required', new ValidTime($request->from, $timerecord)],
            'hasBreak' => 'nullable|boolean',
            'breakfast' => 'boolean',
            'lunch' => 'boolean',
            'dinner' => 'boolean',
            'date' => 'required|date',
            'comment' => 'nullable|string'
        ]);

        if ($request->hasBreak) {
            $request->validate([
                'breakFrom' => 'required|after:from',
                'breakTo' => 'required|after:breakFrom|before:to'
            ]);
        }

        $timerecord->breakfast = $request->breakfast;
        $timerecord->lunch = $request->lunch;
        $timerecord->dinner = $request->dinner;
        $timerecord->save();

        if ($request->hasBreak) {
            if (!$this->createHour($timerecord, $request->from, $request->breakFrom, $request->comment, $request->worktype)) {
                return redirect("/time?date=" . $date->format('d.m.Y') . "&error=overlapping");
            }
            if (!$this->createHour($timerecord, $request->breakTo, $request->to, $request->comment, $request->worktype)) {
                return redirect("/time?date=" . $date->format('d.m.Y') . "&error=overlapping");
            }
        } else {
            if (!$this->createHour($timerecord, $request->from, $request->to, $request->comment, $request->worktype)) {
                abort(500, 'could not create hourrecord');
            }
        }
        auth()->user()->ismealdefault = $request->lunch;
        auth()->user()->save();
        auth()->user()->timerecords()->save($timerecord);

        return $this->getHoursWidthLunch($timerecord->id);
    }

    // PATCH time/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['worker', 'admin']);

        $hour = Hour::find($id);
        if ($hour->timerecord->user->id == auth()->user()->id) {
            $request->validate([
                'worktype' => 'required|string',
                'from' => 'required|before_or_equal:to',
                'to' => ['required', new ValidTime($request->from, $hour->timerecord, $hour->id)],
                'lunch' => 'nullable|boolean',
                'commnet' => 'nullable|string'
            ]);

            $date = new \DateTime($hour->timerecord->date);

            $hour->from = $request->from;
            $hour->to = $request->to;
            $hour->comment = $request->comment;

            $worktype = Worktype::where('name', $request->worktype)->first();
            $worktype->hours()->save($hour);

            $hour->save();

            $hour->timerecord->breakfast = $request->breakfast;
            $hour->timerecord->lunch = $request->lunch;
            $hour->timerecord->dinner = $request->dinner;
            $hour->timerecord->save();

            return $this->getHoursWidthLunch($hour->timerecord->id);
        } else {
            return response('access denied', 401);
        }
    }

    // DELETE time/{id}
    public function destroy(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['worker', 'admin']);

        $hour = Hour::find($id);
        $timerecordId = $hour->timerecord->id;

        if ($hour->timerecord->user->id == auth()->user()->id) {
            Hour::destroy($id);

            return $this->getHoursWidthLunch($timerecordId);
        } else {
            return response('access denied', 401);
        }
    }

    public function stats($date)
    {
        auth()->user()->authorizeRoles(['worker', 'admin']);

        if (strlen($date) == 7) {
            return [
                'week' => $this->getMonthStats($date)
            ];
        } else {
            return [
                'month' => $this->getMonthStats($date),
                'week' => $this->getWeekStats($date)
            ];
        }
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

    private function getHoursWidthLunch($id)
    {
        $timerecord = Timerecord::find($id);
        $timerecord->hours = $timerecord->hours->load('worktype');
        foreach ($timerecord->hours as $hour) {
            $hour->breakfast = $timerecord->breakfast;
            $hour->lunch = $timerecord->lunch;
            $hour->dinner = $timerecord->dinner;
        }
        return $timerecord->hours;
    }

    private function getWeekStats($date)
    {
        $monday = new \DateTime($date);
        $sunday = new \DateTime($date);
        $monday->modify('monday this week');
        $sunday->modify('sunday this week');

        $timerecords = auth()->user()->timerecords->where('date', '>=', $monday->format('Y-m-d'))
            ->where('date', '<=', $sunday->format('Y-m-d'));

        return $this->getTotalHours($timerecords);
    }

    private function getMonthStats($date)
    {
        $firstDayOfMonth = new \DateTime($date);
        $lastDayOfMonth = new \DateTime($date);
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth->modify('last day of this month');

        $timerecords = auth()->user()->timerecords->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'));

        return $this->getTotalHours($timerecords);
    }

    private function getTotalHours($timerecords)
    {
        $totalHours = 0;
        $holidayHours = 0;
        foreach ($timerecords as $timerecord) {
            $totalHours += $timerecord->totalHours();
            $hours = $timerecord->hours->where('worktype_id', WorkTypeEnum::Holydays);
            foreach ($hours as $hour) {
                $holidayHours += $hour->duration();
            }
        }

        return [
            'totalHours' => $totalHours,
            'holidayHours' => $holidayHours
        ];
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
