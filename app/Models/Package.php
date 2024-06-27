<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected $fillable = [
        'title',
        'image',
        'target',
        'price',
    ];
}