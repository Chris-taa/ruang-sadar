<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'therapist_id',
        'full_name',
        'consultation_date',
        'topic',
        'method',
        'status',
    ];

    // Relasi ke Pasien
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Relasi ke Terapis/Psikolog
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}