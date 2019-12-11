<?php

namespace App\Http\Controllers;

use Image;
use App\Employee;
use Illuminate\Http\Request;
use App\Rapportdetail;
use App\Enums\FoodTypeEnum;
use App\Helpers\Pdf;
use App\Helpers\Settings;

class EmployeeController extends Controller
{
    private $pdf;
    // GET employee
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_preview_read', 'employee_read', 'roomdispositioner_read', 'evaluation_employee']);

        $employees = [];
        if (isset($request->deleted)) $employees = Employee::onlyTrashed()->where('isGuest', false)->orderBy('lastname')->get();
        else if (isset($request->all)) $employees = Employee::withTrashed()->where('isGuest', false)->orderBy('lastname')->get();
        else $employees = Employee::where('isGuest', false)->orderBy('lastname')->get();
        return $employees;
    }

    // GET guests
    public function guests(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_preview_read', 'employee_read', 'roomdispositioner_read', 'evaluation_employee']);

        $employees = [];
        if (isset($request->deleted)) $employees = Employee::onlyTrashed()->where('isGuest', true)->orderBy('lastname')->get();
        else if (isset($request->all)) $employees = Employee::withTrashed()->where('isGuest', true)->orderBy('lastname')->get();
        else $employees = Employee::where('isGuest', true)->orderBy('lastname')->get();
        return $employees;
    }

    // GET employeeswithguests
    public function employeesWithGuests(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read', 'evaluation_employee']);

        if (isset($request->deleted)) $employees = Employee::onlyTrashed()->orderBy('lastname')->get();
        else if (isset($request->all)) $employees = Employee::withTrashed()->orderBy('lastname')->get();
        else $employees = Employee::orderBy('lastname')->get();
        return $employees;
    }

    // POST employee
    public function store(Request $request)
    {
        if ($request->isGuest) {
            auth()->user()->authorize(['superadmin'], ['employee_write', 'roomdispositioner_write']);
        } else {
            auth()->user()->authorize(['superadmin'], ['employee_write']);
        }
        $this->validate(request(), $this->validateArray);

        $employee = Employee::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'callname' => $request->callname,
            'nationality' => $request->nationality,
            'isIntern' => $request->isIntern,
            'isDriver' => $request->isDriver,
            'german_knowledge' => $request->german_knowledge,
            'english_knowledge' => $request->english_knowledge,
            'sex' => $request->sex,
            'comment' => $request->comment,
            'experience' => $request->experience,
            'isActive' => 1,
            'isGuest' => $request->isGuest,
            'allergy' => $request->allergy
        ]);

        return $employee->id;
    }

    // GET employee/{id}
    public function show(Request $request, Employee $employee)
    {
        if ($employee->isGuest) {
            auth()->user()->authorize(['superadmin'], ['employee_read', 'roomdispositioner_read']);
        } else {
            auth()->user()->authorize(['superadmin'], ['employee_read']);
        }
        return $employee;
    }

    // PATCH employee/{id}
    public function update(Request $request, $id)
    {
        if ($request->isGuest) {
            auth()->user()->authorize(['superadmin'], ['employee_write', 'roomdispositioner_write']);
        } else {
            auth()->user()->authorize(['superadmin'], ['employee_write']);
        }
        if (isset($request->deleted_at)) {
            $employe = Employee::withTrashed()->find($id);
            $employe->restore();
            return response('success');
        }

        $this->validate($request, $this->validateArray);

        $employe = Employee::find($id);

        $employe->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'callname' => $request->callname,
            'nationality' => $request->nationality,
            'isIntern' => $request->isIntern,
            'isDriver' => $request->isDriver,
            'german_knowledge' => $request->german_knowledge,
            'english_knowledge' => $request->english_knowledge,
            'sex' => $request->sex,
            'comment' => $request->comment,
            'experience' => $request->experience,
            'isActive' => $request->isActive,
            'isGuest' => $request->isGuest,
            'allergy' => $request->allergy
        ]);
        $employe->save();
    }

    // POST employee/{id}/editimage
    public function uploadImage(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        if ($request->profileimage != null) {
            $employee = Employee::find($id);
            $photoName = uniqid() . '.' . $request->profileimage->getClientOriginalExtension();
            $request->profileimage->move(public_path('profileimages'), $photoName);

            $img = Image::make(public_path('profileimages/') . $photoName);
            $width = $img->width();
            $height = $img->height();

            $factor = $width / $height;

            $img->resize(150 * $factor, 150);

            $img->save(public_path('profileimages/') . "small-$photoName");

            $request->element = "profileimage";
            $request->data = $photoName;
            if ($employee->profileimage != null) {
                $imagePath = public_path('profileimages/') . $employee->profileimage;
                $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                if (file_exists($smallImagePath)) {
                    unlink($smallImagePath);
                }
            }
            $employee->profileimage = $photoName;
            $employee->save();
            return response($photoName);
        } else {
            return response('no image', 404);
        }
    }

    public function deleteImage(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        $employee = Employee::find($id);

        $imagePath = public_path('profileimages/') . $employee->profileimage;
        $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        if (file_exists($smallImagePath)) {
            unlink($smallImagePath);
        }
        $employee->profileimage = null;
        $employee->save();
    }

    // DELETE employee/{id}
    public function destroy(Request $request, Employee $employee)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        if ($employee->profileimage != null) {
            $imagePath = public_path('profileimages/') . $employee->profileimage;
            $smallImagePath = public_path('profileimages/') . "small-" . $employee->profileimage;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (file_exists($smallImagePath)) {
                unlink($smallImagePath);
            }
        }

        $employee->delete();
    }

    // GET employee/all
    public function all(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_read']);


        $employees = Employee::where('isActive', 1)->get();

        return $employees;
    }

    public function employeeDayTotalsByYear(Request $request, $employeeId, $year)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf('P');
        $employee = Employee::find($employeeId);

        if (!$employee) {
            $this->pdf->error("Mitarbeiter konne nicht gefunden werden");
            return;
        }

        $year = new \DateTime($year);
        $firstDayOfMonth = clone $year;
        $firstDayOfMonth = $firstDayOfMonth->modify('first day of January this year');

        $monthsAdded = 0;
        for ($i = 0; $i < 12; $i++) {
            $lastDayOfMonth = clone $firstDayOfMonth;
            $lastDayOfMonth->modify('last day of this month');

            $lines = $this->dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth);
            if (count($lines) > 0) {
                if ($monthsAdded > 0) $this->pdf->addPage();
                $monthsAdded++;

                $this->addDayTotalsTable($lines, $employee, $firstDayOfMonth);
            }
            $firstDayOfMonth->modify('first day of next month');
        }
        if ($monthsAdded == 0) {
            $this->pdf->documentTitle("Keine Daten gefunden für dieses Jahr und diesen Mitarbeiter.");
        }

        $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$year->format('Y')}.pdf");
    }

    public function employeeDayTotalsByMonth(Request $request, $employeeId, $month)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf('P');
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $this->pdf->error("Mitarbeiter konne nicht gefunden werden");
            return;
        }

        $month = new \DateTime($month);
        $firstDayOfMonth = $month->modify('first day of this month');
        $lastDayOfMonth = clone $month;
        $lastDayOfMonth->modify('last day of this month');

        $lines = $this->dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth);
        $this->addDayTotalsTable($lines, $employee, $firstDayOfMonth);
        $monthName = Settings::getMonthName($firstDayOfMonth);
        $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$monthName} {$firstDayOfMonth->format('Y')}.pdf");
    }

    public function reservationsByYear(Request $request, $employeeId, $date)
    {
        $this->pdf = new Pdf();

        $employee = Employee::find($employeeId);
        $firstDayOfYear = new \DateTime($date);
        $firstDayOfYear->modify('first day of january this year');
        $lastDayOfYear = clone $firstDayOfYear;
        $lastDayOfYear->modify('last day of december this year');
        $reservationsThisYear = $employee->reservationsBetweenDates($firstDayOfYear, $lastDayOfYear);
        $sleepOver = $this->getSleepOver($reservationsThisYear, $firstDayOfYear, $lastDayOfYear);
        $this->pdf->documentTitle("Übernachtungen von {$employee->name()}");
        $this->pdf->documentTitle("Jahr: {$firstDayOfYear->format('Y')}");
        $this->pdf->documentTitle("Totale Übernachtungen: $sleepOver");
        $this->reservationsPdfTable($reservationsThisYear);

        $firstDayOfMonth = clone $firstDayOfYear;
        for ($i = 0; $i < 12; $i++) {
            $lastDayOfThisMonth = clone $firstDayOfMonth;
            $lastDayOfThisMonth->modify('last day of this month');
            $reservationsThisMonth = $employee->reservationsBetweenDates($firstDayOfMonth, $lastDayOfThisMonth);
            $sleepOver = $this->getSleepOver($reservationsThisMonth, $firstDayOfMonth, $lastDayOfThisMonth);
            if ($sleepOver > 0) {
                $this->pdf->addPage();
                $monthName = Settings::getMonthName($firstDayOfMonth);
                $this->pdf->documentTitle("Übernachtungen von {$employee->name()}");
                $this->pdf->documentTitle("{$monthName} {$firstDayOfMonth->format('Y')}");
                $this->pdf->documentTitle("Totale Übernachtungen: $sleepOver");
                $this->reservationsPdfTable($reservationsThisMonth);
            }
            $firstDayOfMonth->modify('first day of next month');
        }
        $this->pdf->export("Übernachtungen von {$employee->name()} {$firstDayOfYear->format('Y')}.pdf");
    }

    private function getSleepOver($reservations, $firstDay, $lastDay)
    {
        $sleepOver = 0;
        foreach ($reservations as $reservation) {
            $sleepDays = $reservation->days();
            if ($firstDay > $reservation->entry) {
                $sleepDays -= $firstDay->diff($reservation->entry)->days;
            }
            if ($lastDay < $reservation->exit) {
                $sleepDays -= $lastDay->diff($reservation->exit)->days - 1;
            }
            $sleepOver += $sleepDays;
        }
        return $sleepOver;
    }

    private function reservationsPdfTable($reservations)
    {
        $this->pdf->newLine();
        $headers = ['Eintritt', 'Austritt', 'Bett'];
        $columns = [];
        foreach ($reservations as $reservation) {
            array_push($columns, [
                (new \DateTime($reservation->entry))->format('d.m.Y'),
                (new \DateTime($reservation->exit))->format('d.m.Y'),
                $reservation->bedRoomPivot->room->name
            ]);
        }
        $this->pdf->table($headers, $columns);
    }

    private function dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth)
    {
        $rapportdetailsByDay = Rapportdetail::where('employee_id', $employee->id)
            ->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $lines = [];
        foreach ($rapportdetailsByDay as $rapportdetails) {
            $date = new \DateTime($rapportdetails[0]->date);
            $cells = [$date->format('d.m.Y')];
            $totalHours = 0;
            foreach ($rapportdetails as $rapportdetail) {
                $hours = $rapportdetail->hours ? $rapportdetail->hours : 0;
                if ($hours > 0) {
                    $customer = $rapportdetail->rapport->customer;
                    array_push($cells, "{$customer->lastname} {$customer->firstname}");

                    $projectName = $rapportdetail->project ? $rapportdetail->project->name : "";
                    array_push($cells, $projectName);

                    $footType = "Eichhof";
                    if ($rapportdetail->foodtype_id === FoodTypeEnum::Customer) $footType = "Kunde";
                    if ($rapportdetail->foodtype_id === FoodTypeEnum::None) $footType = "Keine Angabe";
                    array_push($cells, $footType);

                    if (count($rapportdetails) > 1) {
                        array_push($cells, $hours);
                    } else {
                        array_push($cells, "Total: $hours");
                    }
                    array_push($lines, $cells);
                    $cells = [""];
                    $totalHours += $rapportdetail->hours;
                }
            }
            if (count($rapportdetails) > 1 && $totalHours > 0) {
                array_push($lines, ['', '', '', '', "Total: $totalHours"]);
            }
        }

        return $lines;
    }

    private function addDayTotalsTable($lines, $employee, $date)
    {
        $titles = ['Datum', 'Kunde', 'Projekt', 'Verpflegung', 'Stunden'];

        $this->pdf->documentTitle("Tagestotale für $employee->lastname $employee->firstname");
        $monthName = Settings::getMonthName($date);
        $this->pdf->documentTitle("{$monthName} {$date->format('Y')}");

        $this->pdf->newLine();
        $this->pdf->table($titles, $lines, [0.7, 1.5, 1, 1, 0.8]);
        return true;
    }

    // Helpers
    private $validateArray = [
        'firstname' => 'required|string|max:100',
        'lastname' => 'required|string|max:100',
        'callname' => 'nullable|max:100|string',
        'sex' => 'required',
        'comment' => 'nullable|string|max:500',
        'experience' => 'nullable|string|max:100',
        'allergy' => 'nullable|string|max:100',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'isGuest' => 'boolean'
    ];
}
