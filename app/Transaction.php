<?php

namespace App;

use DateTimeInterface;
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
        'transactionable_id',
        'transactionable_type',
        'entered',
    ];

    protected $dates = [
        'date',
    ];

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
