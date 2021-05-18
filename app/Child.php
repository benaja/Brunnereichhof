<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function familyAllowance()
    {
        return $this->belongsTo(FamilyAllowance::class);
    }
}
