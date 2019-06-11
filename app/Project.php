<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public $table = "project";

    protected $fillable = ['name', 'description', 'isDeleted'];

    public function customer()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }
}
