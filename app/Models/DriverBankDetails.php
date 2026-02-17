<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverBankDetails extends Model
{
    use HasFactory;

    protected $fillable = [
      'driver_id',
      'driver_bank_name',
      'driver_bank_account_number',
      'driver_bank_ifsc_code',
      'driver_bank_branch_name',
      'is_bank_account_verified',
      'is_bank_account_active',
      'is_active',
      'is_delete',
      'bank_verified_by',
      'bank_verified_at',
      'bank_verification_reason',
	];
}
