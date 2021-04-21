<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $guarded = [];

    public const namesMap = [
        'start_time' => 'Startzeit',
        'end_time' => 'Endzeit',
        'comment' => 'Kommentar',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

    public function cars()
    {
        return $this->belongsToMany(Car::class);
    }

    public function tools()
    {
        return $this->belongsToMany(Tool::class)->withPivot('amount');
    }

    public function plannerDay()
    {
        return $this->belongsTo(ResourcePlannerDay::class, 'resource_planner_day_id');
    }

    // protected static function booted()
    // {
    //     static::updating(function ($resource) {
    //         if ($resource->plannerDay && $resource->plannerDay->history_enabled) {
    //         }
    //     });
    // }
}
