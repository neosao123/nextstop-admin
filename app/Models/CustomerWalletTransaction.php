<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWalletTransaction extends Model
{
    use HasFactory;
	
	protected $fillable = [
		'trip_id',
		'customer_id',
		'type',
		'message',
		'amount',
		'status',
		'payment_status',
		'payment_response',
		'payment_id',
		'payment_order_id',
		'payment_mode',
		'is_active',
		'is_delete',
	];
}
