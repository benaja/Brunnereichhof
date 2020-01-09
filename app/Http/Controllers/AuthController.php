<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
use Auth;
use JWTAuth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $user = new User;
    //     $user->email = $request->email;
    //     $user->name = $request->name;
    //     $user->password = bcrypt($request->password);
    //     $user->save();
    //     return response([
    //         'status' => 'success',
    //         'data' => $user
    //     ], 200);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            $credentials = [
                'username' => $credentials['email'],
                'password' => $credentials['password']
            ];
            if (!$token = JWTAuth::attempt($credentials)) {
                return response([
                    'status' => 'error',
                    'error' => 'invalid.credentials',
                    'msg' => 'Invalid Credentials.'
                ], 400);
            }
        }
        return response([
            'status' => 'success'
        ])->header('Authorization', $token);
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response('Email does not exist.', 400);
        }

        $token = str_random(64);
        $data = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'token' => $token,
            'userId' => $user->id
        ];
        \Mail::to($user->email)->send(new ResetPassword($data));
        $user->passwordResetToken = Hash::make($token);
        $user->save();
    }

    public function setPassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6',
            'token' => 'required',
            'userId' => 'required'
        ]);

        $user = User::find($request->userId);
        if (!$user || !Hash::check($request->token, $user->passwordResetToken)) {
            return response('Token is invalid', 400);
        }
        $user->password = Hash::make($request->password);
        $user->passwordResetToken = null;
        $user->isPasswordChanged = 1;
        if ($user->type_id == UserTypeEnum::Customer) {
            $user->customer->secret = null;
            $user->customer->save();
        }
        $user->save();

        return ['email' => $user->email];
    }

    public function user(Request $request)
    {
        $user = User::with('role.authorizationRules')->with('type')->find(Auth::user()->id);
        if (!$user->isActive) {
            JWTAuth::invalidate();
            return response('Your account has been deactivated', 403);
        }
        return response([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function refresh()
    {
        return response([
            'status' => 'success'
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate();
        return response([
            'status' => 'success',
            'msg' => 'Logged out Successfully.'
        ], 200);
    }

    public function generatePdfToken()
    {
        $token = str_random(32);
        Cache::put('pdfToken', $token, 5);

        return $token;
    }
}
