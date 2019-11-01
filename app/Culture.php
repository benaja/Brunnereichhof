<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Culture extends Model
{
    use SoftDeletes;
    
    public $table = "culture";

    protected $fillable = ['name', 'isAutocomplete'];

    public function hourrecords()
    {
        return $this->hasMany(Hourrecord::class);
    }
}
