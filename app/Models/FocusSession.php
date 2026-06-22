<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FocusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preset_id',
        'type',
        'planned_duration',
        'actual_duration',
        'started_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'planned_duration' => 'integer',
        'actual_duration'  => 'integer',
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function preset()
    {
        return $this->belongsTo(FocusPreset::class, 'preset_id');
    }
}