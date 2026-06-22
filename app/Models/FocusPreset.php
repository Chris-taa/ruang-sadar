<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FocusPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'focus_duration',
        'short_break',
        'long_break',
        'rounds',
    ];

    protected $casts = [
        'focus_duration' => 'integer',
        'short_break'    => 'integer',
        'long_break'     => 'integer',
        'rounds'         => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(FocusSession::class, 'preset_id');
    }
}