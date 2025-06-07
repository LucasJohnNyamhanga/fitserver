<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;
    public function user():BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
    protected $fillable = [
        'title',
        'description',
        'image',
        'user_id',
    ];
}
