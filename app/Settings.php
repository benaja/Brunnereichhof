<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $table = 'settings';

    protected $fillable = ['value', 'key'];

    public static function value($key)
    {
        $setting = self::firstOrCreate([
            'key' => $key,
        ], [
            'key' => $key,
        ]);
        if ($setting->type == 'int') {
            return intval($setting->value);
        } elseif ($setting->typ == 'double') {
            return floatval($setting->value);
        } else {
            return $setting->value;
        }
    }

    public static function put($key, $value)
    {
        $setting = self::firstOrCreate([
            'key' => $key,
        ], [
            'key' => $key,
        ]);
        $setting->value = $value;
        $setting->save();
    }

    public static function allSettings()
    {
        $settings = self::all();

        $response = [];
        foreach ($settings as $setting) {
            if ($setting->type == 'int') {
                $response[$setting->key] = intval($setting->value);
            } elseif ($setting->tpye == 'double') {
                $response[$setting->key] = floatval($setting->value);
            } else {
                $response[$setting->key] = $setting->value;
            }
        }

        return $response;
    }
}
