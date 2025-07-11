<?php

namespace App\Models;

use App\Jobs\CheckPaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'package_id',
        'user_id',
        'reference',
        'status',
        'transaction_id',
        'channel',
        'phone',
        'amount',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            if ($payment->status === 'pending') {
                CheckPaymentStatus::dispatch($payment);
            }
        });
    }
}
