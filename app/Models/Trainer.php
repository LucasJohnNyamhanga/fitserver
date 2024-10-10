<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    use HasFactory;

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package():HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function exercise():HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    protected $fillable = [
        'location',
        'bio',
        'services',
        'active',
        'is_super',
        'user_id',
    ];
}
