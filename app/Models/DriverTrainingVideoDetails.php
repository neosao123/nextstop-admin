<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTrainingVideoDetails extends Model
{
    use HasFactory;
	
	protected $table="driver_training_videos_details";
	protected $fillable = [
        'driver_id',
        'is_active',
        'is_delete',
        'checked_status'
    ];
	
	//training video 
	public function trainingVideo()
    {
        return $this->belongsTo(TrainingVideo::class, 'training_video_id');
    }
}
