<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverOnlineOffline extends Model
{
    use HasFactory;
	
	protected $fillable = [
        'driver_id',
        'status',
        'event_at',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
