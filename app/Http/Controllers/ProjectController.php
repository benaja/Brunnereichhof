<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['rapport_read', 'customer_read']);

        $projects = Project::all();

        return $projects;
    }

    // POST customers/{customerId}/projects/{projectId}
    public function addToCustomer($customerId, $projectId)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write', 'customer_write']);
        $project = Project::find($projectId);

        if ($project == null) {
            return response('project not exists', 400);
        }

        $customer = Customer::withTrashed()->find($customerId);

        $project->customer()->attach($customer);
    }

    // POST project
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);
        $project = Project::create([
            'name' => $request->title,
            'description' => $request->description,
        ]);

        return $project;
    }

    // GET project/{id}
    public function show($id)
    {
        //
    }

    // PATCH project/{id}
    public function update(Request $request, $id)
    {
        //
    }

    // DELETE customer/{customerId}/projects/{projectId}
    public function removeFromCustomer(Request $request, $customerId, $projectId)
    {
        auth()->user()->authorize(['superadmin'], ['rapport_write']);

        $customer = Customer::withTrashed()->find($customerId);
        $project = Project::find($projectId);

        $customer->projects()->detach($project);
    }

    // DELETE project/{id}
    public function destroy($id)
    {
        //
    }
}
