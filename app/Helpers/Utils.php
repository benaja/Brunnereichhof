<?php

namespace App\Helpers;

use App\Rapportdetail;
use App\Timerecord;
use App\User;

class Utils
{
    public static function getUniqueUsername($username)
    {
        $username = strtolower($username);

        if (Utils::checkIfUsernameExist($username)) {
            $usernameIsUnique = false;
            $counter = 1;

            while (!$usernameIsUnique) {
                if (Utils::checkIfUsernameExist($username . $counter)) {
                    $counter++;
                } else {
                    $username = $username . $counter;
                    $usernameIsUnique = true;
                }
            }
        }
        return $username;
    }

    private static function checkIfUsernameExist($username)
    {
        return User::where('username', $username)->count() > 0;
    }

    public static function firstDate($type, $date)
    {
        if ($type === 'year') {
            $date->modify('first day of january this year');
        } else if ($type === 'month') {
            $date->modify('first day of this month');
        } else {
            $date->modify('monday this week');
        }
        return $date;
    }

    public static function lastDate($type, $date)
    {
        if ($type === 'year') {
            $date->modify('last day of december this year');
        } else if ($type === 'month') {
            $date->modify('last day of this month');
        } else {
            $date->modify('sunday this week');
        }
        return $date;
    }
}
