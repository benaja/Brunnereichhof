<?php

namespace App\Http\Controllers\Evaluation;

use App\Customer;
use App\Enums\FoodTypeEnum;
use App\Exports\CustomerExport;
use App\Helpers\Pdf;
use App\Http\Controllers\Controller;
use App\Project;
use App\Rapport;
use App\Rapportdetail;
use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerPdfController extends Controller
{
    private $pdf;
    private $rapportFoodTypeEnabled = false;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET /rapport/{id}/pdf
    public function weekRapportByRapportId(Request $request, Rapport $rapport)
    {
        auth()->user()->authorize(['superadmin', 'customer'], ['rapport_read']);

        if (auth()->user()->isType(['customer']) && $rapport->customer_id !== auth()->user()->customer->id) {
            abort(403, 'This action is forbidden.');
        }

        $this->rapportFoodTypeEnabled = Settings::value('rapportFoodTypeEnabled');
        $this->pdf = new Pdf();
        $this->weekRapportForSingleCustomer($rapport);
        $date = new \DateTime($rapport->startdate);

        return $this->pdf->export("Wochenrapport {$rapport->customer->lastname} {$rapport->customer->firstname} KW {$date->format('W')}.pdf");
    }

    // GET /pdf/customer/week/{date}
    public function weekRapport(Request $request, $customerId, $date)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_customer']);

        $this->rapportFoodTypeEnabled = Settings::value('rapportFoodTypeEnabled');
        $date = new \DateTime($date);
        $monday = $date->modify('+1 day')->modify('last monday');
        $this->pdf = new Pdf();

        if ($customerId == 'all') {
            $customers = Customer::with(['rapports' => function ($query) use ($monday) {
                $query->where('startdate', $monday->format('Y-m-d'));
            }])->get();
            $customersAdded = 0;
            foreach ($customers as $customer) {
                $rapport = $customer->rapports->first();
                if ($rapport) {
                    $hours = $rapport->rapportdetails->sum('hours');
                    if ($hours > 0) {
                        if ($customersAdded > 0) {
                            $this->pdf->addNewPage();
                        }
                        $this->weekRapportForSingleCustomer($rapport);
                        $customersAdded++;
                    }
                }
            }
            if ($customersAdded == 0) {
                $this->pdf->documentTitle('Keine Einträge vorhanden in dieser Woche.');
            }

            return $this->pdf->export("Wochenrapport Kunden KW {$date->format('W')}.pdf");
        } else {
            $customer = Customer::find($customerId);
            $rapport = $customer->rapports->where('startdate', $monday->format('Y-m-d'))->first();
            if ($rapport) {
                $this->weekRapportForSingleCustomer($rapport);
            } else {
                $this->pdf->documentTitle("Keine Enträge vorhanden für {$customer->lastname} {$customer->firstname} in der Woche {$date->format('W')}.");
            }

            return $this->pdf->export("Wochenrapport {$customer->lastname} {$customer->firstname} KW {$date->format('W')}.pdf");
        }
    }

    // GET export/customers
    public function csvExport(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_customer']);
        $fileName = 'Kundenverzeichnis.csv';
        $file = storage_path().'/app/'.$fileName;
        Excel::store(new CustomerExport, $fileName, null, \Maatwebsite\Excel\Excel::CSV);

        return response()->download($file, $fileName, [
            'Pragma' => $fileName,
        ])->deleteFileAfterSend();
    }

    // GET pdf/customer/{customer}/year/{year}
    public function customerYearRapport(Request $request, $customerId, $year)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_customer']);

        $this->rapportFoodTypeEnabled = Settings::value('rapportFoodTypeEnabled');
        $year = (new \DateTime($year))->format('Y');

        $this->pdf = new Pdf('P');
        if ($customerId == 'all') {
            $customers = Customer::all();
            $isNotFirstPage = false;
            foreach ($customers as $customer) {
                $pageAdded = $this->singleCustomerYearRapport($customer, $year, true, $isNotFirstPage);
                if ($pageAdded) {
                    $isNotFirstPage = true;
                }
            }

            return $this->pdf->export("Jahresrapport $year.pdf");
        } else {
            $customer = Customer::find($customerId);
            $this->singleCustomerYearRapport($customer, $year);

            return $this->pdf->export("Jahresrapport $year {$customer->firstname} {$customer->lastname}.pdf");
        }
    }

    private function singleCustomerYearRapport($customer, $year, $onlyWhenNotZeroHours = false, $isNotFirstPage = false)
    {
        $firstDate = new \DateTime("1.1.$year");
        $lastDate = new \DateTime("31.12.$year");

        $rapportIds = $customer->rapports->map(function ($item) {
            return $item->id;
        });

        $totalHours = Rapportdetail::whereIn('rapport_id', $rapportIds)
            ->where('date', '>=', $firstDate->format('Y-m-d'))
            ->where('date', '<=', $lastDate->format('Y-m-d'))
            ->sum('hours');

        if ($onlyWhenNotZeroHours && $totalHours === 0) {
            return false;
        }

        $mondayThisWeek = clone $firstDate;
        $mondayThisWeek->modify('monday this week');
        $rapportsByWeek = $customer->rapports
            ->where('startdate', '>=', $mondayThisWeek->format('Y-m-d'))
            ->where('startdate', '<=', $lastDate->format('Y-m-d'))
            ->sortBy('startdate');

        if ($isNotFirstPage) {
            $this->pdf->addNewPage();
        }

        $this->pdf->documentTitle("Kunde: {$customer->firstname} {$customer->lastname}");
        $this->pdf->documentTitle("Jahr: $year");
        $this->pdf->documentTitle('Totale Arbeitsstunden: '.$totalHours.'h');

        foreach ($customer->projects as $project) {
            $hoursByProject = $project->rapportdetails->whereIn('rapport_id', $rapportIds)
                ->where('date', '>=', $firstDate->format('Y-m-d'))
                ->where('date', '<=', $lastDate->format('Y-m-d'))
                ->sum('hours');
            if ($hoursByProject > 0) {
                $this->pdf->documentTitle("{$project->name}: {$hoursByProject}h");
            }
        }
        $this->pdf->newLine();

        $titles = ['Kalenderwoche', 'Arbeitszeit Total'];
        $lines = [];
        foreach ($rapportsByWeek as $week) {
            $startDate = new \DateTime($week->startdate);
            $endDate = clone $startDate;
            $endDate->modify('+6 days');
            if ($startDate->format('Y') != $year) {
                $startDate->modify('first day of january');
                $startDate->modify('+1 year');
            }
            if ($endDate->format('Y') != $year) {
                $endDate->modify('last day of december');
                $endDate->modify('-1 year');
            }
            $hours = $week->rapportdetails->where('date', '>=', $startDate->format('Y-m-d'))
                ->where('date', '<=', $endDate->format('Y-m-d'))->sum('hours');
            array_push($lines, [
                "KW {$startDate->format('W')} ({$startDate->format('d.m.Y')}-{$endDate->format('d.m.Y')})",
                $hours.'h',
            ]);
        }
        $this->pdf->table($titles, $lines);

        foreach ($rapportsByWeek as $rapport) {
            $hours = $rapport->rapportdetails->sum('hours');
            if ($hours > 0) {
                $this->pdf->landscape = 'L';
                $this->pdf->addNewPage();
                $this->weekRapportForSingleCustomer($rapport);
            }
        }

        return true;
    }

    private function weekRapportForSingleCustomer($rapport)
    {
        $this->pdf->textToInsertOnPageBreak = "{$rapport->customer->customer_number} {$rapport->customer->lastname} {$rapport->customer->firstname}";
        $monday = new \DateTime($rapport->startdate);
        $sunday = clone $monday;
        $sunday = $sunday->modify('next sunday');
        $this->pdf->documentTitle("KW: {$monday->format('W')} / {$monday->format('d.m.Y')} - {$sunday->format('d.m.Y')}");
        $this->pdf->documentTitle("{$rapport->customer->customer_number} {$rapport->customer->lastname} {$rapport->customer->firstname}");
        $header = ['Wochentag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        $comments = ['Bemerkung', $rapport->comment_mo, $rapport->comment_tu, $rapport->comment_we, $rapport->comment_th, $rapport->comment_fr, $rapport->comment_sa];

        $totalHours = $rapport->rapportdetails->sum('hours');
        $this->pdf->documentTitle("Stunden: {$totalHours}");

        $staffGrantHours = $rapport->rapportdetails->where('contract_type', 'staff_grant')->sum('hours');

        if ($staffGrantHours > 0) {
            $workContractHours = $rapport->rapportdetails->where('contract_type', 'work_contract')->sum('hours');
            $this->pdf->documentTitle("Werksvertrag: {$workContractHours}, Personalverlei: {$staffGrantHours}");
        }

        if ($this->rapportFoodTypeEnabled) {
            $meals = $rapport->rapportdetails->where('foodtype_id', '=', FoodTypeEnum::Customer)->count();
            $this->pdf->documentTitle("Verpflegungen durch Kunde: $meals");
        }
        if ($rapport->customer->needs_payment_order) {
            $this->pdf->documentTitle('Einzahlungsschein erwünscht');
        }
        if ($rapport->customer->differingBillingAddress) {
            $this->pdf->newLine();
            $billingAddress = $rapport->customer->billingAddress;
            $this->pdf->documentTitle('Rechnungsadresse', 0, 'B');
            $documentTitle = "{$billingAddress->street}";
            if ($billingAddress->addition) {
                $documentTitle .= ", {$billingAddress->addition}";
            }
            $documentTitle .= "\n{$billingAddress->plz} {$billingAddress->place}";
            $this->pdf->documentTitle($documentTitle);
        }
        $this->pdf->newLine();

        $timePerDay = ['Totale Stunden', 0, 0, 0, 0, 0, 0];
        $lines = [$comments];
        $rapportdetailsGruped = $rapport->rapportdetails->groupBy('employee_id');

        if ($this->rapportFoodTypeEnabled) {
            $currentDay = clone $monday;
            $mealsPerDay = ['Verpflegungen'];
            for ($i = 0; $i < 6; $i++) {
                $meals = $rapport->rapportdetails->where('date', '=', $currentDay->format('Y-m-d'))
                    ->where('foodtype_id', '=', FoodTypeEnum::Customer)
                    ->where('hours', '>', 0)
                    ->count();

                array_push($mealsPerDay, $meals);
                $currentDay->modify('+1 day');
            }
            array_push($lines, $mealsPerDay);
        }

        foreach ($rapportdetailsGruped as $rapportdetails) {
            $cells = [$rapportdetails[0]->employee->name()];

            for ($i = 0; $i < 6; $i++) {
                $rapportdetail = $rapportdetails->where('day', $i)->first();

                if (! $rapportdetail) {
                    array_push($cells, 0);
                } else {
                    $cell = $rapportdetail->hours ? $rapportdetail->hours : 0;

                    if ($rapportdetail->project && $rapportdetail->project->name != 'Allgemein' && $cell > 0) {
                        $cell = "{$cell} ({$rapportdetail->project->name})";
                    }
                    array_push($cells, $cell);
                    $timePerDay[$i + 1] += $rapportdetail->hours;
                }
            }
            array_push($lines, $cells);
        }

        array_push($lines, $timePerDay);

        $this->pdf->table($header, $lines, [], ['lastLineBold' => true, 'lineBreakEnabledOnLines' => [0]]);

        $rappordetailsByProjects = $rapport->rapportdetails->groupBy('project_id')->toArray();
        $rappordetailsByProjects = array_keys($rappordetailsByProjects);

        $projects = Project::whereIn('id', $rappordetailsByProjects)->pluck('name');
        $this->pdf->documentTitle('Projekte: '.$projects->implode(', '));
    }
}
