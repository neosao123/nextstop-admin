<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRejectionReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'is_active',
        'is_delete',
    ];

    public static function filterDriverRejectionReason(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('driver_rejection_reasons.*')
            ->where('driver_rejection_reasons.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('driver_rejection_reasons.id', 'like', "%{$searchTerm}%");
                $query->orWhere('driver_rejection_reasons.reason', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('driver_rejection_reasons.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
