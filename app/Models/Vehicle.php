<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'vehicle_name',
        'vehicle_icon',
        'vehicle_dimensions',
        'vehicle_max_load_capacity',
		'vehicle_fixed_km_delivery_charge',
        'vehicle_per_km_delivery_charge',
        'vehicle_per_km_extra_delivery_charge',
        'vehicle_description',
        'vehicle_rules',
		'vehicle_fixed_km',
        'is_active',
        'is_delete',
    ];

    /**
     * Vehicle Realtion With Vehicle Type 
     * @author shreyasm@neosao
     */
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /**
     * Get Vehicle Information For Datatable List 
     * @author shreyasm@neosao
     */
    public static function filterVehicles(string $searchTerm = "", int $limit = 0, int $skip = 0, array $filterArray = [])
    {
        $query = self::select('vehicles.*')
            ->where('vehicles.is_delete', 0);

        if (!empty($filterArray)) {
            if (isset($filterArray['search_vehicle_type'])) {
                $query->whereHas('vehicleType', function ($query) use ($filterArray) {
                    $query->where('vehicle_types.id', $filterArray['search_vehicle_type']);
                });
            }

            if (isset($filterArray['search_vehicle_max_load_capacity'])) {
                $query->where('vehicles.vehicle_max_load_capacity', $filterArray['search_vehicle_max_load_capacity']);
            }

            if (isset($filterArray['search_vehicle_per_km_delivery_charge'])) {
                $query->where('vehicles.vehicle_per_km_delivery_charge', $filterArray['search_vehicle_per_km_delivery_charge']);
            }
        }


        // Apply search filters
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('vehicles.vehicle_name', 'like', "%{$searchTerm}%")
                    ->orWhere('vehicles.vehicle_dimensions', 'like', "%{$searchTerm}%")
                    ->orWhere('vehicles.vehicle_max_load_capacity', 'like', "%{$searchTerm}%")
                    ->orWhere('vehicles.vehicle_per_km_delivery_charge', 'like', "%{$searchTerm}%")
                    ->orWhere('vehicles.vehicle_per_km_extra_delivery_charge', 'like', "%{$searchTerm}%")
                    ->orWhereHas('vehicleType', function ($query) use ($searchTerm) {
                        $query->where('vehicle_types.vehicle_type', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Get total count of records
        $total = $query->count();

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the filtered results
        $result = $query->orderBy('vehicles.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
