<?php

namespace App\Helpers;

class Settings
{
  public const monthNames = [
    "Januar", "Februar", "März", "April", "Mai", "Juni",
    "Juli", "August", "September", "Oktober", "November", "Dezember"
  ];

  public const monthShortNames = [
    "Jan.", "Feb.", "März", "Apr.", "Mai", "Juni",
    "Juli", "Aug.", "Sep.", "Okt.", "Nov.", "Dez."
  ];

  public static function getMonthName($date)
  {
    return Settings::monthNames[intval($date->format('m')) - 1];
  }
}
