<?php

namespace App\Http\Controllers;

use App\Project;
use App\Customer;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $projects = Project::all();

        return $projects;
    }

    // GET project/customer/{customer}
    public function allByCustomer(Request $request, Customer $customer)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        return $customer->projects;
    }

    // POST project/add
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

    // POST project
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

    // GET project/{id}
    public function show($id)
    {
        //
    }

    // GET project/{id}/edit
    public function edit($id)
    {
        //
    }

    // PATCH project/{id}
    public function update(Request $request, $id)
    {
        //
    }

    // DELETE project/{projectName}/customer/{customerId}
    public function removeFromCustomer(Request $request, $projectId, Customer $customer)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $project = Project::find($projectId);

        $customer->projects()->detach($project);
    }

    // DELETE project/{id}
    public function destroy($id)
    {
        //
    }
}
