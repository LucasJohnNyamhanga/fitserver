<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    protected $fillable = [
        'reference',
        'status',
        'transaction_id',
        'channel',
        'amount',
        'package_id',
        'phone',
    ];
}
