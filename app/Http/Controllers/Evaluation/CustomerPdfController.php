<?php

namespace App\Http\Controllers\Evaluation;

use App\Helpers\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Customer;
use App\Rapportdetail;
use App\Project;
use App\Enums\FoodTypeEnum;

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

    public function weekRapport(Request $request, $week)
    {
        Pdf::validateToken($request->token);
        $date = new \DateTime($week);
        $this->pdf = new Pdf();

        if ($request->customer_id == 0) {
            $customers = Customer::all();
            $customersAdded = 0;
            foreach ($customers as $customer) {
                $monday = $date->modify('+1 day')->modify('last monday');
                $sunday = clone $monday;
                $sunday = $sunday->modify('next sunday');
                $hours = Rapportdetail::join('rapport', function ($join) use ($customer) {
                    $join->on('rapport.id', '=', 'rapportdetail.rapport_id')
                        ->where('rapport.customer_id', '=', $customer->id);
                })->where('date', '>=', $monday->format('Y-m-d'))
                    ->where('date', '<=', $sunday->format('Y-m-d'))->sum('hours');
                if ($hours > 0) {
                    if ($customersAdded > 0) {
                        $this->pdf->addPage();
                    }
                    $this->weekRapportForSingleCustomer($customer, $date);
                    $customersAdded++;
                }
            }
            $this->pdf->export("Wochenrapport Kunden KW {$date->format('W')}.pdf");
        } else {
            $customer = Customer::find($request->customer_id);
            $this->weekRapportForSingleCustomer($customer, $date);
            $this->pdf->export("Wochenrapport {$customer->lastname} {$customer->firstname} KW {$date->format('W')}.pdf");
        }
        $this->pdf->export('test.pdf');
    }

    private function weekRapportForSingleCustomer($customer, $date)
    {
        $monday = $date->modify('+1 day')->modify('last monday');
        $sunday = clone $monday;
        $sunday = $sunday->modify('next sunday');
        $this->pdf->documentTitle("KW: {$date->format('W')} / {$monday->format('d.m.Y')} - {$sunday->format('d.m.Y')}");
        $this->pdf->documentTitle("$customer->customer_number $customer->lastname $customer->firstname");

        $rappordetails = Rapportdetail::join('rapport', function ($join) use ($customer) {
            $join->on('rapport.id', '=', 'rapportdetail.rapport_id')
                ->where('rapport.customer_id', '=', $customer->id);
        })->where('date', '>=', $monday->format('Y-m-d'))
            ->where('date', '<=', $sunday->format('Y-m-d'));

        $totalHours = (clone $rappordetails)->sum('hours');
        $this->pdf->documentTitle("Stunden: $totalHours");

        $meals = (clone $rappordetails)->where('foodtype_id', '=', FoodTypeEnum::Customer)->count();
        $this->pdf->documentTitle("Verpflegungen durch Kunde: $meals");
        $this->pdf->newLine();

        $lines = [['Stunden'], ['Verpflegungen']];
        $currentDay = clone $monday;
        for ($i = 0; $i < 7; $i++) {
            $hours = (clone $rappordetails)->where('date', '=', $currentDay->format('Y-m-d'))->sum('hours');
            array_push($lines[0], $hours);
            $currentDay->modify('+1 day');
        }

        $currentDay = clone $monday;
        for ($i = 0; $i < 7; $i++) {
            $meals = (clone $rappordetails)->where('date', '=', $currentDay->format('Y-m-d'))
                ->where('foodtype_id', '=', FoodTypeEnum::Customer)->count();
            array_push($lines[1], $meals);
            $currentDay->modify('+1 day');
        }

        $titles = $this->dayNames;
        array_unshift($titles, 'Wochentag');
        $this->pdf->table($titles, $lines);
        $rappordetailsByProjects = (clone $rappordetails)->get()->groupBy('project_id')->toArray();
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
