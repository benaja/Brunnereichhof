<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkInputType extends Model
{
    public $table = "work_input_type";

    protected $fillable = ['name', 'hours'];

    public function worktype()
    {
        return $this->belongsTo(Worktype::class);
    }
}
