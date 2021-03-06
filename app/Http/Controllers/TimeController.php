<?php

namespace App\Http\Controllers;

use App\Enums\WorkTypeEnum;
use App\Hour;
use App\Rules\ValidTime;
use App\Timerecord;
use App\User;
use App\Worktype;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET time
    public function index(Request $request, $date)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_read']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;

        $currentDate = new \DateTime($date);

        $timerecord = Timerecord::firstOrNew(
            ['date' => $currentDate->format('Y-m-d'), 'user_id' => $userId]
        );

        $timerecord->horus = $timerecord->hours->load('worktype');

        foreach ($timerecord->hours as $hour) {
            $hour->breakfast = $timerecord->breakfast;
            $hour->lunch = $timerecord->lunch;
            $hour->dinner = $timerecord->dinner;
        }

        return [$timerecord];
    }

    // GET time/week
    public function week(Request $request, $date)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_read']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;

        $date = new \DateTime($date);
        $date->modify('monday this week');

        $timerecords = [];
        for ($i = 0; $i < 7; $i++) {
            $timerecord = Timerecord::firstOrNew(
                ['date' => $date->format('Y-m-d'), 'user_id' => $userId]
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
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_write']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;
        $this->isAllowedToEdit($userId);

        $date = new \DateTime($request->date);
        $timerecord = Timerecord::firstOrCreate(
            ['date' => $date->format('Y-m-d'), 'user_id' => $userId]
        );

        $request->validate([
            'worktype' => 'required|numeric',
            'from' => 'required|before_or_equal:to',
            'to' => ['required', new ValidTime($request->from, $timerecord)],
            'hasBreak' => 'nullable|boolean',
            'breakfast' => 'boolean',
            'lunch' => 'boolean',
            'dinner' => 'boolean',
            'date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        if ($request->hasBreak) {
            $request->validate([
                'breakFrom' => 'required|after:from',
                'breakTo' => 'required|after:breakFrom|before:to',
            ]);
        }

        $timerecord->breakfast = $request->breakfast;
        $timerecord->lunch = $request->lunch;
        $timerecord->dinner = $request->dinner;
        $timerecord->save();

        if (! $this->createHour($timerecord, $request->from, $request->to, $request->comment, $request->worktype)) {
            abort(500, 'could not create hourrecord');
        }

        $user = User::find($userId);
        $user->ismealdefault = $request->lunch;
        $user->save();
        $user->timerecords()->save($timerecord);

        return $this->getHoursWidthLunch($timerecord->id);
    }

    // PATCH time/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_write']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;
        $this->isAllowedToEdit($userId);

        $hour = Hour::find($id);
        if ($hour->timerecord->user->id == $userId) {
            $request->validate([
                'worktype' => 'required|numeric',
                'from' => 'required|before_or_equal:to',
                'to' => ['required', new ValidTime($request->from, $hour->timerecord, $hour->id)],
                'lunch' => 'nullable|boolean',
                'commnet' => 'nullable|string',
            ]);

            $hour->from = $request->from;
            $hour->to = $request->to;
            $hour->comment = $request->comment;

            $worktype = Worktype::find($request->worktype);
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
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_write']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;
        $this->isAllowedToEdit($userId);

        $hour = Hour::find($id);
        $timerecord = $hour->timerecord;

        if ($hour->timerecord->user->id == $userId) {
            Hour::destroy($id);

            if (count($timerecord->hours) === 0) {
                $timerecord->update([
                    'breakfast' => false,
                    'lunch' => false,
                    'dinner' => false,
                ]);
            }

            return $this->getHoursWidthLunch($timerecord->id);
        } else {
            return response('access denied', 401);
        }
    }

    public function stats(Request $request, $date)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'worker_read']);
        $userId = isset($request->workerId) ? $request->workerId : auth()->user()->id;

        if (strlen($date) == 7) {
            return [
                'week' => $this->getMonthStats($date, $userId),
            ];
        } else {
            return [
                'month' => $this->getMonthStats($date, $userId),
                'week' => $this->getWeekStats($date, $userId),
            ];
        }
    }

    //-- helpers --//
    private function createHour($timerecord, $from, $to, $comment, $workTypeId)
    {
        $hour = Hour::create([
            'from' => $from,
            'to' => $to,
            'comment' => $comment,
            'date' => $timerecord->date,
        ]);
        $worktype = Worktype::find($workTypeId);

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

    private function getWeekStats($date, $userId)
    {
        $monday = new \DateTime($date);
        $sunday = new \DateTime($date);
        $monday->modify('monday this week');
        $sunday->modify('sunday this week');

        $user = User::find($userId);
        $timerecords = $user->timerecords->where('date', '>=', $monday->format('Y-m-d'))
            ->where('date', '<=', $sunday->format('Y-m-d'));

        return $this->getTotalHours($timerecords);
    }

    private function getMonthStats($date, $userId)
    {
        $firstDayOfMonth = new \DateTime($date);
        $lastDayOfMonth = new \DateTime($date);
        $firstDayOfMonth->modify('first day of this month');
        $lastDayOfMonth->modify('last day of this month');

        $user = User::find($userId);
        $timerecords = $user->timerecords->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
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
            'holidayHours' => $holidayHours,
        ];
    }

    private function isAllowedToEdit($userId)
    {
        if (
            auth()->user()->hasRule(['timerecord_read_write']) &&
            ! auth()->user()->hasRule('worker_write') &&
            auth()->user()->id != $userId
        ) {
            abort(403, 'This action is forbidden.');
        }

        return true;
    }
}
