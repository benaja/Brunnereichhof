<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Enums\FoodTypeEnum;
use App\FamilyAllowance;
use App\Helpers\Pdf;
use App\Helpers\Settings;
use App\Helpers\Utils;
use App\Http\Resources\EmployeeResource;
use App\Rapportdetail;
use App\Reservation;
use App\Role;
use App\User;
use App\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Image;

class EmployeeController extends Controller
{
    private $pdf;

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET employee
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_preview_read', 'employee_read', 'roomdispositioner_read', 'evaluation_employee', 'resource_planner_write']);

        if ($request->get('paginate') === 'true') {
            $query = Employee::join('user', 'user.id', '=', 'employee.user_id')
                ->select('employee.*', 'user.lastname');

            if ($request->get('withGuests') !== 'true') {
                $query->where('employee.isGuest', false);
            }
            if ($request->get('witInactive') !== 'true') {
                $query->where('employee.isActive', false);
            }

            $employees = $query->orderBy('lastname')->paginate($request->get('per_page') ?? 10);

            return EmployeeResource::collection($employees);
        }

        $employees = [];
        if (isset($request->deleted)) {
            $employees = Employee::onlyTrashed();
        } elseif (isset($request->all)) {
            $employees = Employee::withTrashed();
        } else {
            $employees = Employee::query();
        }

        return $employees
            ->with('languages')
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    // GET guests
    public function guests(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_preview_read', 'employee_read', 'roomdispositioner_read', 'evaluation_employee']);

        $employees = [];
        if (isset($request->deleted)) {
            $employees = Employee::with('user')->onlyTrashed();
        } elseif (isset($request->all)) {
            $employees = Employee::with('user')->withTrashed();
        } else {
            $employees = Employee::with('user');
        }

        return $employees->where('isGuest', true)
            ->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    // GET employeeswithguests
    public function employeesWithGuests(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['roomdispositioner_read', 'evaluation_employee']);

        $employees = [];
        if (isset($request->deleted)) {
            $employees = Employee::with('user')->onlyTrashed();
        } elseif (isset($request->all)) {
            $employees = Employee::with('user')->withTrashed();
        } else {
            $employees = Employee::with('user');
        }

        return $employees->get()
            ->sortBy('user.lastname', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    // POST employee
    public function store(Request $request)
    {
        if ($request->isGuest) {
            auth()->user()->authorize(['superadmin'], ['employee_write', 'roomdispositioner_write']);
        } else {
            auth()->user()->authorize(['superadmin'], ['employee_write']);
        }
        $request['data'] = json_decode($request->data, true);
        $modifiedValidateArray = [];
        foreach ($this->validateArray as $key => $validate) {
            $modifiedValidateArray[$key] = "data.$validate";
        }
        $request->validate($modifiedValidateArray);
        $this->validate($request, [
            'data.email' => 'nullable|email|unique:user,email',
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request['data'];
            foreach (array_keys($this->validateArray) as $key) {
                if (array_key_exists($key, $data)) {
                    continue;
                }
                $data[$key] = null;
            }
            $employee = Employee::create([
                'callname' => $data['callname'],
                'nationality' => $data['nationality'],
                'isIntern' => $data['isIntern'],
                'isDriver' => $data['isDriver'],
                'sex' => $data['sex'],
                'comment' => $data['comment'],
                'experience' => $data['experience'],
                'isActive' => $data['isActive'],
                'isGuest' => $data['isGuest'],
                'allergy' => $data['allergy'],
                'isLoginActive' => $data['isLoginActive'] || false,
                'drivingLicence' => $data['drivingLicence'],
                'entryDate' => $data['entryDate'],
            ]);

            $user = User::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'username' => Utils::getUniqueUsername($data['firstname'].'.'.$data['lastname']),
                'email' => strtolower($data['email']),
                'password' => Hash::make(str_random(8)),
                'isPasswordChanged' => 0,
            ]);

            $familyAllowance = FamilyAllowance::create();

            $employee->familyAllowance()->save($familyAllowance);

            if (! $data['isLoginActive']) {
                $user->delete();
            }

            if (array_key_exists('role_id', $data['user'])) {
                $role = Role::find($data['user']['role_id']);
                $role->users()->save($user);
            }

            if ($request->file('profileimage')) {
                $this->handleProfileImage($employee->id, $request->file('profileimage'));
            }

            $employeeUserType = UserType::where('name', 'employee')->first();
            $user->employee()->save($employee);
            $employeeUserType->users()->save($user);

            return $employee->id;
        });
    }

    // GET employee/{id}
    public function show(Request $request, Employee $employee)
    {
        if ($employee->isGuest) {
            auth()->user()->authorize(['superadmin'], ['employee_read', 'roomdispositioner_read']);
        } else {
            auth()->user()->authorize(['superadmin'], ['employee_read']);
        }
        $employee->load('languages');

        if (auth()->user()->hasRule(['family_allowance_read']) || auth()->user()->isAnyType(['superadmin'])) {
            $employee->load('familyAllowance');
        }

        $employee->profileimage = $employee->getProfileimageUrl();
        $employee->saldo = $employee->transactions()->where('entered', false)->sum('amount');

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
            $employe->user->restore();

            return response('success');
        }

        $data = $this->validate($request, $this->validateArray);

        $employee = Employee::find($id);

        $doesEmailExist = User::where('id', '!=', $employee->user_id)
            ->where('email', strtolower($request->email))
            ->first();
        if ($doesEmailExist) {
            return response('Email already exist', 400);
        }

        DB::transaction(function () use ($request, $employee, $data) {
            $employee->update($data);
            $employee->save();

            $employee->languages()->sync($data['languages']);

            if ($request->isLoginActive && $employee->user->deleted_at) {
                $employee->user->restore();
            } elseif (! $request->isLoginActive) {
                $employee->user->delete();
            }

            if ($request->user['role_id']) {
                $role = Role::find($request->user['role_id']);
                $role->users()->save($employee->user);
            }

            $employee->user->update([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => strtolower($request->email),
            ]);
            $employee->user->save();
        });
    }

    // POST employee/{id}/editimage
    public function uploadImage(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        if ($request->image != null) {
            return $this->handleProfileImage($id, $request->file('image'));
        } else {
            return response('no image', 404);
        }
    }

    public function deleteImage(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        $employee = Employee::find($id);
        Storage::disk('s3')->delete($employee->profileimage);
        Storage::disk('s3')->delete("small/{$employee->profileimage}");

        $employee->profileimage = null;
        $employee->save();
    }

    // DELETE employee/{id}
    public function destroy(Request $request, Employee $employee)
    {
        auth()->user()->authorize(['superadmin'], ['employee_write']);

        if ($employee->profileimage != null) {
            $imagePath = public_path('profileimages/').$employee->profileimage;
            $smallImagePath = public_path('profileimages/').'small-'.$employee->profileimage;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (file_exists($smallImagePath)) {
                unlink($smallImagePath);
            }
        }

        $employee->user->delete();
        $employee->delete();
    }

    // GET employee/all
    public function all(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['employee_read']);

        $employees = Employee::where('isActive', 1)->get();

        return $employees;
    }

    // GET pdf/day-total/employees/{employeeId}
    public function dayTotalsPdf(Request $request, $employeeId)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);

        $employee = Employee::find($employeeId);
        if (! $employee) {
            abort(400, 'Employee not found');
        }

        $this->pdf = new Pdf('P');
        $originalDate = Utils::firstDate($request->dateRangeType, new \DateTime($request->date));
        $firstDayOfMonth = clone $originalDate;
        $lastDayOfMonth = Utils::lastDate($request->dateRangeType, new \DateTime($request->date));

        $monthsAdded = 0;
        for ($i = 0; $i < 12; $i++) {
            $lines = $this->dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth);
            if (count($lines) > 0 || $request->dateRangeType === 'month') {
                if ($monthsAdded > 0) {
                    $this->pdf->addNewPage();
                }
                $monthsAdded++;

                $this->addDayTotalsTable($lines, $employee, $firstDayOfMonth);
            }
            if ($request->dateRangeType === 'month') {
                $monthName = Settings::getMonthName($firstDayOfMonth);

                return $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$monthName} {$firstDayOfMonth->format('Y')}.pdf");
            }
            $firstDayOfMonth->modify('first day of next month');
            $lastDayOfMonth->modify('last day of next month');
        }
        if ($monthsAdded == 0) {
            abort(400, 'Employee has no entries');
        }

        return $this->pdf->export("Tagestotale $employee->lastname $employee->firstname {$originalDate->format('Y')}.pdf");
    }

    public function reservationsPdf(Request $request, $employeeId)
    {
        auth()->user()->authorize(['superadmin'], ['evaluation_employee']);
        $this->pdf = new Pdf();

        if ($employeeId === 'all') {
            $firstDayOfYear = Utils::firstDate('year', new \DateTime($request->date));
            $lastDayOfYear = Utils::lastDate('year', new \DateTime($request->date));

            $reservationsThisYear = Reservation::where('entry', '<=', $lastDayOfYear->format('Y-m-d'))
                ->where('exit', '>=', $firstDayOfYear->format('Y-m-d'))
                ->orderBy('entry')
                ->get();
            $sleepOver = Reservation::getSleepOver($reservationsThisYear, $firstDayOfYear, $lastDayOfYear);

            $this->pdf->documentTitle("Übernachtungen im Jahr {$firstDayOfYear->format('Y')}");
            $this->pdf->documentTitle("Totale Übernachtungen: $sleepOver");

            $employees = Employee::withTrashed()->get();
            foreach ($employees as $employee) {
                $this->reservationsByYearSingleEmployee($employee, $firstDayOfYear, false);
            }

            return $this->pdf->export("Übernachtungen im Jahr {$firstDayOfYear->format('Y')}.pdf");
        } else {
            $employee = Employee::find($employeeId);
            $date = new \DateTime($request->date);
            $this->reservationsByYearSingleEmployee($employee, $date);

            return $this->pdf->export("Übernachtungen von {$employee->name()} {$date->format('Y')}.pdf");
        }
    }

    private function reservationsByYearSingleEmployee($employee, $date, $addWhenEmpty = true)
    {
        $firstDayOfYear = Utils::firstDate('year', $date);
        $lastDayOfYear = Utils::lastDate('year', $date);

        $reservationsThisYear = $employee->reservationsBetweenDates($firstDayOfYear, $lastDayOfYear);
        $sleepOver = Reservation::getSleepOver($reservationsThisYear, $firstDayOfYear, $lastDayOfYear);
        if ($sleepOver > 0 || $addWhenEmpty) {
            if (! $addWhenEmpty) {
                $this->pdf->addNewPage();
            }
            $this->pdf->documentTitle("Übernachtungen von {$employee->name()}");
            $this->pdf->documentTitle("Jahr: {$firstDayOfYear->format('Y')}");
            $this->pdf->documentTitle("Totale Übernachtungen: $sleepOver");
            $this->reservationsPdfTable($reservationsThisYear);

            if ($addWhenEmpty) {
                $firstDayOfMonth = clone $firstDayOfYear;
                for ($i = 0; $i < 12; $i++) {
                    $lastDayOfThisMonth = clone $firstDayOfMonth;
                    $lastDayOfThisMonth->modify('last day of this month');
                    $reservationsThisMonth = $employee->reservationsBetweenDates($firstDayOfMonth, $lastDayOfThisMonth);
                    $sleepOver = Reservation::getSleepOver($reservationsThisMonth, $firstDayOfMonth, $lastDayOfThisMonth);
                    if ($sleepOver > 0) {
                        $this->pdf->addNewPage();
                        $monthName = Settings::getMonthName($firstDayOfMonth);
                        $this->pdf->documentTitle("Übernachtungen von {$employee->name()}");
                        $this->pdf->documentTitle("{$monthName} {$firstDayOfMonth->format('Y')}");
                        $this->pdf->documentTitle("Totale Übernachtungen: $sleepOver");
                        $this->reservationsPdfTable($reservationsThisMonth);
                    }
                    $firstDayOfMonth->modify('first day of next month');
                }
            }
        }
    }

    private function reservationsPdfTable($reservations)
    {
        $this->pdf->newLine();
        $headers = ['Eintritt', 'Austritt', 'Raum', 'Bett'];
        $columns = [];
        foreach ($reservations as $reservation) {
            array_push($columns, [
                (new \DateTime($reservation->entry))->format('d.m.Y'),
                (new \DateTime($reservation->exit))->format('d.m.Y'),
                $reservation->bedRoomPivot->room->name,
                $reservation->bedRoomPivot->bed->name,
            ]);
        }
        $this->pdf->table($headers, $columns);
    }

    private function dayTotalsByMonthTable($employee, $firstDayOfMonth, $lastDayOfMonth)
    {
        $rapportdetailsByDay = Rapportdetail::where('employee_id', $employee->id)
            ->where('date', '>=', $firstDayOfMonth->format('Y-m-d'))
            ->where('date', '<=', $lastDayOfMonth->format('Y-m-d'))
            ->where('hours', '>', 0)
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

                    $projectName = $rapportdetail->project ? $rapportdetail->project->name : '';
                    array_push($cells, $projectName);

                    $footType = 'Eichhof';
                    if ($rapportdetail->foodtype_id === FoodTypeEnum::Customer) {
                        $footType = 'Kunde';
                    }
                    if ($rapportdetail->foodtype_id === FoodTypeEnum::None) {
                        $footType = 'Keine Angabe';
                    }
                    array_push($cells, $footType);

                    if (count($rapportdetails) > 1) {
                        array_push($cells, $hours);
                    } else {
                        array_push($cells, $hours);
                    }
                    array_push($lines, $cells);
                    $cells = [''];
                    $totalHours += $hours;
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

    private function handleProfileImage($employeeId, $file)
    {
        $employee = Employee::find($employeeId);

        $img = Image::make($file);
        $width = $img->width();
        $height = $img->height();
        $factor = $width / $height;
        $img->resize(150 * $factor, 150);

        $imagePath = Storage::disk('s3')->putFile('profileimages', $file);
        Storage::disk('s3')->put('small/'.$imagePath, (string) $img->stream());
        $employee->profileimage = $imagePath;
        $employee->save();

        return $employee->getProfileImageUrl();
    }

    private $validateArray = [
        'firstname' => 'required|string|max:100',
        'lastname' => 'required|string|max:100',
        'callname' => 'nullable|max:100|string',
        'email' => 'nullable|email',
        'sex' => 'required',
        'comment' => 'nullable|string|max:500',
        'experience' => 'nullable|string|max:100',
        'allergy' => 'nullable|string|max:100',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'isGuest' => 'boolean',
        'isLoginActive' => 'boolean',
        'drivingLicence' => 'boolean|nullable',
        'entryDate' => 'nullable|date',
        'nationality' => 'nullable|string',
        'isIntern' => 'nullable|boolean',
        'isDriver' => 'nullable|boolean',
        'isActive' => 'boolean',
        'languages' => 'nullable|array',
        'function' => 'nullable|string',
        'resource_planner_white_listed' => 'nullable|boolean',
    ];
}
