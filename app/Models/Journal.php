<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model{
    use HasFactory;

    // Mengizinkan semua kolom untuk diisi secara langsung (mass assignment)
    protected $guarded = [];

    // Memberitahu Laravel untuk memperlakukan 'cause' sebagai array
    protected $casts = [
        'cause' => 'array',
        'date' => 'datetime',
    ];
}
