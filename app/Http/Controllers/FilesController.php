<?php

namespace App\Http\Controllers;

use App\File;
use App\Http\Requests\FileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Image;

class FilesController extends Controller
{
    public function store(FileRequest $request)
    {
        return JsonResource::make($request->store());
    }

    public function uploadFile(File $file, Request $request)
    {
        $imagePath = Storage::disk('s3')
            ->putFileAs('files/'.$file->id, $request->file('file'), $request->file('file')->getClientOriginalName());

        $file->path = $imagePath;
        $file->save();

        return JsonResource::make($file);
    }

    public function update(File $file, FileRequest $request)
    {
        return JsonResource::make($request->update($file));
    }
}
