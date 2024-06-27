<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    use HasFactory;

     public function package():BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
    protected $fillable = [
        'title',
        'detail',
        'package_id'
    ];
}