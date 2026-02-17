<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOtp extends Model
{
    use HasFactory;
	protected $fillable = [
        'id',
        'otp',
        'mobile',
		'expired_at',
		'used_at',
		'type',
		'trip_id'
    ];
}
