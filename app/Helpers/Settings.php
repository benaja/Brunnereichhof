<?php

namespace App\Helpers;

class Settings
{
    public const monthNames = [
    'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
    'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember',
  ];

    public const monthShortNames = [
    'Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni',
    'Juli', 'Aug.', 'Sep.', 'Okt.', 'Nov.', 'Dez.',
  ];

    public const tinyMonthNames = [
    'Ja', 'Fe', 'Mä', 'Ap', 'Mai', 'Ju', 'Jul', 'Au', 'Se', 'Ok', 'No', 'Dez',
  ];

    public const dayNames = [
    'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag',
  ];

    public static function getMonthName($date)
    {
        return self::monthNames[intval($date->format('m')) - 1];
    }

    public static function getShortMonthName($date)
    {
        return self::monthShortNames[intval($date->format('m')) - 1];
    }

    public static function getTinyMonthName($date)
    {
        return self::tinyMonthNames[intval($date->format('m')) - 1];
    }
}
