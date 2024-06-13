<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instruction extends Model
{
    use HasFactory;

    public function exercises():BelongsTo
    {
        return $this->belongsTo(Exercises::class);
    }
    protected $fillable = [
        'maelezo',
        'exercise_id'
    ];
}
