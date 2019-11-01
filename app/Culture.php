<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Culture extends Model
{
    public $table = "culture";

    protected $fillable = ['name', 'isAutocomplete'];

    public function hourrecords()
    {
        return $this->hasMany(Hourrecord::class);
    }
}
