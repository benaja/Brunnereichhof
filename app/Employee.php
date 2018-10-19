<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public $table = "employee";

    protected $fillable = ['callname', 'firstname', 'lastname', 'nationality', 'isIntern', 'isDriver', 'german_knowledge', 'english_knowledge', 'sex', 'comment', 'experience', 'isActive', 'profileimage', 'allergy'];

    public function Rapportdetails(){
        return $this->hasMany(Rapportdetail::class);
    }

}
