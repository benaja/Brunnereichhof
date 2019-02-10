<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use App\Worktype;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function index()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $settings = Settings::allSettings();

        return $settings;
    }

    public function timeSettings()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'worker']);

        $response = [
            'fullDayShortStart' => Settings::value('fullDayShortStart'),
            'fullDayShortEnd' => Settings::value('fullDayShortEnd'),
            'fullDayLongStart' => Settings::value('fullDayLongStart'),
            'fullDayLongEnd' => Settings::value('fullDayLongEnd'),
            'worktypes' => Worktype::all()
        ];

        return $response;
    }

    public function hourrecordSettings()
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin', 'customer']);

        return [
            'hourrecordStartDate' => Settings::value('hourrecordStartDate'),
            'hourrecordEndDate' => Settings::value('hourrecordEndDate')
        ];
    }

    public function update(Request $request)
    {
        auth()->user()->authorizeRoles(['admin', 'superadmin']);

        $updatetKey = key($request->except('_token'));

        Settings::put($updatetKey, $request->$updatetKey);
    }
}
