<?php

namespace App\Http\Controllers;

use App\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguagesController extends Controller
{
    public function index() {
        $languages = Language::all();

        return JsonResource::collection($languages);
    }
}
