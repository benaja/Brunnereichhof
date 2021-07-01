<?php

namespace App\Http\Controllers;

use App\FamilyAllowance;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Mail\WorkerCreated;
use App\User;
use App\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET worker
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['worker_read', 'timerecord_stats']);

        $query = User::workers();

        if (isset($request->deleted)) {
            $workers = $query->onlyTrashed()->orderBy('lastname')->get();
        } elseif (isset($request->all)) {
            $workers = $query->withTrashed()->orderBy('lastname')->get();
        } else {
            $workers = $query->orderBy('lastname')->get();
        }

        return $workers;
    }

    // POST worker
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['worker_write']);

        $this->validate($request, [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:user',
            'role_id' => 'required|integer',
        ]);

        $username = Utils::getUniqueUsername($request->firstname.'.'.$request->lastname);

        $password = str_random(8);
        $usertype = UserType::where('name', 'worker')->first();
        $user = User::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'email' => request('email'),
            'username' => $username,
            'password' => Hash::make($password),
            'isPasswordChanged' => 0,
            'role_id' => $request->role_id,
            'isActive' => $request->isActive,
        ]);

        $usertype->users()->save($user);

        $familyAllowance = FamilyAllowance::create();
        $user->familyAllowance()->save($familyAllowance);

        $data['mail'] = $user->email;
        $data['password'] = $password;

        \Mail::to($user->email)->send(new WorkerCreated($data));

        return 'success';
    }

    // GET worker/{id}
    public function show(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['worker_read']);

        $worker = User::find($id);

        if (auth()->user()->hasRule(['family_allowance_read']) || auth()->user()->isAnyType(['superadmin'])) {
            $worker->load('familyAllowance');
        }

        $worker->workHoursThisMonth = $worker->totalHoursOfThisMonth();
        $worker->mealsThisMonth = $worker->getNumberOfMealsByMonth(new \DateTime('first day of this month'));

        $worker->workHoursLastMonth = $worker->totalHoursByMonth(new \DateTime('first day of last month'));
        $worker->mealsLastMonth = $worker->getNumberOfMealsByMonth(new \DateTime('first day of last month'));

        $worker->holidaysPlant = $worker->holydaysPlant(new \DateTime('now'));
        $worker->holidaysDone = $worker->holydaysDone(new \DateTime('now'));

        $worker->saldo = $worker->saldo();

        return $worker;
    }

    // PATCH worker/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['worker_write']);

        if (isset($request->deleted_at)) {
            $user = User::withTrashed()->find($id);
            $user->restore();

            return $user;
        }

        $user = User::find($id);
        foreach ($request->except('_token') as $key => $value) {
            $user->$key = $value;
        }

        $user->save();

        return $user;
    }

    // DELETE worker/{id}
    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['worker_write']);

        $worker = User::find($id);

        $worker->delete();
    }
}
