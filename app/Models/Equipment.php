<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipment extends Model
{
    use HasFactory;

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercises::class, 'equipment_excercise', 'equipment_id', 'excercise_id');
    }

    protected $fillable = [
        'jina',
        'picha'
    ];
}
