<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    use HasFactory;

    protected $fillable = ['family_allowance_id', 'type', 'value', 'expiration_date'];

    public function familyAllowance()
    {
        return $this->belongsTo(FamilyAllowance::class);
    }
}
