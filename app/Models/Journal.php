<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mood',
        'cause',
        'contain',
        'date',
    ];

    protected $casts = [
        'cause' => 'array',
        'date'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}