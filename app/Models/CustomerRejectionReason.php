<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRejectionReason extends Model
{
    use HasFactory;
	
	protected $fillable = [
        'reason',
        'is_active',
        'is_delete',
    ];
	
	  public static function filterCustomerRejectionReason(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('customer_rejection_reasons.*')
            ->where('customer_rejection_reasons.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('customer_rejection_reasons.id', 'like', "%{$searchTerm}%");
                $query->orWhere('customer_rejection_reasons.reason', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('customer_rejection_reasons.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
