<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Coupons extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_code',
        'coupon_image',
        'coupon_type',
        'coupon_amount_or_percentage',
        'coupon_cap_limit',
        'coupon_min_order_amount',
        'coupon_description',
        'is_active',
        'is_delete'
    ];



    public static function filterCoupons(string $searchTerm = "", int $limit = 0, int $skip = 0, $filterArray = [])
    {
        $query = self::select('coupons.*')
            ->where('coupons.is_delete', 0);

        if (!empty($filterArray)) {
            if (isset($filterArray['search_coupon_type'])) {
                $query->where('coupons.coupon_type', $filterArray['search_coupon_type']);
            }

            if (isset($filterArray['search_status'])) {
                $status = $filterArray['search_status'] === 'active' ? 1 : 0;
                $query->where('coupons.is_active', $status);
            }
			if (!empty($filterArray['from_date']) || !empty($filterArray['to_date'])) {
				
				if (!empty($filterArray['from_date'])) {
					$startDate = Carbon::createFromFormat('d-m-Y', $filterArray['from_date'])->format('Y-m-d') . " 00:00:00";
				}
				if (!empty($filterArray['to_date'])) {
					$endDate = Carbon::createFromFormat('d-m-Y', $filterArray['to_date'])->format('Y-m-d') . " 23:59:59";
				}
				
				
				if (isset($startDate) && isset($endDate)) {
					$query->where(function ($subQuery) use ($startDate, $endDate) {
						$subQuery->whereBetween('coupons.coupon_start_date', [$startDate, $endDate])
								 ->orWhereBetween('coupons.coupon_end_date', [$startDate, $endDate]);
					});
				}
			}
        }


        // Apply search filters
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('coupons.coupon_code', 'like', "%{$searchTerm}%")
                    ->orWhere('coupons.coupon_type', 'like', "%{$searchTerm}%")
                    ->orWhere('coupons.coupon_amount_or_percentage', 'like', "%{$searchTerm}%")
                    ->orWhere('coupons.coupon_cap_limit', 'like', "%{$searchTerm}%")
                    ->orWhere('coupons.coupon_min_order_amount', 'like', "%{$searchTerm}%");
            });
        }

        // Get total count of records
        $total = $query->count();

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the filtered results
        $result = $query->orderBy('coupons.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
