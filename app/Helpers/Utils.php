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
}
