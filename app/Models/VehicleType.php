<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type',
        'is_active',
        'is_delete',
    ];


    public static function filterVehicleType(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('vehicle_types.*')
            ->where('vehicle_types.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('vehicle_types.id', 'like', "%{$searchTerm}%");
                $query->orWhere('vehicle_types.vehicle_type', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('vehicle_types.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
