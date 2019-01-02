<?php

namespace App\Http\Controllers;

use Fpdf;
use App\User;
use App\Rapport;
use App\Employee;
use App\Customer;
use App\Rapportdetail;
use Illuminate\Http\Request;
use App\Enums\AuthorizationType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OverviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET overview
    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);

        return view('pages.admin.overview.index');
    }

    


    // helpers

}
