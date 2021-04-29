<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    use HasFactory;

    public function familyAllowance()
    {
        return $this->belongsTo(FamilyAllowance::class);
    }
}
