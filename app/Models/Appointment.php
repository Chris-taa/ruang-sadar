<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'therapist_id',
        'full_name',
        'consultation_date',
        'topic',
        'method',
        'status',
    ];

    // Relasi ke user therapist
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    // Relasi ke user patient
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}