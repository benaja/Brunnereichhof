<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $settings = Settings::all();

        return $projects;
    }

    public function timeSettings()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'worker']);

        $response = [
            'fullDayShortStart' => Settings::value('fullDayShortStart'),
            'fullDayShortEnd' => Settings::value('fullDayShortEnd'),
            'fullDayLongStart' => Settings::value('fullDayLongStart'),
            'fullDayLongEnd' => Settings::value('fullDayLongEnd')
        ];

        return $response;
    }

    public function allByCustomer(Request $request, Customer $customer)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return $customer->projects;
    }

    public function addToCustomer(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $project = Project::find($request->projectId);

        if ($project == null) {
            return response('project no exists', 400);
        }

        $customer = Customer::find($request->customerId);

        $project->customer()->attach($customer);
    }

    public function store(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);
        $project = Project::create([
            'name' => $request->title,
            'description' => $request->description
        ]);

        $customer = Customer::find($request->customerId);
        $project->customer()->attach($customer);
        return $project->id;
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
