<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Culture extends Model
{
    public $table = "culture";

    protected $fillable = ['name', 'isAutocomplete'];

    public function hourrecord()
    {
        return $this->hasMany(Hourrecord::class);
    }

}
