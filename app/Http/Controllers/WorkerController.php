<?php

namespace App\Http\Controllers;

use App\User;
use App\UserType;
use App\Mail\WorkerCreated;
use Illuminate\Http\Request;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

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

        $workers = User::where('type_id', UserTypeEnum::Worker)
            ->orderBy('lastname')
            ->get();

        foreach ($workers as $worker) {
            $worker->workHoursThisMonth = $worker->totalHoursOfThisMonth();
            $worker->mealsThisMonth = $worker->getNumberOfMeals(new \DateTime('first day of this month'));

            $worker->workHoursLastMonth = $worker->totalHours(new \DateTime('first day of last month'));
            $worker->mealsLastMonth = $worker->getNumberOfMeals(new \DateTime('first day of last month'));

            $worker->holidaysPlant = $worker->holydaysPlant(new \DateTime('now'));
            $worker->holidaysDone = $worker->holydaysDone(new \DateTime('now'));
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
            'email' => 'required|email|unique:user'
        ]);

        $username = strtolower($request->firstname) . "." . strtolower($request->lastname);

        if ($this->checkIfUsernameExist($username)) {
            $usernameIsUnique = false;
            $counter = 1;

            while (!$usernameIsUnique) {
                if ($this->checkIfUsernameExist($username . $counter)) {
                    $counter++;
                } else {
                    $username = $username . $counter;
                    $usernameIsUnique = true;
                }
            }
        }

        $password = str_random(8);
        $usertype = UserType::where('name', 'worker')->first();
        $user = User::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'email' => request('email'),
            'username' => $username,
            'password' => Hash::make($password),
            'isPasswordChanged' => 0
        ]);

        $usertype->users()->save($user);

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

        return $worker;
    }

    // PATCH worker/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['worker_write']);

        $user = User::find($id);

        $updatetKey = key($request->except('_token'));
        $updatedValue = $request->$updatetKey;

        $user->$updatetKey = $updatedValue;
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

    //-- helpers --//
    private function checkIfUsernameExist($username)
    {
        if (User::where('username', '=', $username)->count() > 0) {
            return true;
        }
        return false;
    }
}
