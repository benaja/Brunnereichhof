<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    public $table = "customer";

    protected $fillable = [
        'firstname',
        'lastname',
        'mobile',
        'phone',
        'hasCatering',
        'kitchen_infrastructure',
        'max_catering',
        'comment_catering',
        'driver_info',
        'comment',
        'maps',
        'secret',
        'user_id',
        'customer_number',
        'needs_payment_order',
        'differingBillingAddress',
        'is_blacklisted',
        'blacklist_comment'
    ];

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

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class);
    }
}
