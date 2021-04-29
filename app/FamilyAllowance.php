<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyAllowance extends Model
{
    use HasFactory;

    public function familyAllowanceable()
    {
        return $this->morphTo();
    }

    public function files()
    {
        return $this->morphMany(File::class, 'filable');
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function employerConfirmation()
    {
        return $this->hasMany(Quarter::class)->where('type');
    }
}
