<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
	
	public function trip()
    {
        return $this->belongsTo(Trip::class, 'rating_trip_id');
    }
}
