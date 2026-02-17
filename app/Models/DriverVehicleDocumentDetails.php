<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicleDocumentDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'porter_id',
        'vehicle_id',
        'document_type',
        'document_number',
        'document_expiry_date',
        'document_file_type',
        'document_file_path',
        'document_verification_status',
        'document_uploaded_at',
        'document_verified_by',
        'document_verified_at',
        'is_active',
        'is_delete',
    ];
}
