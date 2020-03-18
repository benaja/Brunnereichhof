<?php

namespace App\Http\Controllers;

use App\WorkInputType;
use App\Worktype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorktypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    // GET worktypes
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin', 'worker'], ['timerecord_read_write', 'settings_read']);

        return Worktype::with('workInputTypes')->get();
    }

    // POST worktypes
    public function store(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'settings_write']);
    }

    // PUT worktypes/{id}
    public function update(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'settings_write']);

        $this->validate($request, [
            'name_de' => 'required|string',
            'manually' => 'required|boolean',
            'work_input_types.*.name' => 'required|string',
            'work_input_types.*.hours' => 'required|numeric'
        ]);

        DB::transaction(function () use ($request, $id) {
            $worktype = Worktype::find($id);
            $worktype->update([
                'name_de' => $request->name_de,
                'manually' => $request->manually
            ]);

            $existingWorkInputTypes = array_filter($request->work_input_types, function ($workInputType) {
                return isset($workInputType['id']);
            });
            $existingWorkInputTypeIds = array_map(function ($workInputType) {
                return $workInputType['id'];
            }, $existingWorkInputTypes);

            $worktype->workInputTypes()->whereNotIn('id', $existingWorkInputTypeIds)->delete();

            foreach ($request->work_input_types as $inputType) {
                if (isset($inputType['id'])) {
                    $workInputType = WorkInputType::find($inputType['id']);
                    $workInputType->update($inputType);
                } else {
                    $workInputType = WorkInputType::create($inputType);
                    $workInputType->worktype()->associate($worktype);
                    $workInputType->save();
                }
            }
        });
    }

    // DELETE worktypes/{id}
    public function destroy(Request $request, $id)
    {
        auth()->user()->authorize(['superadmin'], ['timerecord_read_write', 'settings_write']);
    }
}
