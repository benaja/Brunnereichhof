<?php

namespace App\Http\Controllers;

use App\Hourrecord;
use App\Customer;
use App\Culture;
use Illuminate\Http\Request;
use App\Enums\UserTypeEnum;
use App\Helpers\Pdf;
use App\Settings;
use App\Project;

class HourrecordController extends Controller
{
    private $pdf = null;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_read']);

        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            return Hourrecord::with('culture')->where([
                'customer_id' => auth()->user()->customer->id,
                'year' => (new \DateTime())->format('Y')
            ])->get()->groupBy('week');
        } else {
            $date = new \DateTime($request->year);
            if (isset($request->sortBy) && $request->sortBy === 'customer') {
                return Customer::withTrashed()->with(array('Hourrecords' => function ($query) use ($date) {
                    $query->where('year', $date->format('Y'))->orderBy('week');
                }))->orderBy('lastname')->get();
            } else if (isset($request->sortBy) && $request->sortBy === 'project') {
                return Culture::withTrashed()->with(['hourrecords' => function ($query) use ($date) {
                    $query->where('year', $date->format('Y'));
                }])->orderBy('name')->get();
            }
            return Hourrecord::with('culture')
                ->where('year', $date->format('Y'))
                ->get()->groupBy('week');
        }
    }

    // POST project
    // create multiple at once
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        $this->validateEditeDate();

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
                    'createdByAdmin' => false,
                    'customer_id' => auth()->user()->customer->id
                ]);
            }
        }

        return Hourrecord::with('culture')->where([
            'customer_id' => auth()->user()->customer->id,
            'year' => (new \DateTime())->format('Y')
        ])->get()->groupBy('week');
    }

    public function createSingle(Request $request, $year, $week)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        $this->validateEditeDate();

        if ($week > 52) {
            abort(400, 'the week can not be greater than 52');
        }

        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            $customer = auth()->user()->customer;
        } else {
            $customer = Customer::find($request->customer);
        }

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

        $hourrecord = Hourrecord::create([
            'hours' => $request->hours,
            'comment' => $request->comment,
            'week' => $week,
            'year' => $year,
            'createdByAdmin' => !(auth()->user()->type_id == UserTypeEnum::Customer),
            'customer_id' => $customer->id
        ]);

        $this->addProjectToCustomer($culture, $hourrecord);
        $hourrecord->culture()->associate($culture);
        $hourrecord->save();
        $hourrecord->customer = $hourrecord->customer;

        return Hourrecord::with('customer')->with('culture')->find($hourrecord->id);
    }

    // PATCH hourrecord/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        $this->validateEditeDate();

        $hourrecord = Hourrecord::find($id);
        $hourrecord->hours = $request->hours;
        $hourrecord->comment = $request->comment;

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

        $this->addProjectToCustomer($culture, $hourrecord);

        $hourrecord->culture()->associate($culture);
        $hourrecord->save();

        return $hourrecord;
    }

    public function getByWeek($year, $week)
    {
        auth()->user()->authorize(['superadmin'], ['hourrecord_read']);

        return Customer::withTrashed()->with(['hourrecords' => function ($query) use ($year, $week) {
            $query->with('culture')->where([
                'year' => $year,
                'week' => $week
            ]);
        }])->get();
    }

    // DELETE hourrecord/{id}
    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            $this->validateEditeDate();
        }

        $hourrecrod = Hourrecord::find($id);
        $hourrecrod->delete();
    }

    // GET pdf/hourrecord/{year}/customer/{customer}
    public function hourrecordYearRappport($year, $customer)
    {
        auth()->user()->authorize(['superadmin'], ['hourrecord_read']);

        $year = new \DateTime($year);
        $this->pdf = new Pdf();
        if ($customer === 'all') {
            $customers = Customer::withTrashed()->with(['Hourrecords' => function ($query) use ($year) {
                $query->with('culture');
                $query->where('year', $year->format('Y'))->orderBy('week');
            }])->orderBy('lastname')->get();
            $addNewPage = false;
            foreach ($customers as $customer) {
                if ($customer->hourrecords->count() > 0) {
                    $this->customerYearRapport($customer, $year, $addNewPage);
                    $addNewPage = true;
                }
            }
            return $this->pdf->export("Stundenangaben {$year->format('Y')}.pdf");
        } else {
            $customer = Customer::with(['hourrecords' => function ($query) use ($year) {
                $query->where('year', $year->format('Y'))->orderBy('week');
            }])->find($customer);
            $this->customerYearRapport($customer, $year);
            return $this->pdf->export("Stundenangaben {$customer->lastname} {$customer->firstname} {$year->format('Y')}.pdf");
        }
    }


    // --helpers--
    private function isEditDate()
    {
        $startDate = new \DateTime(Settings::value('hourrecordStartDate'));
        $endDate = new \DateTime(Settings::value('hourrecordEndDate'));
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            if ($startDate > $today || $endDate < $today) {
                return false;
            }
        }
        return true;
    }

    private function validateEditeDate()
    {
        if (!$this->isEditDate()) {
            abort(400, 'the edit duration is over');
        }
    }

    private function addProjectToCustomer($culture, $hourrecord)
    {
        if ($culture != null) {
            $project = Project::firstOrCreate([
                'name' => $culture->name
            ]);
            if (!$hourrecord->customer->projects->find($project->id)) {
                $hourrecord->customer->projects()->save($project);
            }
        }
    }

    private function customerYearRapport($customer, $year, $addNewPage = false)
    {
        if ($addNewPage) $this->pdf->addNewPage();
        $totalHours = $customer->hourrecords->sum('hours');
        $title = "{$customer->lastname} {$customer->firstname}\nJahr: {$year->format('Y')}\nTotale Stunden: {$totalHours}";
        $this->pdf->documentTitle($title);
        $this->pdf->textToInsertOnPageBreak = $title;

        $lines = [];

        foreach ($customer->hourrecords as $hourrecord) {
            $date = new \DateTime();
            $date->setISODate(intval($year->format('Y')), $hourrecord->week);
            $weekStartDate = $date->format('Y-m-d');
            $date->modify('+6 days');
            $weekEndDate = $date->format('Y-m-d');
            array_push($lines, ["KW {$date->format('W')} ($weekStartDate - $weekEndDate)", $hourrecord->hours, $hourrecord->culture['name'], $hourrecord->comment]);
        }

        $headers = ['Woche (Datum)', 'Stunden', 'Kultur', 'Kommentar'];
        $this->pdf->table($headers, $lines);
    }
}
