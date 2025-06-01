<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivePackage extends Model
{
    use HasFactory;

    public function user():BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    protected $fillable = [
        'package_id',
        'user_id',
    ];
}
