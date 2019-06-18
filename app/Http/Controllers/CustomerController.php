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
        $this->middleware('jwt.auth');
    }

    // GET customer
    public function index(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $customers = Customer::orderBy('lastname')->where('isDeleted', 0)->get();

        foreach ($customers as $customer) {
            $customer->username = $customer->user->username;
            $customer->email = $customer->user->email;
        }
        $customers = $customers->toArray();

        return array_values($customers);
    }

    // POST customer
    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $this->validate($request, $this->validateArray);
        $this->validate($request, [
            'email' => 'nullable|email|unique:user',
            'customer_number' => 'nullable|unique:customer'
        ]);

        $password = str_random(8);
        $secret = encrypt($password);

        $authorization = Authorization::where('name', 'customer')->first();
        $user = User::create([
            'firstname' => request('firstname'),
            'lastname' => request('lastname'),
            'email' => request('email'),
            'username' => $request->customer_number,
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

        $defaultProject = Project::where('name', 'Allgemein')->first();
        if ($defaultProject == null) {
            $customer->delete();
            return response('unknown error', 404);
        }
        $customer->projects()->save($defaultProject);
        $customer->save();

        if ($user->email != null) {
            $data['mail'] = $user->email;
            $data['password'] = $password;

            \Mail::to($user->email)->send(new CustomerCreated($data));
        }

        return $customer;
    }

    // GET customer/{id}
    public function show(Request $request, Customer $customer)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        //return view('pages.admin.customer.show', compact('customer'));
        $customer->email = $customer->user->email;
        if ($customer->secret != null) {
            $customer->secret = decrypt($customer->secret);
        }
        return response($customer);
    }

    //PATCH customer/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $this->validate($request, $this->validateArray);

        $customer = Customer::find($id);

        $customer->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'addition' => $request->addition,
            'street' => $request->street,
            'place' => $request->place,
            'plz' => $request->plz,
            'mobile' => $request->mobile,
            'phone' => $request->phone,
            'hasCatering' => $request->hasCatering,
            'kitchen_infrastructure' => $request->kitchen_infrastructure,
            'max_catering' => $request->max_catering,
            'comment_catering' => $request->comment_catering,
            'driver_info' => $request->driver_info,
            'comment' => $request->comment,
            'maps' => $request->maps,
            'customer_number' => $request->customer_number,
            'needs_payment_order' => $request->needs_payment_order
        ]);
        $customer->user->username = $request->customer_number;
        $customer->user->save();

        if ($request->email != $customer->user->email) {
            $this->validate($request, [
                'email' => 'nullable|email|unique:user'
            ]);
            $customer->user->email = $request->email;
            $customer->user->save();

            if ($request->email != '') {
                $data['mail'] = $request->email;
                $data['password'] = decrypt($customer->secret);

                \Mail::to($request->email)->send(new CustomerCreated($data));
                return response('email send');
            }
        }
        return response('success');
    }

    public function resetPassword(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $customer = Customer::find($id);

        $password = str_random(8);
        $secret = encrypt($password);

        $customer->user->password = Hash::make($password);
        $customer->user->isPasswordChanged = 0;
        $customer->secret = $secret;

        $customer->user->save();
        $customer->save();

        return $password;
    }

    // DELETE customer/{id}
    public function destroy(Request $request, $id)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $customer = Customer::find($id);
        $customer->user->update([
            'isDeleted' => true,
            'password' => null
        ]);
        $customer->update([
            'isDeleted' => true
        ]);
    }

    //-- helpers --//
    private function checkIfUsernameExist($username)
    {
        if (User::where('username', '=', $username)->count() > 0) {
            return true;
        }
        return false;
    }

    private $validateArray = [
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
        'customer_number' => 'nullable|numeric',
        'needs_payment_order' => 'nullable|boolean'
    ];
}
