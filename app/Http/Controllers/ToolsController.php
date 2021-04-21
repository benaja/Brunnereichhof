<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToolRequest;
use App\Http\Resources\ToolResource;
use App\Tool;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['tools_read', 'resource_planner_write']);

        $tools = Tool::orderBy('name')
          ->when($request->get('search'), function ($query, $search) {
              $query->where('name', 'LIKE', "%$search%");
          })
          ->get();

        return ToolResource::collection($tools);
    }

    public function store(ToolRequest $request)
    {
        auth()->user()->authorize(['superadmin'], ['tools_write']);

        return ToolResource::make($request->store());
    }

    public function update(ToolRequest $request, Tool $tool)
    {
        auth()->user()->authorize(['superadmin'], ['tools_write']);

        $tool = $request->update($tool);

        return ToolResource::make($tool);
    }

    public function destroy(Tool $tool)
    {
        auth()->user()->authorize(['superadmin'], ['tools_write']);

        $tool->delete();
    }
}
