<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\Customer;
use App\Authorization;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Mail\CustomerCreated;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET customer
    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        $customers = DB::table('customer')->select('customer.*', 'user.email', 'user.username')->join('user', 'customer.user_id', 'user.id')->get();

        return view('pages.admin.customer.index', compact('customers'));        
    }

    // GET customer/create
    public function create(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.customer.create');
    }

    // POST customer
    public function store(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);
        
        $this->validate(request(), [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'addition' => 'nullable|max:400|string',
            'street' => 'required|string|max:100',
            'place' => 'required|string|max:100',
            'plz' => 'required|integer',
            'mobile' => 'nullable',
            'phone' => 'nullable',
            'hasCatering' => 'nullable|boolean',
            'kitchen_infrastructure' => 'nullable|string|max:500',
            'max_catering' => 'nullable|integer',
            'comment_catering' => 'nullable|string|max:500',
            'driver_info' => 'nullable|string|max:500',
            'comment' => 'nullable|string|max:1000',
            'maps' => 'nullable|string|max:1000',
            'customer_number' => 'nullable|integer',
            'email' => 'nullable|email|unique:user',
            'username' => 'required|string|max:100'
        ]);

        $password = str_random(8);

        $secret = encrypt($password);

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
        
        $authorization = Authorization::where('name', 'customer')->first();
        $user = User::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'email' => request('email'),
            'username' => $username,
            'password' => Hash::make($password)
        ]);
        $authorization->users()->save($user);

        $customer = Customer::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'addition' => request('addition'),
            'street' => request('street'),
            'place' => request('place'),
            'plz' => request('plz'),
            'mobile' => request('mobile'),
            'phone' => request('phone'),
            'hasCatering' => request('hasCatering'),
            'kitchen_infrastructure' => request('kitchen_infrastructure'),
            'max_catering' => request('max_catering'),
            'comment_catering' => request('comment_catering'),
            'driver_info' => request('driver_info'),
            'comment' => request('comment'),
            'maps' => request('maps'),
            'secret' => $secret,
            'user_id' => $user->id,
            'customer_number' => request('customer_number'),
            'needs_payment_order' => request('needs_payment_order')
        ]);

        if($user->email != null){
            $data['mail'] = $user->email;
            $data['password'] = $password;
            
            \Mail::to($user->email)->send(new CustomerCreated($data));
        }

        return redirect('/customer/');
    }

    // GET customer/{id}
    public function show(Request $request, Customer $customer)
    {
        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.customer.show', compact('customer'));
    }

    //PATCH customer/{id}
    public function update(Request $request, $id)
    {
        $request->user()->authorizeRoles(['admin']);
        $element = request('element');
        $customer = Customer::find($id);
        if(request('element') == 'email'){
            $user = User::find($customer->user_id);
            $user->email = request('data'); 
            $user->save();
        }else{
            $customer->$element = request('data');
        }
        if(request('element') == "hasCatering" && request('data') == 0){
            $customer->kitchen_infrastructure = null;
            $customer->max_catering = null;
            $customer->comment_catering = null;
        }
        $customer->save();
    }

    // DELETE customer/{id}
    public function destroy(Request $request, $id)
    {
        $request->user()->authorizeRoles(['admin']);

        $customer = Customer::find($id);

        $customer->user->delete();
    }

    // GET customer/all
    public function all(Request $request){
        $request->user()->authorizeRoles(['admin']);

        return Customer::all();
    }

    // GET customer/find
    public function find(Request $request){
        $request->user()->authorizeRoles(['admin']);

        $name = explode(" ", $request->name);

        $customer = Customer::where([['firstname', '=', $name[0]], ['lastname', '=', $name[count($name)-1]]])->first();

        return $customer;
    }

    //-- helpers --//
    private function checkIfUsernameExist($username){
        if(User::where('username', '=', $username)->count() > 0){
            return true;
        }
        return false;
    }
}
