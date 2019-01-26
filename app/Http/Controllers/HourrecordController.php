<?php

namespace App\Http\Controllers;

use App\Hourrecord;
use App\Customer;
use App\Culture;
use Illuminate\Http\Request;

class HourrecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return Hourrecord::all();
    }

    // POST project
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
                array_splice($hourrecords, $index, 1);
            }
        }

        foreach ($request->weeks as $week) {
            if (count($hourrecords->where('week', $week['week'])) == 0) {
                $hourrecord = Hourrecord::create([
                    'hours' => 0,
                    'comment' => null,
                    'week' => $week['week'],
                    'year' => (new \DateTime)->format('Y'),
                    'customer_id' => auth()->user()->customer->id
                ]);
                array_push($hourrecords, $hourrecord);
            }
        }
        return $hourrecords;

    }

    // GET project/{id}
    public function show($id)
    {
        //
    }

    // GET project/{id}/edit
    public function edit($id)
    {
        foreach ($request->weeks as $week) {
            $culture = Culture::firstOrCreate(
                [
                    'name' => $week['culture']
                ],
                [
                    'isAutocomplete' => 0
                ]
            );
            $culture->save();

            $year = new \DateTime();
            $year = $year->format('Y');
            $hourrecord = Hourrecord::create([
                'hours' => $week['hours'],
                'comment' => $week['comment'],
                'week' => $week['week'],
                'year' => $year,
                'customer_id' => auth()->user()->customer->id,
                'culture_id' => $culture->id
            ]);
        }
    }

    // PATCH project/{id}
    public function update(Request $request, $id)
    {
        //
    }

    // DELETE project/{id}
    public function destroy($id)
    {
        //
    }
}
