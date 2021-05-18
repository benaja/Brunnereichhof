<?php

namespace App;

use App\Enums\QuarterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyAllowance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['files', 'children'];

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
        return $this->hasMany(Quarter::class)->where('type', QuarterType::EmployerConfirmation);
    }

    public function creditToEichhof()
    {
        return $this->hasMany(Quarter::class)->where('type', QuarterType::CreditToEichhof);
    }
}
