<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['files'];

    public function familyAllowance()
    {
        return $this->belongsTo(FamilyAllowance::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'filable');
    }
}
