<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rontgen extends Model
{
    use HasFactory;

    protected $table = 'rontgen';

    protected $fillable = [
        'patient_id',
        'xray_image',
        'detail',
    ];

    protected $appends = ['xray_image_url'];

    // Accessor for xray_image URL
    public function getXrayImageUrlAttribute()
    {
        if ($this->xray_image) {
            return asset('storage/' . $this->xray_image);
        }
        return null;
    }

    // Relationship
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
