<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Customer extends Model
{
    use HasFactory;

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package():BelongsToMany
    {
        return $this->belongsToMany(related: Package::class);
    }

    protected $fillable = [
        'gender',
        'goal',
        'age',
        'height',
        'weight',
        'targetWeight',
        'health',
        'fitnessLevel',
        'strength',
        'fatStatus',
        'image',
        'user_id',
    ];

}
