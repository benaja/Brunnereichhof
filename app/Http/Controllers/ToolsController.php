<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToolRequest;
use App\Http\Resources\ToolResource;
use App\Tool;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index(Request $request) {
        $tools = Tool::orderBy('name')
          ->when($request->get('search'), function ($query, $search){
            $query->where('name', 'LIKE', "%$search%");
          })
          ->get();

        return ToolResource::collection($tools); 
    }

    public function store(ToolRequest $request) {
        return ToolResource::make($request->store());
    }

    public function update(ToolRequest $request, Tool $tool) {
        $tool = $request->update($tool);

        return ToolResource::make($tool);
    }

    public function destroy(Tool $tool) {
        $tool->delete();
    }
}
