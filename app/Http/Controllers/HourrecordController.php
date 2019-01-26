<?php

namespace App\Http\Controllers;

use App\Hourrecord;
use App\Customer;
use App\Culture;
use Illuminate\Http\Request;
use App\Enums\AuthorizationType;

class HourrecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        if (auth()->user()->authorization_id == AuthorizationType::Customer) {
            return Hourrecord::with('culture')->where([
                'customer_id' => auth()->user()->customer->id,
                'year' => (new \DateTime())->format('Y')
            ])->get()->groupBy('week');
        } else {
            return Hourrecord::with('culture')
                ->where('year', (new \DateTime())->format('Y'))
                ->get()->groupBy('week');
        }
    }

    // POST project
    // create multiple at once
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        $hourrecords = auth()->user()->customer->hourrecords->where('year', (new \DateTime)->format('Y'));

        foreach ($hourrecords as $index => $hourrecord) {
            $weeks = array_filter(
                $request->weeks,
                function ($week) use ($hourrecord) {
                        // abort(400, json_encode($hourrecord));
                    return $week['week'] == $hourrecord->week;
                        // return true;
                }
            );
            if (count($weeks) == 0) {
                $hourrecord->delete();
            }
        }

        foreach ($request->weeks as $week) {
            if (count($hourrecords->where('week', $week['week'])) == 0) {
                $hourrecord = Hourrecord::create([
                    'hours' => null,
                    'comment' => null,
                    'week' => $week['week'],
                    'year' => (new \DateTime)->format('Y'),
                    'customer_id' => auth()->user()->customer->id
                ]);
            }
        }

        return Hourrecord::with('culture')->where([
            'customer_id' => auth()->user()->customer->id,
            'year' => (new \DateTime())->format('Y')
        ])->get()->groupBy('week');

    }

    public function createSingle(Request $request, $week)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        if ($week > 52) {
            abort(400, 'the week can not be greater than 52');
        }

        $hourrecord = Hourrecord::create([
            'hours' => $request->hours,
            'comment' => $request->comment,
            'week' => $week,
            'year' => (new \DateTime)->format('Y'),
            'customer_id' => auth()->user()->customer->id
        ]);
        return $hourrecord;
    }

    // GET project/{id}
    public function show($id)
    {
        //
    }

    // PATCH project/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        $hourrecrod = Hourrecord::find($id);
        $hourrecrod->hours = $request->hours;
        $hourrecrod->comment = $request->comment;

        if (is_array($request->culture)) {
            $culture = Culture::find($request->culture['id']);
        } else if ($request->culture) {
            $culture = Culture::firstOrCreate(
                [
                    'name' => $request->culture
                ],
                [
                    'isAutocomplete' => 0
                ]
            );
        } else {
            $culture = null;
        }
        $hourrecrod->culture()->associate($culture);
        $hourrecrod->save();

        return $hourrecrod;
    }

    // DELETE project/{id}
    public function destroy($id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        $hourrecrod = Hourrecord::find($id);
        $hourrecrod->delete();
    }
}
