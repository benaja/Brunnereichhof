<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hourrecord extends Model
{
    public $table = "hourrecords";

    protected $fillable = ['customer_id', 'culture_id', 'week', 'hours', 'comment', 'year', 'createdByAdmin'];

    public function customer()
    {
        // return $this->belongsTo(Customer::class);
        return $this->belongsTo('App\Customer');
    }

    public function culture()
    {
        // return $this->belongsTo(Culture::class);
        return $this->belongsTo('App\Culture');
    }
}
