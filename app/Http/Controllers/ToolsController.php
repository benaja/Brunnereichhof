<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToolRequest;
use App\Http\Resources\ToolResource;
use App\Tool;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index() {
        return ToolResource::collection(Tool::all()); 
    }

    public function store(ToolRequest $request) {
        return ToolResource::make($request->store());
    }

    public function update(ToolRequest $request, Tool $tool) {
        $tool = $request->update($tool);

        return ToolResource::make($tool);
    }
}
