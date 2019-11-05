<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    public $table = "rapport";

    protected $fillable = ['isFinished', 'startdate', 'rapporttype', 'comment_mo', 'default_project_id', 'comment_tu', 'comment_we', 'comment_th', 'comment_fr', 'comment_sa'];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }

    public function defaultProject()
    {
        return $this->belongsTo(Project::class, 'default_project_id');
    }

    public function hours()
    {
        return $this->rapportdetails->sum('hours');
    }
}
