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
use Carbon\Carbon;

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
                ->get()
                ->groupBy('week');
        }
    }

    // POST project
    // create multiple at once
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        $this->validateEditeDate();

        $hourrecords = auth()->user()->customer->hourrecords()->where('year', Carbon::now()->format('Y'))->get();

        foreach ($hourrecords as $hourrecord) {
            if (!in_array($hourrecord->week, $request->get('weeks', []))) {
                $hourrecord->delete();
            }
        }

        foreach ($request->weeks as $week) {
            if (count($hourrecords->where('week', $week)) === 0) {
                $hourrecord = Hourrecord::create([
                    'hours' => null,
                    'comment' => null,
                    'week' => $week,
                    'year' => Carbon::now()->format('Y'),
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
        $year = new \DateTime($year);

        $customer = null;
        if (auth()->user()->type_id == UserTypeEnum::Customer) {
            $customer = auth()->user()->customer;
        } else {
            $customer = Customer::find($request->customerId);
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
            'year' => $year->format('Y'),
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

        return $this->updateHourrecordById($id, $request);
    }

    // PATCH /hourrecord
    public function updateMultible(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['hourrecord_write']);
        $this->validateEditeDate();

        $hourrecords = json_decode($request->getContent(), true);
        foreach ($hourrecords as $hourrecord) {
            $this->updateHourrecordById($hourrecord['id'], $hourrecord);
        }
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
            $customers = Customer::withTrashed()->orderBy('lastname')->get();
            $addNewPage = false;
            $totalHourrecords = 0;
            foreach ($customers as $customer) {
                $hourrecords = $customer->hourrecords->where('year', $year->format('Y'));
                if ($hourrecords->count() > 0) {
                    $totalHourrecords += $hourrecords->sum('hours');
                    $this->customerYearRapport($customer, $year, $addNewPage);
                    $addNewPage = true;
                }
            }
            if ($totalHourrecords === 0) {
                abort(400, 'No hourrecords for selected time');
            }
            return $this->pdf->export("Stundenangaben {$year->format('Y')}.pdf");
        } else {
            $customer = Customer::with('hourrecords')->find($customer);
            $this->customerYearRapport($customer, $year);
            return $this->pdf->export("Stundenangaben {$customer->lastname} {$customer->firstname} {$year->format('Y')}.pdf");
        }
    }

    public function getByCustomer(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['hourrecord_read']);

        return Hourrecord::with('culture')->where([
            'customer_id' => $id,
            'year' => (new \DateTime($request->year))->format('Y')
        ])->get()->groupBy('week');
    }

    // GET /pdf/hourrecord?week=23?year=2020
    public function pdfByWeek(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['hourrecord_read']);

        $this->validate($request, [
            'week' => 'required|string',
            'year' => 'required|string'
        ]);
        $date = new \DateTime();
        $date->setISODate($request->year, $request->week);
        $this->pdf = new Pdf();
        $startOfWeek = (clone $date)->modify('monday this week');
        $endOfWeek = (clone $date)->modify('sunday this week');
        $headline = "Stundenangaben KW {$date->format('W')} ({$startOfWeek->format('d.m.Y')} - {$endOfWeek->format('d.m.Y')})";
        $this->pdf->textToInsertOnPageBreak = $headline;
        $hourrecords = Hourrecord::where('week', $request->week)->where('year', $request->year);

        $this->pdf->documentTitle($headline);
        $this->pdf->documentTitle("Stunden: {$hourrecords->sum('hours')}");
        $this->pdf->newLine();

        $headers = ['Kunde', 'Stunden', 'Kultur', 'Bemerkung'];
        $hourrecords = $hourrecords->get()->sortBy('customer.lastname', SORT_NATURAL | SORT_FLAG_CASE);
        $columns = [];
        foreach ($hourrecords as $hourrecord) {
            array_push($columns, [
                "{$hourrecord->customer->lastname} {$hourrecord->customer->firstname}",
                $hourrecord->hours,
                $hourrecord->culture ? $hourrecord->culture->name : '',
                $hourrecord->comment
            ]);
        }
        $this->pdf->table($headers, $columns, [1, 0.5, 1, 1.5]);

        return $this->pdf->export("{$headline}.pdf");
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
        $hourrecords = $customer->hourrecords->where('year', $year->format('Y'))->sortBy('week');
        $totalHours = $hourrecords->sum('hours');
        $title = "{$customer->lastname} {$customer->firstname}\nJahr: {$year->format('Y')}\nTotale Stunden: {$totalHours}";
        $this->pdf->documentTitle($title);
        $this->pdf->textToInsertOnPageBreak = $title;

        $lines = [];

        foreach ($hourrecords as $hourrecord) {
            $date = new \DateTime();
            $date->setISODate(intval($year->format('Y')), $hourrecord->week);
            $weekStartDate = $date->format('d.m.Y');
            $date->modify('+6 days');
            $weekEndDate = $date->format('d.m.Y');
            array_push($lines, ["KW {$date->format('W')} ($weekStartDate - $weekEndDate)", $hourrecord->hours, $hourrecord->culture['name'], $hourrecord->comment]);
        }

        $headers = ['Woche (Datum)', 'Stunden', 'Kultur', 'Kommentar'];
        $this->pdf->table($headers, $lines);
    }

    private function updateHourrecordById($id, $hourrecord)
    {
        $hourrecordInstance = Hourrecord::find($id);
        $hourrecordInstance->hours = $hourrecord['hours'];
        $hourrecordInstance->comment = $hourrecord['comment'];

        if (is_array($hourrecord['culture'])) {
            $culture = Culture::find($hourrecord['culture']['id']);
        } else {
            $culture = Culture::firstOrCreate(
                [
                    'name' => $hourrecord['culture']
                ],
                [
                    'isAutocomplete' => 0
                ]
            );
        }

        $this->addProjectToCustomer($culture, $hourrecordInstance);
        $hourrecordInstance->culture()->associate($culture);
        $hourrecordInstance->save();

        return $hourrecordInstance;
    }
}
