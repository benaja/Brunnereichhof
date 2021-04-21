<?php

namespace App\Helpers;

use App\Rapportdetail;
use App\Timerecord;
use App\User;
use Illuminate\Support\Str;

class Utils
{
    public static function getUniqueUsername($username)
    {
        $username = Str::ascii(strtolower($username));

        if (self::checkIfUsernameExist($username)) {
            $usernameIsUnique = false;
            $counter = 1;

            while (! $usernameIsUnique) {
                if (self::checkIfUsernameExist($username.$counter)) {
                    $counter++;
                } else {
                    $username = $username.$counter;
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
        if (is_string($date)) {
            $date = new \DateTime($date);
        } else {
            $date = clone $date;
        }
        if ($type === 'year') {
            $date->modify('first day of january this year');
        } elseif ($type === 'month') {
            $date->modify('first day of this month');
        } else {
            $date->modify('monday this week');
        }

        return $date;
    }

    public static function lastDate($type, $date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        } else {
            $date = clone $date;
        }
        if ($type === 'year') {
            $date->modify('last day of december this year');
        } elseif ($type === 'month') {
            $date->modify('last day of this month');
        } else {
            $date->modify('sunday this week');
        }

        return $date;
    }
}
