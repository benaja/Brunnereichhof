<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
// use Auth;
use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login', 'resetPassword', 'setPassword']]);
    }

    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            $credentials = [
                'username' => $credentials['email'],
                'password' => $credentials['password'],
            ];
            if (! $token = auth()->attempt($credentials)) {
                return response([
                    'status' => 'error',
                    'error' => 'invalid.credentials',
                    'msg' => 'Invalid Credentials.',
                ], 401);
            }
        }

        return $this->respondWithToken($token);
    }

    public function refresh()
    {
        return auth()->tokenById(auth()->user()->id);
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response('Email does not exist.', 400);
        }

        $token = str_random(64);
        $data = [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'token' => $token,
            'userId' => $user->id,
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
            'userId' => 'required',
        ]);

        $user = User::find($request->userId);
        if (! $user || ! Hash::check($request->token, $user->passwordResetToken)) {
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
        $user = User::with(['role.authorizationRules', 'customer'])->with('type')->find(auth()->user()->id);
        if (! $user->isActive) {
            auth()->invalidate();

            return response('Your account has been deactivated', 403);
        }

        return response([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    protected function respondWithToken($token)
    {
        return response([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate();

        return response([
            'status' => 'success',
            // return response()->json(['error' => 'Unauthorized'], 401);
            'msg' => 'Logged out Successfully.',
        ], 200);
    }
}
