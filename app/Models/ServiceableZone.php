<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\GeometryCast;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
class ServiceableZone extends Model
{
    use HasFactory,HasSpatial;

    protected $table="serviceable_zones";
    protected $fillable = [
        'serviceable_zone_name',
		'serviceable_area',
        'is_active',
        'is_delete',
    ];

    protected $casts = [
        'serviceable_area' => Polygon::class,
    ];
    public static function filterServiceableZone(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('serviceable_zones.*')
            ->where('serviceable_zones.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('serviceable_zones.id', 'like', "%{$searchTerm}%");
                $query->orWhere('serviceable_zones.serviceable_zone_name', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('serviceable_zones.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
