<?php

namespace App\Http\Controllers;

use Fpdf;
use App\Rapport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class PdfController extends Controller
{

    // GET rapport/{id}/pdf
    public function rapportWeek(Request $request, Rapport $rapport)
    {
        $this->validateToken($request->token);

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf::SetFont('Arial', 'B', 16);
        Fpdf::AddPage('L');
        Fpdf::SetFont('Arial', 'B', 18);

        $header = [
            'Wochentag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
        ];

        $comments = ['Bemerkung', $rapport->comment_mo, $rapport->comment_tu, $rapport->comment_we, $rapport->comment_th, $rapport->comment_fr, $rapport->comment_sa];
        $rapportdetailsGruped = $rapport->rapportdetails->groupBy('employee_id');

        $totalTime = 0;
        foreach ($rapportdetailsGruped as $rapportdetails) {
            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $totalTime += $rapportdetail->hours;
                $counter++;
            }
        }

        $cellHeight = 8;

        Fpdf::SetFillColor(42, 168, 42);
        Fpdf::SetDrawColor(255);
        Fpdf::SetLineWidth(.3);
        Fpdf::AddFont('Raleway', '', 'Raleway-Regular.php');
        Fpdf::AddFont('Raleway', 'B', 'Raleway-Bold.php');
        // Fpdf::AddFont('Raleway','I', 'Raleway-Italic.php');

        Fpdf::SetFont('Raleway', 'B', 20);
        Fpdf::Cell(0, 15, utf8_decode("Kunde: {$rapport->customer->customer_number} {$rapport->customer->firstname} {$rapport->customer->lastname}"), 0, 2);
        Fpdf::SetFont('Raleway', '', 18);
        $startdate = new \DateTime($rapport->startdate);
        Fpdf::Cell(0, 12, "Zeitraum: {$startdate->format('Y')} / KW {$startdate->format('W')} ({$startdate->format('d.m.Y')} - {$startdate->modify('+6 day')->format('d.m.Y')})", 0, 2);
        Fpdf::Cell(0, 12, "Totale Arbeitsstunden: {$totalTime}");
        Fpdf::Ln();


        Fpdf::SetFont('Raleway', 'B', 12);
        Fpdf::SetTextColor(255);
        // Header
        $cellWidth = 40;
        for ($i = 0; $i < count($header); $i++) {
            Fpdf::Cell($cellWidth, $cellHeight, $header[$i], 0, 0, 'L', true);
        }
        Fpdf::Ln();
        // Color and font restoration
        Fpdf::SetFillColor(242, 242, 242);
        Fpdf::SetTextColor(0);
        Fpdf::SetFont('Raleway', '', 10);
        // Data
        $fill = false;
        $marginLeft = Fpdf::getX();
        $marginTop = Fpdf::getY();
        foreach ($comments as $comment) {
            Fpdf::SetXY($marginLeft, $marginTop);
            Fpdf::MultiCell(40, $cellHeight, utf8_decode($comment), 'L', $fill);
            $marginLeft += 40;
        }
        Fpdf::Ln();
        $fill = !$fill;


        $timePerDay = [0, 0, 0, 0, 0, 0];
        foreach ($rapportdetailsGruped as $rapportdetails) {
            Fpdf::cell(40, $cellHeight, utf8_decode("{$rapportdetails[0]->employee->firstname} {$rapportdetails[0]->employee->lastname}"), 0, 'R', 'L', $fill);

            $counter = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $timePerDay[$counter] += $rapportdetail->hours;
                $counter++;
                Fpdf::cell(40, $cellHeight, $rapportdetail->hours != null ? $rapportdetail->hours . " h" : "0" . " h", 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();


            Fpdf::cell(40, $cellHeight, "", 0, 'R', 'L', $fill);
            foreach ($rapportdetails as $rapportdetail) {
                Fpdf::cell(40, $cellHeight, utf8_decode($rapportdetail->project != null ? $rapportdetail->project->name : "keine Angabe"), 0, 'R', 'L', $fill);
            }
            Fpdf::Ln();
            $fill = !$fill;
        }
        // Closing line
        Fpdf::cell(40, $cellHeight, "Total Zeit", 0, 'R', 'L', $fill);
        foreach ($timePerDay as $time) {
            Fpdf::cell(40, $cellHeight, $time . " h", 0, 'R', 'L', $fill);
        }

        $filename = "pdf/{$rapport->customer->firstname}_{$rapport->customer->lastname}_{$startdate->modify('-6 day')->format('d-m-Y')}.pdf";
        Fpdf::Output('D', $filename);
    }

    private function validateToken($token)
    {
        if($token != Cache::pull('pdfToken')){
            abort(401, 'This action is unauthorized.');
        }
    }
}
