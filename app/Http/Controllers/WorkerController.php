<?php

namespace App\Http\Controllers;

use App\User;
use App\Authorization;
use App\Mail\WorkerCreated;
use Illuminate\Http\Request;
use App\Enums\AuthorizationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET worker
    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $workers = User::where('authorization_id', AuthorizationType::Worker)->get();

        return view('pages.admin.worker.index', compact('workers'));
    }

    // GET worker/all
    public function all(Request $request){
        $request->user()->authorizeRoles(['admin']);

        $workers = User::where('authorization_id', AuthorizationType::Worker)->get();

        return $workers;
    }

    // GET worker/create
    public function create(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.worker.create');
    }

    // POST worker
    public function store(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $this->validate($request, [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:user',
            'username' => 'required|string|max:100'
        ]);

        $username = request('username');

        if($this->checkIfUsernameExist(request('username'))){
            $usernameIsUnique = false;
            $counter = 1;
            
            while(!$usernameIsUnique){
                if($this->checkIfUsernameExist(request('username').$counter)){
                    $counter++;
                }else{
                    $username = request('username').$counter;
                    $usernameIsUnique = true;
                }
            }
        }

        $password = str_random(8);
        $authorization = Authorization::where('name', 'worker')->first();
        $user = User::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'email' => request('email'),
            'username' => $username,
            'password' => Hash::make($password),
            'isPasswordChanged' => 0
        ]);

        $authorization->users()->save($user);

        $data['mail'] = $user->email;
        $data['password'] = $password;

        \Mail::to($user->email)->send(new WorkerCreated($data));
        return redirect('/worker');
    }

    // GET worker/{id}
    public function show(Request $request, $id)
    {
        $request->user()->authorizeRoles(['admin']);

        $worker = User::find($id);

        return view('pages.admin.worker.show', compact('worker'));
    }

    // PATCH worker/{id}
    public function update(Request $request, $id)
    {
        //
    }

    // DELETE worker/{id}
    public function destroy($id)
    {
        //
    }

    
    //-- helpers --//
    private function checkIfUsernameExist($username){
        if(User::where('username', '=', $username)->count() > 0){
            return true;
        }
        return false;
    }
}
