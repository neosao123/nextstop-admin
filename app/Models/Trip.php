<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
class Trip extends Model
{
    use HasFactory;
	protected $guarded = [];
	
	public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'trip_vehicle_id');
    }
	
	public function customer()
    {
        return $this->belongsTo(Customer::class, 'trip_customer_id');
    }
	
	public function goodtype()
    {
        return $this->belongsTo(GoodsType::class, 'trip_goods_type_id');
    }
	
	public function coupon()
    {
        return $this->belongsTo(Coupons::class, 'trip_coupon_id');
    }
	
	public function customerAddresses()
	{
		return $this->hasMany(Customeraddress::class, 'customeraddresses_trip_id', 'id');
	}
	
	public function sourceAddress()
	{
		return $this->belongsTo(Customeraddress::class, 'trip_source_address_id', 'id');
	}

	public function destinationAddress()
	{
		return $this->belongsTo(Customeraddress::class, 'trip_destination_address_id', 'id');
	}
	
	
	public function driver()
    {
        return $this->belongsTo(Driver::class, 'trip_driver_id');
    }
	
	
	public function rating()
	{
		return $this->hasMany(Rating::class, 'rating_trip_id');
	}
	
	public function tripstatus()
	{
		return $this->hasMany(TripStatus::class, 'trip_id');
	}
	
	public function transaction()
	{
		return $this->hasMany(CustomerWalletTransaction::class, 'trip_id');
	}
	
	public function earnings()
    {
        return $this->hasMany(DriverEarning::class, 'trip_id', 'id');
    }

	
	public static function filterTrips(string $searchTerm = "", int $limit = 0, int $skip = 0, array $filterArray = [])
	{
		$query = self::with('vehicle', 'customer', 'goodtype', 'coupon','sourceAddress','destinationAddress','driver')
			->where('is_delete', 0)
			->whereIn('trip_status', ['pending', 'completed','accepted']);
			
		$query->where(function($q) {
			$q->where('trip_payment_mode', '!=', 'online') // show all non-online payments
			  ->orWhere(function($q2) {
				  $q2->where('trip_payment_mode', 'online')
					 ->where('trip_payment_status', 'completed'); // only completed for online
			  });
		});


		// Apply search filter if provided
		if ($searchTerm) {
			$query->where(function ($query) use ($searchTerm) {
				
				$query->orWhere('trips.trip_unique_id', 'like', "%{$searchTerm}%");
                
				$query->orWhereHas('vehicle', function ($query) use ($searchTerm) {
					$query->where('vehicle_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('customer', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(customer_first_name, ' ', customer_last_name) LIKE ?", ["%{$searchTerm}%"])
						->orWhere('customer_email', 'like', "%{$searchTerm}%")
						->orWhere('customer_phone', 'like', "%{$searchTerm}%");
				});
				
				$query->orWhereHas('driver', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(driver_first_name, ' ', driver_last_name) LIKE ?", ["%{$searchTerm}%"]);
				});

				$query->orWhereHas('goodtype', function ($query) use ($searchTerm) {
					$query->where('goods_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('coupon', function ($query) use ($searchTerm) {
					$query->where('coupon_code', 'like', "%{$searchTerm}%");
				});
				
				$query->orWhereHas('sourceAddress', function ($query) use ($searchTerm) {
					$query->where('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%");
				});
				$query->orWhereHas('destinationAddress', function ($query) use ($searchTerm) {
					$query->where('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%");
				});
			});
		}

		if (!empty($filterArray)) {
			if (!empty($filterArray['unique_id'])) {
				$query->where('trip_unique_id', $filterArray['unique_id']);
			}
			
			if (!empty($filterArray['vehicle_id'])) {
				$query->where('trip_vehicle_id', $filterArray['vehicle_id']);
			}

			if (!empty($filterArray['customer_id'])) {
				$query->where('trip_customer_id', $filterArray['customer_id']);
			}
			
			if (!empty($filterArray['driver_id'])) {
				$query->where('trip_driver_id', $filterArray['driver_id']);
			}

			if (!empty($filterArray['goods_type_id'])) {
				$query->where('trip_goods_type_id', $filterArray['goods_type_id']);
			}

			if (!empty($filterArray['coupon_id'])) {
				$query->where('trip_coupon_id', $filterArray['coupon_id']);
			}
			

			if (!empty($filterArray['is_active'])) {
				$query->where('is_active', $filterArray['is_active']);
			}
			
			
			if (!empty($filterArray['from_date']) || !empty($filterArray['to_date'])) {
				
				if (!empty($filterArray['from_date'])) {
					$startDate = Carbon::createFromFormat('d-m-Y', $filterArray['from_date'])->format('Y-m-d') . " 00:00:00";
				}
				if (!empty($filterArray['to_date'])) {
					$endDate = Carbon::createFromFormat('d-m-Y', $filterArray['to_date'])->format('Y-m-d') . " 23:59:59";
				}
				
				
				// Apply date range filter
				if (isset($startDate) && isset($endDate)) {
					$query->whereBetween('trips.created_at', [$startDate, $endDate]);
				}
			}
			
		}

		// Get the total count of records before applying pagination
		$total = $query->count();

		// Apply pagination if limit is provided
		if ($limit > 0) {
			$query->limit($limit)->offset($skip);
		}

		$result = $query->orderBy('id', 'DESC')->get();

		return ["totalRecords" => $total, "result" => $result];
	}
	
	
	public static function filterCancelTrips(string $searchTerm = "", int $limit = 0, int $skip = 0, array $filterArray = [])
	{
		// JOIN used instead of relying only on with()
		$query = self::query()
			->join(DB::raw('(
				SELECT id AS latest_status_id, trip_id, trip_status_reason, trip_action_type,trip_status_short,created_at 
				FROM trip_statuses 
				WHERE trip_status_title = "cancelled" 
				AND id IN (
					SELECT MAX(id) FROM trip_statuses 
					WHERE trip_status_title = "cancelled" 
					GROUP BY trip_id
				)
			) AS latest_status'), 'trips.id', '=', 'latest_status.trip_id')
			->where('trips.is_delete', 0)
			->where('trips.trip_status', 'cancelled')
			->select('trips.*', 'latest_status.trip_status_short','latest_status.trip_status_reason', 'latest_status.trip_action_type', 'latest_status.latest_status_id','latest_status.created_at as created_date');

		// Search term logic
		if ($searchTerm) {
			$query->where(function ($query) use ($searchTerm) {
				$query->orWhere('trips.trip_unique_id', 'like', "%{$searchTerm}%");

				$query->orWhere('latest_status.trip_status_short', 'like', "%{$searchTerm}%")
					  ->orWhere('latest_status.trip_status_reason', 'like', "%{$searchTerm}%");

                $query->orWhere('latest_status.trip_action_type', 'like', "%{$searchTerm}%")
                      ->orWhere('latest_status.trip_status_reason', 'like', "%{$searchTerm}%");
				$query->orWhereHas('vehicle', function ($query) use ($searchTerm) {
					$query->where('vehicle_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('customer', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(customer_first_name, ' ', customer_last_name) LIKE ?", ["%{$searchTerm}%"])
						->orWhere('customer_email', 'like', "%{$searchTerm}%")
						->orWhere('customer_phone', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('driver', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(driver_first_name, ' ', driver_last_name) LIKE ?", ["%{$searchTerm}%"]);
				});

				$query->orWhereHas('goodtype', function ($query) use ($searchTerm) {
					$query->where('goods_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('coupon', function ($query) use ($searchTerm) {
					$query->where('coupon_code', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('sourceAddress', function ($query) use ($searchTerm) {
					$query->where('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('destinationAddress', function ($query) use ($searchTerm) {
					$query->where('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%");
				});
			});
		}

		// Filter array conditions
		if (!empty($filterArray)) {
			if (!empty($filterArray['unique_id'])) {
				$query->where('trip_unique_id', $filterArray['unique_id']);
			}
			if (!empty($filterArray['vehicle_id'])) {
				$query->where('trip_vehicle_id', $filterArray['vehicle_id']);
			}
			if (!empty($filterArray['customer_id'])) {
				$query->where('trip_customer_id', $filterArray['customer_id']);
			}
			if (!empty($filterArray['driver_id'])) {
				$query->where('trip_driver_id', $filterArray['driver_id']);
			}
			if (!empty($filterArray['goods_type_id'])) {
				$query->where('trip_goods_type_id', $filterArray['goods_type_id']);
			}
			if (!empty($filterArray['coupon_id'])) {
				$query->where('trip_coupon_id', $filterArray['coupon_id']);
			}
			if (!empty($filterArray['is_active'])) {
				$query->where('is_active', $filterArray['is_active']);
			}
			if (!empty($filterArray['from_date']) || !empty($filterArray['to_date'])) {
				if (!empty($filterArray['from_date'])) {
					$startDate = Carbon::createFromFormat('d-m-Y', $filterArray['from_date'])->format('Y-m-d') . " 00:00:00";
				}
				if (!empty($filterArray['to_date'])) {
					$endDate = Carbon::createFromFormat('d-m-Y', $filterArray['to_date'])->format('Y-m-d') . " 23:59:59";
				}
				if (isset($startDate) && isset($endDate)) {
					$query->whereBetween('trips.created_at', [$startDate, $endDate]);
				}
			}
		}

		// Total count before pagination
		$total = $query->count();

		// Apply pagination
		if ($limit > 0) {
			$query->limit($limit)->offset($skip);
		}

		// Apply sorting by joined table
		$result = $query->orderBy('latest_status.latest_status_id', 'DESC')->get();

		// Load relationships after join (since with() won't auto-load with join)
		$result->load(['vehicle', 'customer', 'goodtype', 'coupon', 'sourceAddress', 'destinationAddress', 'driver', 'tripstatus']);

		return ["totalRecords" => $total, "result" => $result];
	}


	public static function filterRefund(string $searchTerm = "", int $limit = 0, int $skip = 0, array $filterArray = [])
	{
		$query = self::with(['vehicle', 'customer', 'goodtype', 'coupon', 'customerAddresses', 'driver'])
			->where('trips.is_delete', 0)
			->where('trips.trip_payment_status',"refund");
		// Apply search filter if provided
		if ($searchTerm) {
			$query->where(function ($query) use ($searchTerm) {
				
				$query->orWhere('trips.trip_unique_id', 'like', "%{$searchTerm}%");
				$query->orWhere('trips.trip_total_amount', 'like', "%{$searchTerm}%");
				
                $query->orWhereHas('vehicle', function ($query) use ($searchTerm) {
					$query->where('vehicle_name', 'like', "%{$searchTerm}%");
				});
				$query->orWhereHas('customer', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(customer_first_name, ' ', customer_last_name) LIKE ?", ["%{$searchTerm}%"])
						->orWhere('customer_email', 'like', "%{$searchTerm}%")
						->orWhere('customer_phone', 'like', "%{$searchTerm}%");
				});
				
				$query->orWhereHas('driver', function ($query) use ($searchTerm) {
					$query->whereRaw("CONCAT(driver_first_name, ' ', driver_last_name) LIKE ?", ["%{$searchTerm}%"]);
				});

				$query->orWhereHas('goodtype', function ($query) use ($searchTerm) {
					$query->where('goods_name', 'like', "%{$searchTerm}%");
				});

				$query->orWhereHas('coupon', function ($query) use ($searchTerm) {
					$query->where('coupon_code', 'like', "%{$searchTerm}%");
				});
				
				$query->orWhereHas('customerAddresses', function ($query) use ($searchTerm) {
					$query->where('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%");
				});
			});
		}

		if (!empty($filterArray)) {
			if (!empty($filterArray['unique_id'])) {
				$query->where('trip_unique_id', $filterArray['unique_id']);
			}
			
			if (!empty($filterArray['vehicle_id'])) {
				$query->where('trip_vehicle_id', $filterArray['vehicle_id']);
			}

			if (!empty($filterArray['customer_id'])) {
				$query->where('trip_customer_id', $filterArray['customer_id']);
			}
			
			if (!empty($filterArray['driver_id'])) {
				$query->where('trip_driver_id', $filterArray['driver_id']);
			}

			if (!empty($filterArray['goods_type_id'])) {
				$query->where('trip_goods_type_id', $filterArray['goods_type_id']);
			}

			if (!empty($filterArray['coupon_id'])) {
				$query->where('trip_coupon_id', $filterArray['coupon_id']);
			}
			

			if (!empty($filterArray['is_active'])) {
				$query->where('is_active', $filterArray['is_active']);
			}
			
		}

		// Get the total count of records before applying pagination
		$total = $query->count();

		// Apply pagination if limit is provided
		if ($limit > 0) {
			$query->limit($limit)->offset($skip);
		}

		$result = $query->orderBy('trips.id', 'DESC')->get();

		return ["totalRecords" => $total, "result" => $result];
	}

	
	
}
