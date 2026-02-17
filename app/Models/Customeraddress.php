<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customeraddress extends Model
{
    use HasFactory;
	
	public function trip()
    {
        return $this->belongsTo(Trip::class, 'customeraddresses_trip_id');
    }
}
