<?php

namespace App\Http\Controllers\Evaluation;

use App\Helpers\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Customer;
use App\Rapportdetail;
use App\Project;
use App\Enums\FoodTypeEnum;
use App\Rapport;

class CustomerPdfController extends Controller
{
    private $pdf;
    private $monthNames = [
        "Januar", "Februar", "März", "April", "Mai", "Juni",
        "Juli", "August", "September", "Oktober", "November", "Dezember"
    ];
    private $dayNames = [
        'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'
    ];

    // GET /rapport/{id}/pdf
    public function weekRapportByRapportId(Request $request, Rapport $rapport)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf();
        $this->weekRapportForSingleCustomer($rapport);
        $date = new \DateTime($rapport->startdate);
        $this->pdf->export("Wochenrapport {$rapport->customer->lastname} {$rapport->customer->firstname} KW {$date->format('W')}.pdf");
    }

    // GET /pdf/customer/week/{date}
    public function weekRapport(Request $request, $date)
    {
        Pdf::validateToken($request->token);
        $date = new \DateTime($date);
        $monday = $date->modify('+1 day')->modify('last monday');
        $this->pdf = new Pdf();

        if ($request->customer_id == 0) {
            $customers = Customer::all();
            $customersAdded = 0;
            foreach ($customers as $customer) {
                $rapport = $customer->rapports->where('startdate', $monday->format('Y-m-d'))->first();
                if ($rapport) {
                    $hours = $rapport->rapportdetails->sum('hours');
                    if ($hours > 0) {
                        if ($customersAdded > 0) {
                            $this->pdf->addPage();
                        }
                        $this->weekRapportForSingleCustomer($rapport);
                        $customersAdded++;
                    }
                }
            }
            if ($customersAdded == 0) {
                $this->pdf->documentTitle("Keine Einträge vorhanden in dieser Woche.");
            }
            $this->pdf->export("Wochenrapport Kunden KW {$date->format('W')}.pdf");
        } else {
            $customer = Customer::find($request->customer_id);
            $rapport = $customer->rapports->where('startdate', $monday->format('Y-m-d'))->first();
            if ($rapport) {
                $this->weekRapportForSingleCustomer($rapport);
            } else {
                $this->pdf->documentTitle("Keine Enträge vorhanden für {$customer->lastname} {$customer->firstname} in der Woche {$date->format('W')}.");
            }
            $this->pdf->export("Wochenrapport {$customer->lastname} {$customer->firstname} KW {$date->format('W')}.pdf");
        }
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

        $totalHours = (clone $rapport->rapportdetails)->sum('hours');
        $this->pdf->documentTitle("Stunden: $totalHours");

        $meals = (clone $rapport->rapportdetails)->where('foodtype_id', '=', FoodTypeEnum::Customer)->count();
        $this->pdf->documentTitle("Verpflegungen durch Kunde: $meals");
        if ($rapport->customer->needs_payment_order) {
            $this->pdf->documentTitle("Einzahlungsschein erwünscht");
        }
        $this->pdf->newLine();

        $timePerDay = ['Totale Stunden', 0, 0, 0, 0, 0, 0];
        $lines = [$comments];
        $rapportdetailsGruped = $rapport->rapportdetails->groupBy('employee_id');

        $currentDay = clone $monday;
        $mealsPerDay = ['Verpflegungen'];
        for ($i = 0; $i < 6; $i++) {
            $meals = (clone $rapport->rapportdetails)->where('date', '=', $currentDay->format('Y-m-d'))
                ->where('foodtype_id', '=', FoodTypeEnum::Customer)->count();
            array_push($mealsPerDay, $meals);
            $currentDay->modify('+1 day');
        }
        array_push($lines, $mealsPerDay);

        foreach ($rapportdetailsGruped as $rapportdetails) {
            $cells = [$rapportdetails[0]->employee->name()];

            $counter = 1;
            foreach ($rapportdetails as $rapportdetail) {
                $cell = $rapportdetail->hours ? $rapportdetail->hours : 0;
                // $cell = $hasNonCommonProject ? $cell . "\n" : $cell;
                if ($rapportdetail->project && $rapportdetail->project->name != "Allgemein") {
                    $cell = "{$cell} ({$rapportdetail->project->name})";
                }
                array_push($cells, $cell);
                $timePerDay[$counter] += $rapportdetail->hours;
                $counter++;
            }
            array_push($lines, $cells);
        }

        array_push($lines, $timePerDay);

        $this->pdf->table($header, $lines, [], ['lastLineBold' => true, 'lineBreakEnabledOnLines' => [0]]);

        $rappordetailsByProjects = (clone $rapport->rapportdetails)->groupBy('project_id')->toArray();
        $rappordetailsByProjects = array_keys($rappordetailsByProjects);
        $projects = [];
        foreach ($rappordetailsByProjects as $projectId) {
            $project = Project::find($projectId);
            if ($project && !in_array($project->name, $projects)) {
                array_push($projects, $project->name);
            }
        }

        $this->pdf->documentTitle("Projekte: " . implode(", ", $projects));
    }
}
