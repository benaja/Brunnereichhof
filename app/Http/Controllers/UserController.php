<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Customer;
use Illuminate\Http\Request;
use App\Mail\CustomerCreated;
use App\Enums\UserTypeEnum;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // POST password/change 
    public function changePassword(Request $request)
    {
        $this->validate(request(), [
            'passwordOld' => 'required|string',
            'password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
            'email' => 'nullable|email|unique:user'
        ]);

        if (Hash::check(request('passwordOld'), Auth::user()->password)) {
            $user = User::find(Auth::user()->id);
            $user->password = Hash::make(request('password'));
            $user->isPasswordChanged = 1;
            if ($user->type_id == UserTypeEnum::Customer) {
                $user->customer->secret = null;
                $user->customer->save();
            }
            if ($request->email) {
                $user->email = $request->email;
            }
            $user->save();
            return response('success');
        } else {
            // $errors = new \Illuminate\Support\MessageBag();
            // $errors->add('password_old', 'Passwort stimmt nicht!');
            // return back()->withInput()->withErrors($errors);
            return response('password invalid', 400);
        }
    }

    public function resetPassword(User $user)
    {
        if ($user->type->name == 'worker') {
            auth()->user()->authorize(['superadmin'], ['worker_write']);
        } else {
            auth()->user()->authorize(['superadmin']);
        }

        $password = str_random(8);
        $secret = encrypt($password);

        $user->password = Hash::make($password);
        $user->isPasswordChanged = 0;
        $user->save();

        $data['mail'] = $user->email;
        $data['password'] = $password;

        $success = \Mail::to($user->email)->send(new CustomerCreated($data));
        return response('email send');
    }
}
