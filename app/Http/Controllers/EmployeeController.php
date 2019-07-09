<?php

namespace App\Http\Controllers;

use Image;
use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $employees = [];
        if (isset($request->guests)) {
            $employees = Employee::where('isGuest', 1)->where('isDeleted', 0)->orderBy('lastname')->get();
        } else if (isset($request->all)) {
            $employees = Employee::orderBy('lastname')->get();
        } else {
            $employees = Employee::where('isGuest', 0)->where('isDeleted', 0)->orderBy('lastname')->get();
        }
        return $employees;
    }

    // POST employee
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        return $employee;
    }

    // PATCH employee/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
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
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
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

        $employee->update([
            'isDeleted' => true
        ]);
    }

    // GET employee/all
    public function all(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $request->user()->authorizeRoles(['admin']);

        $employees = Employee::where('isActive', 1)->get();

        return $employees;
    }

    public function employeeDayTotalsByYear(Request $request, $employeeId, $year)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf('P');
        $employee = Employee::find($employeeId);

        $year = new \DateTime($year);
        $firstDayOfMonth = clone $year;
        $firstDayOfMonth = $firstDayOfMonth->modify('first day of January this year');

        $isFirstPage = true;
        for ($i = 0; $i < 12; $i++) {
            $lastDayOfMonth = clone $firstDayOfMonth;
            $lastDayOfMonth->modify('last day of this month');

            $lines = $this->dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth);
            if (count($lines) > 0) {
                if (!$isFirstPage) $this->pdf->addPage();
                else $isFirstPage = false;

                $this->addDayTotalsTable($lines, $employee, $firstDayOfMonth);
            }
            $firstDayOfMonth->modify('first day of next month');
        }

        $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$year->format('Y')}.pdf");
    }

    public function employeeDayTotalsByMonth(Request $request, $employeeId, $month)
    {
        Pdf::validateToken($request->token);
        $this->pdf = new Pdf('P');
        $employee = Employee::find($employeeId);

        $month = new \DateTime($month);
        $firstDayOfMonth = $month->modify('first day of this month');
        $lastDayOfMonth = clone $month;
        $lastDayOfMonth->modify('last day of this month');

        $lines = $this->dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth);
        $this->addDayTotalsTable($lines, $employee, $firstDayOfMonth);
        $monthName = Settings::getMonthName($firstDayOfMonth);
        $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$monthName} {$firstDayOfMonth->format('Y')}.pdf");
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
                $customer = $rapportdetail->rapport->customer;
                array_push($cells, "{$customer->lastname} {$customer->firstname}");
                array_push($cells, $rapportdetail->project->name);
                $footType = "Eichhof";
                if ($rapportdetail->foodtype_id === FoodTypeEnum::Customer) $footType = "Kunde";
                if ($rapportdetail->foodtype_id === FoodTypeEnum::None) $footType = "Keine Angabe";
                array_push($cells, $footType);

                $hours = $rapportdetail->hours ? $rapportdetail->hours : 0;
                if (count($rapportdetails) > 1) {
                    array_push($cells, $hours);
                } else {
                    array_push($cells, "Total: $hours");
                }
                array_push($lines, $cells);
                $cells = [""];
                $totalHours += $rapportdetail->hours;
            }
            if (count($rapportdetails) > 1) {
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
        $this->pdf->table($titles, $lines);
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
