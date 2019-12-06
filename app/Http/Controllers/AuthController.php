<?php

namespace App\Http\Controllers;

use Auth;
use JWTAuth;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = new User;
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->save();
        return response([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

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
