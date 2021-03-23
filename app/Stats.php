<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    protected $fillable = ['value', 'key'];

    protected $casts = [
        'values' => 'array',
    ];

    public static function values($key)
    {
        $stat = self::firstOrCreate([
            'key' => $key,
        ]);

        return $stat->values;
    }

    public static function put($key, $values)
    {
        $stat = self::firstOrCreate([
            'key' => $key,
        ]);
        $stat->values = $values;
        $stat->save();
    }
}
