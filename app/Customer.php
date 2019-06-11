<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $table = "customer";

    protected $fillable = ['firstname', 'lastname', 'addition', 'street', 'place', 'plz', 'mobile', 'phone', 'hasCatering', 'kitchen_infrastructure', 'max_catering', 'comment_catering', 'driver_info', 'comment', 'maps', 'secret', 'user_id', 'customer_number', 'needs_payment_order', 'isDeleted'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hourrecords()
    {
        return $this->hasMany(Hourrecord::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }
}
