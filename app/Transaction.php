<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'amount',
        'date',
        'comment',
        'transaction_type_id',
        'employee_id'
    ];

    protected $dates = [
        'date'
    ];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function type() {
        return $this->belongsTo(TransactionType::class);
    }
}
