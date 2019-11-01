<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    public $table = "project";

    protected $fillable = ['name', 'description'];

    public function customer()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function rapportdetails()
    {
        return $this->hasMany(Rapportdetail::class);
    }
}
