<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rapportdetail extends Model
{
    use SoftDeletes;
    
    public $table = "rapportdetail";

    protected $fillable = ['hours', 'comment', 'day', 'foodtype_id', 'date', 'project_id'];

    protected $appends = ['foodtype_ok'];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function foodtype()
    {
        return $this->belongsTo(Foodtype::class);
    }

    public function getFoodtypeOkAttribute()
    {
        if ($this->hours > 0) {
            $otherRapportdetailsForThisEmployee =  Rapportdetail::where([
                'date' => $this->date,
                'employee_id' => $this->employee_id
            ])->where('id', '!=', $this->id)->where('hours', '>', 0)->get();

            foreach ($otherRapportdetailsForThisEmployee as $rapportdetail) {
                if ($rapportdetail->foodtype_id != $this->foodtype_id) {
                    return false;
                }
            }
        }
        return true;
    }
}
