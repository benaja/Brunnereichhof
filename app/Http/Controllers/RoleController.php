<?php

namespace App\Http\Controllers;

use App\AuthorizationRule;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorize(['superadmin'], ['worker_read', 'employee_read', 'role_write']);

        return Role::all();
    }

    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['role_write']);

        $this->validate($request, [
            'name' => 'required|string|max:200',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        try {
            $role->authorizationRules()->attach($request->authorizationRules);
        } catch (\Exception $e) {
            $role->forceDelete();
            abort(500, 'Could not create role');
        }

        return $role;
    }

    public function show($id)
    {
        auth()->user()->authorize(['superadmin'], ['role_write']);

        $role = Role::with('authorizationRules')->find($id);

        return $role;
    }

    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['role_write']);

        $this->validate($request, [
            'name' => 'required|string|max:200',
        ]);

        $role = Role::find($id);

        $role->update([
            'name' => $request->name,
        ]);

        $role->authorizationRules()->sync($request->selectedAuthorizationRules);
    }

    public function destroy($id)
    {
        auth()->user()->authorize(['superadmin'], ['role_write']);

        $role = Role::find($id);

        if (count($role->users) != 0) {
            abort(400, 'Role still has one or more users');
        }

        $role->authorizationRules()->detach();

        $role->forceDelete();
    }

    public function getRules()
    {
        auth()->user()->authorize(['superadmin'], ['role_write']);

        return AuthorizationRule::orderBy('name_de')->get();
    }
}
