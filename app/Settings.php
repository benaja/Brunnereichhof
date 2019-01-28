<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $table = "settings";

    protected $fillable = ["value"];

    public static function value($key)
    {
        $setting = Settings::where('key', $key)->first();
        if ($setting->type == "int") {
            return intval($setting->value);
        } else if ($setting->typ == "double") {
            return doubleval($setting->value);
        } else {
            return $setting->value;
        }
    }

    public static function put($key, $value)
    {
        $setting = Settings::where('key', $key)->first();
        $setting->value = $value;
        $setting->save();
    }

    public static function allSettings()
    {
        $settings = Settings::all();

        $response = [];
        foreach ($settings as $setting) {
            if ($setting->type == "int") {
                $response[$setting->key] = intval($setting->value);
            } else if($setting->tpye == "double") {
                $response[$setting->key] = doubleval($setting->value);
            } else {
                $response[$setting->key] = $setting->value;
            }
        }
        return $response;
    }
}
