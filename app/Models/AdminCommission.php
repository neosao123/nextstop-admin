<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminCommission extends Model
{
    use HasFactory;
    
    protected $table = 'admin_commissions';

    protected $fillable = [
        'trip_id',
        'driver_id',
        'amount',
        'status', // Assuming there might be a status
        'created_at',
        'updated_at'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    // Filter commissions function used for the list
    public static function filterCommissions(string $search = "", $limit = 0, $offset = 0, string $driver = "", string $from_date = "", string $to_date = "")
    {
        $query = self::select("admin_commissions.*", 'drivers.driver_first_name', 'drivers.driver_last_name', 'trips.trip_unique_id')
            ->leftJoin('drivers', 'drivers.id', '=', 'admin_commissions.driver_id')
            ->leftJoin('trips', 'trips.id', '=', 'admin_commissions.trip_id');

        if ($driver != "") {
            $query->where('admin_commissions.driver_id', $driver);
        }

        // Date range filters
        if (!empty($from_date)) {
            $query->whereDate('admin_commissions.created_at', '>=', Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d'));
        }

        if (!empty($to_date)) {
            $query->whereDate('admin_commissions.created_at', '<=', Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d'));
        }

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('drivers.driver_first_name', 'like', "%{$search}%")
                      ->orWhere('drivers.driver_last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(drivers.driver_first_name, ' ', drivers.driver_last_name) LIKE ?", ["%{$search}%"])
                      ->orWhere('trips.trip_unique_id', 'like', "%{$search}%")
                      ->orWhere('admin_commissions.type', 'like', "%{$search}%")
                      ->orWhere('admin_commissions.commission_amount', 'like', "%{$search}%")
                      ->orWhere('admin_commissions.grand_total', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('admin_commissions.id', 'DESC');

        $total = $query->count();
        if ($limit && $limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        $result = $query->get();
        return ["totalRecords" => $total, "result" => $result];
    }
}
