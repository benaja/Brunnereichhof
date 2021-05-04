<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class FilesController extends Controller
{
    public function store(FileRequest $request)
    {
        return JsonResource::make($request->store());
    }
}
