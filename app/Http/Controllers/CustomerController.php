<?php

namespace App\Http\Controllers;

use App\Address;
use App\User;
use App\Project;
use App\Customer;
use App\UserType;
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
        auth()->user()->authorize(['superadmin'], ['customer_read', 'rapport_read', 'hourrecord_write', 'evaluation_customer']);

        if (isset($request->deleted)) $customers = Customer::with('address')->onlyTrashed()->orderBy('lastname')->get();
        else if (isset($request->all)) $customers = Customer::with('address')->withTrashed()->orderBy('lastname')->get();
        else $customers = Customer::with('address')->orderBy('lastname')->get();

        foreach ($customers as $customer) {
            $customer->username = $customer->user()->withTrashed()->first()->username;
            $customer->email = $customer->user()->withTrashed()->first()->email;
        }
        $customers = $customers->toArray();

        return array_values($customers);
    }

    // POST customer
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['customer_write']);

        $this->validate($request, $this->validateArray);
        $this->validate($request, [
            'email' => 'nullable|email|unique:user',
            'customer_number' => 'nullable|unique:customer'
        ]);
        $this->validateAddress($request, 'address');
        if ($request->differingBillingAddress) {
            $this->validateAddress($request, 'billing_address');
        }

        DB::transaction(function () use ($request) {
            $password = str_random(8);
            $secret = encrypt($password);

            $usertype = UserType::where('name', 'customer')->first();
            $user = User::create([
                'firstname' => request('firstname'),
                'lastname' => request('lastname'),
                'email' => request('email'),
                'username' => $request->customer_number,
                'password' => Hash::make($password)
            ]);
            $usertype->users()->save($user);

            $customer = Customer::create([
                'firstname' => request('firstname'),
                'lastname' => request('lastname'),
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
                'needs_payment_order' => request('needs_payment_order'),
                'differingBillingAddress' => request('differingBillingAddress')
            ]);

            $address = Address::create($request->address);
            $customer->address()->associate($address);

            if ($request->differingBillingAddress) {
                $billingAddress = Address::create($request->billing_address);
                $customer->billingAddress()->associate($billingAddress);
            }

            $defaultProject = Project::where('name', 'Allgemein')->first();
            if ($defaultProject == null) {
                return response('no project called "Allgemein"', 404);
            }

            $projectIds = array_map(function($project) {
              return $project['id'];
            }, $request->projects);
            array_push($projectIds, $defaultProject->id);
            
            $customer->projects()->sync($projectIds);


            $customer->save();

            if ($user->email != null) {
                $data['mail'] = $user->email;
                $data['password'] = $password;

                \Mail::to($user->email)->send(new CustomerCreated($data));
            }

            return $customer;
        });
    }

    // GET customer/{id}
    public function show(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['customer_read']);

        $customer = Customer::with(['address', 'billingAddress', 'projects'])->find($id);
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
        auth()->user()->authorize(['superadmin'], ['customer_write']);

        if (isset($request->deleted_at)) {
            $customer = Customer::withTrashed()->find($id);
            $customer->restore();
            $customer->user()->withTrashed()->first()->restore();
            return response('success');
        }
        $this->validate($request, $this->validateArray);

        $customer = Customer::find($id);

        $customer->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
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
            'needs_payment_order' => $request->needs_payment_order,
            'differingBillingAddress' => $request->differingBillingAddress
        ]);
        $customer->user->username = $request->customer_number;
        $customer->user->save();

        $customer->address()->update($request->address);
        if ($request->differingBillingAddress && $request->billing_address) {
            if (!$customer->billingAddress) {
                $billingAddress = Address::create($request->billing_address);
                $customer->billingAddress()->associate($billingAddress);
                $customer->save();
            } else {
                $customer->billingAddress()->update($request->billing_address);
            }
        }

        $projectIds = array_map(function($project) {
            return $project['id'];
        }, $request->projects);
        
        $customer->projects()->sync($projectIds);

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
        auth()->user()->authorize(['superadmin'], ['customer_write']);

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
        auth()->user()->authorize(['superadmin'], ['customer_write']);

        $customer = Customer::find($id);
        $customer->user->delete();
        $customer->delete();
    }

    // GET customer/{id}/projects
    public function projects($id)
    {
        auth()->user()->authorize(['superadmin'], ['customer_read']);

        return Customer::withTrashed()->find($id)->projects;
    }

    //-- helpers --//
    private $validateArray = [
        'firstname' => 'required|string|max:100',
        'lastname' => 'required|string|max:100',
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
        'needs_payment_order' => 'nullable|boolean',
        'differingBillingAddress' => 'nullable|boolean'
    ];

    private function validateAddress($request, $addressType)
    {
        $this->validate($request, [
            "$addressType.addition" => 'nullable|max:400|string',
            "$addressType.street" => 'required|string|max:100',
            "$addressType.place" => 'required|string|max:100',
            "$addressType.plz" => 'required|integer',
        ]);
    }
}
