<?php

namespace App\Http\Controllers;

use DB;
use App\Culture;
use App\Customer;
use App\Hourrecord;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET evaluation
    public function index(Request $request){
        $request->user()->authorizeRoles(['admin']);

        $weeksData = Hourrecord::where('year', date('Y'))->orderBy('week')->get();

        $hoursPerWeek = Hourrecord::groupBy('week')->selectRaw('sum(hours) as sum, week')->pluck('sum', 'week', '0');
        $hoursPerWeek = current($hoursPerWeek);

        return view('pages.admin.evaluation', compact('weeksData', 'hoursPerWeek'));
    }

    // GET evaluation/weeks
    public function weeks(Request $request){
        $request->user()->authorizeRoles(['admin']);

        $hours = Hourrecord::groupBy('week')->selectRaw('sum(hours) as sum, week')->pluck('sum', 'week');

        return json_encode($hours);
    }

    // GET evaluation/week/{id}
    public function week(Request $request, $id){
        $request->user()->authorizeRoles(['admin']);

        $weeks = Hourrecord::where([['week', $id], ['year', date('Y')]])->get();

        return view('pages.admin.week', compact('weeks'));
    }
}
