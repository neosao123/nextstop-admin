<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTrainingVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'is_active',
        'is_delete',
        'training_video_verification_reason'
    ];
}
