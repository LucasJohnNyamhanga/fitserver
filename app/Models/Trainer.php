<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trainer extends Model
{
    use HasFactory;

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
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
    ];
}
