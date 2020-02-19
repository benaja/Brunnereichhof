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
        auth()->user()->authorize(['superadmin'], ['settings_read']);

        $settings = Settings::allSettings();

        return $settings;
    }

    public function timeSettings()
    {
        auth()->user()->authorize(['superadmin', 'worker'], ['settings_read']);

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
        auth()->user()->authorize(['superadmin', 'customer'], ['settings_read']);

        return [
            'hourrecordStartDate' => Settings::value('hourrecordStartDate'),
            'hourrecordEndDate' => Settings::value('hourrecordEndDate'),
            'welcomeText' => Settings::value('welcomeText'),
            'hourrecordTitle' => Settings::value('hourrecordTitle'),
            'hourrecordValid' => Settings::value('hourrecordValid'),
            'hourrecordInvalid' => Settings::value('hourrecordInvalid'),
            'surveyTitle' => Settings::value('surveyTitle'),
            'surveyText' => Settings::value('surveyText'),
            'subtitle' => Settings::value('subtitle'),
            'weekRapportTitle' => Settings::value('weekRapportTitle'),
            'weekRapportText' => Settings::value('weekRapportText')
        ];
    }

    public function update(Request $request)
    {
        auth()->user()->authorize(['superadmin'], ['settings_write']);

        $updatetKey = key($request->except('_token'));

        Settings::put($updatetKey, $request->$updatetKey);
    }
}
