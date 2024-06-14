<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    public function instructions():HasMany
    {
        return $this->hasMany(Instruction::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class);
    }

    public function bodyTarget():BelongsToMany
    {
         return $this->belongsToMany(BodyTarget::class);
    }

    protected $fillable = [
        'jina',
        'maelezo',
        'ugumu',
        'muda',
        'picha',
        'muscleName',
    ];

}