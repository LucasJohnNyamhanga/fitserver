<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class);
    }

    public function bodyTarget():BelongsToMany
    {
        return $this->belongsToMany(BodyTarget::class);
    }

    public function packages():BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }
    public function trainer():BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    protected $fillable = [
        'jina',
        'maelezo',
        'ugumu',
        'muda',
        'picha',
        'muscleName',
        'trainer_id',
        'active',
        'video',
        'repetition',
        'seti',
        'instructions',
    ];
}