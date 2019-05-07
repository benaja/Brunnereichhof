<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    public $table = "rapport";

    protected $fillable = ['isFinished', 'startdate', 'rapporttype', 'comment', 'default_project_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

    public function defaultProject()
    {
        return $this->belongsTo(Project::class, 'default_project_id');
    }
}
