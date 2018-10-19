<?php

namespace App\Http\Controllers;

use App\Culture;
use App\Customer;
use App\Hourrecord;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET plan
    public function index(Request $request){
        $request->user()->authorizeRoles(['customer']);

        $hourrecords = Auth::user()->customer->hourrecords->where('year', date('Y'));

        $agent = new Agent();
        if($agent->isMobile()){
            return view("pages.customer.plan-mobile", compact('hourrecords'));
        }else{ 
            return view('pages.customer.plan', compact('hourrecords'));
        }

    }

    // POST plan
    public function store(Request $request){
        $request->user()->authorizeRoles(['customer']);

        $weeks = request('weeks');
        $hours = request('hours');
        $cultures = request('cultures');
        $comments = request('comments');

        $customer = Auth::user()->customer;

        for($i = 0; $i < count($weeks); $i++){
            $hourrecord = Hourrecord::create([
                'week' => $weeks[$i],
                'hours' => $hours[$i],
                'comment' => $comments[$i],
                'year' => date('Y')
            ]);
            $hourrecord->customer()->associate($customer);

            $culture = Culture::firstOrCreate(['name' => $cultures[$i]]);
            $hourrecord->culture()->associate($culture);
            $hourrecord->save();
        }
    }

    // GET cultures/all
    public function cultures(Request $request){
        $request->user()->authorizeRoles(['customer', 'admin']);

        $cultures = Culture::all('name');

        return $cultures;
    }

    // GET plan/edit
    public function edit(Request $request){
        $request->user()->authorizeRoles(['customer', 'admin']);

        $hourrecords = Auth::user()->customer->hourrecords->where('year', date('Y'))->sortBy('week');

        $customerId = $request->user()->customer->id;

        $agent = new Agent();
        if($agent->isMobile()){
            return view("pages.customer.plan-edit-mobile", compact('hourrecords', 'customerId'));
        }else{ 
            return view('pages.customer.plan-edit', compact('hourrecords', 'customerId'));
        }
        
    }

    // PATCH plan/{id}
    public function update(Request $request, $id){
        $request->user()->authorizeRoles(['customer', 'admin']);

        $element = request('element');
        $hourrecord = Hourrecord::find($id);
        if($element == "culture"){
            $culture = Culture::firstOrCreate(['name' => request('data')]);
            $hourrecord->culture()->associate($culture);
        }else{
            $hourrecord->$element = request('data');
        }
        $hourrecord->save();

    }

    // GET plan/delete/{id}
    public function delete(Request $request, $id){
        $request->user()->authorizeRoles(['customer', 'admin']);

        $userid = Hourrecord::find($id)->customer->user->id;

        if($userid == Auth::user()->id){
            $hourrecord = Hourrecord::find($id);
            $hourrecord->delete();
            return redirect('/plan/edit');
        }else{
            return "You dont have access to this page";
        }


    }
}
