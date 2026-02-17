<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverVehicleDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_number',
        'vehicle_photo',
        'vehicle_id',
        'is_active',
        'is_delete',
    ];

    /**
     * Belongs to Driver
     * @author shreyasm@neosao
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    //seemashelar@neosao
    //vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
