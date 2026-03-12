<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'complain',
        'reservation_date',
        'appointment_time',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'reservation_service')
                    ->withTimestamps();
    }
}
