<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    public function exercises():BelongsToMany
    {
        return $this->belongsToMany(Exercise::class);
    }

    public function meals():HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function trainer():BelongsTo
    {
        return $this->belongsTo(related: Trainer::class);
    }

    public function customer():BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    protected $fillable = [
        'title',
        'description',
        'image',
        'target',
        'price',
        'active',
        'rating',
        'expectation',
        'trainer_id',
    ];
}