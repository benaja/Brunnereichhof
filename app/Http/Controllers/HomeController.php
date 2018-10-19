<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Customer;
use Illuminate\Http\Request;
use App\Enums\AuthorizationType;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET /
    public function index(Request $request)
    {

        $request->user()->authorizeRoles(['customer', 'admin', 'worker']);

        $user = Auth::user();

        if($user->authorization->name == "admin"){
            return redirect("/home");
        }
        if($user->authorization->name == "worker"){
            return redirect("/time");
        }
        if($user->isPasswordChanged == 0){
            return redirect('/profile/edit?initialChange=true');
        }
        return view('pages.customer.index');
    }

    // GET home
    public function home(Request $request){

        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.frontpage');
    }

    // GET profile/edit
    public function editProfil(Request $request){
        $request->user()->authorizeRoles(['customer', 'admin', 'worker']);

        $initialChange = false;
        if(isset($request->initialChange) && $request->initialChange){
            $initialChange = true;
        }

        $authorization = $request->user()->authorization->name;

        return view('pages.change-password', compact("initialChange", "authorization"));
       
    }

    // POST password/change 
    public function changePassword(Request $request){
        $this->validate(request(), [
            'password_old' => 'required|string',
            'password' => ['required','string','min:8','max:100','confirmed','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/']
        ]);
        
        if(Hash::check(request('password_old'), Auth::user()->password)){
            $user = User::find(Auth::user()->id);
            $user->password = Hash::make(request('password'));
            $user->save();
            
            if($user->authorization_id == AuthorizationType::Worker){
                $user->isPasswordChanged = 1;
                $user->save();
            }else if($user->authorization_id == AuthorizationType::Customer){
                $user->customer->secret = null;
                $user->isPasswordChanged = 1;
                $user->customer->save();
                $user->save();
            }
            return redirect('/password/changed');
        }else{
            $errors = new \Illuminate\Support\MessageBag();
            $errors->add('password_old', 'Passwort stimmt nicht!');
            return back()->withInput()->withErrors($errors);
        }
    }

    // GET password/changed
    public function passwordChanged(){
        $authorization = request()->user()->authorization->name;

        return view('pages.customer.password-changed', compact('authorization'));
    }
}
