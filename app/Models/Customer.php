<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Rating;
use Carbon\Carbon;

class Customer extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;
	protected $fillable = [
		'customer_first_name',
		'customer_last_name',
		'customer_email',
		'customer_phone',
		'customer_otp',
		'customer_affiliate_id',
		'customer_avatar',
		'customer_password',
		'customer_wallet_balance',
		'customer_email_verified',
		'customer_phone_verified',
		'customer_has_business',
		'customer_business_name',
		'customer_business_tax_number',
		'customer_business_type',
		'customer_account_status',
		'is_active',
		'is_delete',
		'is_block',
		'is_customer_delete',
		'customer_referral_code',
		'customer_referral_by',
		'customer_referral_by_id',
		'customer_referral_wallet',
		'customer_firebase_token'
	];

	//filter customer function which is used for list
	public static function filterCustomer(string $search = "", $limit = 0, $offset = 0, string $customer = "", string $mobile = "", string $email = "", string $account_status = "")
	{
		$query = self::select("customers.*", "referred_customer.customer_first_name as referred_customer_first_name", "referred_customer.customer_last_name as referred_customer_last_name", "referred_customer.customer_referral_code as referred_customer_code")
			->leftJoin('customers as referred_customer', 'customers.customer_referral_by_id', '=', 'referred_customer.id');
		/*
		if ($account_status != "") {
			if ($account_status === 'active') {
				$query->where('customers.is_delete', 0);
			} elseif ($account_status === 'inactive') {
				$query->where('customers.is_active', 0)->where('customers.is_delete', 0);
			} elseif ($account_status === 'delete') {
				$query->where('customers.is_customer_delete', 1);
			}
		} else {
			$query->where('customers.is_delete', 0);
		}
		*/

		if ($account_status != "") {
			if ($account_status === 'active') {
				$query->where('customers.is_active', 1)->where('customers.is_delete', 0);
			} elseif ($account_status === 'inactive') {
				$query->where('customers.is_active', 0)->where('customers.is_delete', 0);
			} elseif ($account_status === 'delete') {
				$query->where('customers.is_customer_delete', 1);
			}
		} else {
			$query->where('customers.is_delete', 0);
		}

		if ($customer != "") {
			$query->where('customers.id', $customer);
		}
		if ($email != "") {
			$query->where('customers.id', $email);
		}
		if ($mobile != "") {
			$query->where('customers.id', $mobile);
		}
		$query->where(function ($query) use ($search) {
			$query->where('customers.customer_first_name', 'like', "%{$search}%")
				->orWhere('customers.customer_last_name', 'like', "%{$search}%")
				->orWhere('customers.customer_email', 'like', "%{$search}%")
				->orWhere('referred_customer.customer_first_name', 'like', "%{$search}%")
				->orWhere('referred_customer.customer_last_name', 'like', "%{$search}%")
				->orWhere('customers.customer_wallet_balance', 'like', "%{$search}%")
				->orWhere('customers.customer_referral_wallet', 'like', "%{$search}%")
				->orWhere('customers.customer_referral_code', 'like', "%{$search}%")
				->orWhere('customers.customer_phone', 'like', "%{$search}%");
		})
			->orderBy('customers.id', 'DESC');

		$total = $query->count();
		if ($limit && $limit > 0) {
			$query->limit($limit)->offset($offset);
		}
		$result = $query->get();
		return ["totalRecords" => $total, "result" => $result];
	}


	//filter customer rating function which is used for list
	public static function filterRating(string $search = "", int $limit = 0, int $offset = 0, string $customer = "", string $trip = "", string $driver = "")
	{
		$query = Rating::select(
			'trips.trip_unique_id',
			'ratings.*',
			'customers.customer_first_name as customer_first_name',
			'customers.customer_last_name as customer_last_name',
			'drivers.driver_first_name as driver_first_name',
			'drivers.driver_last_name as driver_last_name'
		)
			->join("customers", "customers.id", "=", "ratings.rating_customer_id")
			->join("trips", "trips.id", "=", "ratings.rating_trip_id")
			->join("drivers", "drivers.id", "=", "ratings.rating_driver_id")
			->where('ratings.is_delete', 0)
			->where('ratings.rating_given_by', 'driver');

		if (!empty($customer)) {
			$query->where('ratings.rating_customer_id', $customer);
		}

		if (!empty($trip)) {
			$query->where('ratings.rating_trip_id', $trip);
		}

		if (!empty($driver)) {
			$query->where('ratings.rating_driver_id', $driver);
		}

		if (!empty($search)) {
			$query->where(function ($query) use ($search) {
				$query->where('customers.customer_first_name', 'like', "%{$search}%")
					->orWhere('customers.customer_last_name', 'like', "%{$search}%")
					->orWhere('drivers.driver_first_name', 'like', "%{$search}%")
					->orWhere('drivers.driver_last_name', 'like', "%{$search}%")
					->orWhere('trips.trip_unique_id', 'like', "%{$search}%")
					->orWhere('ratings.rating_value', 'like', "%{$search}%")
					->orWhere('ratings.rating_description', 'like', "%{$search}%");
			});
		}

		$query->orderBy('ratings.id', 'DESC');

		$total = $query->count();

		if ($limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		$result = $query->get();

		return [
			"totalRecords" => $total,
			"result" => $result,
		];
	}


	// Filter customer wallet transaction function which is used for the list
	public static function filterWalletTransaction(
		string $search = "",
		int $limit = 0,
		int $offset = 0,
		string $customer = "",
		string $trip = "",
		string $from_date = "",
		string $to_date = "",
		string $type = ""
	) {

		$query = CustomerWalletTransaction::select(
			'trips.trip_unique_id',
			'customer_wallet_transactions.*',
			'customers.customer_first_name as customer_first_name',
			'customers.customer_last_name as customer_last_name'
		)
			->join("customers", "customers.id", "=", "customer_wallet_transactions.customer_id")
			->leftJoin("trips", "trips.id", "=", "customer_wallet_transactions.trip_id")
			->where('customer_wallet_transactions.is_delete', 0);

		// Filter by customer
		if (!empty($customer)) {
			$query->where('customer_wallet_transactions.customer_id', $customer);
		}

		// Filter by trip
		if (!empty($trip)) {
			$query->where('customer_wallet_transactions.trip_id', $trip);
		}

		// Filter by type
		if (!empty($type)) {
			$query->where('customer_wallet_transactions.type', $type);
		}

		// Filter by search keyword
		if (!empty($search)) {
			$query->where(function ($query) use ($search) {
				$query->where('customers.customer_first_name', 'like', "%{$search}%")
					->orWhere('customers.customer_last_name', 'like', "%{$search}%")
					->orWhere('trips.trip_unique_id', 'like', "%{$search}%")
					->orWhere('customer_wallet_transactions.payment_order_id', 'like', "%{$search}%")
					->orWhere('customer_wallet_transactions.amount', 'like', "%{$search}%")
					->orWhere('customer_wallet_transactions.message', 'like', "%{$search}%")
					->orWhere('customer_wallet_transactions.type', 'like', "%{$search}%")
					->orWhere('customer_wallet_transactions.status', 'like', "%{$search}%");
			});
		}

		// Date range filter (if provided)
		if (!empty($from_date)) {
			$query->whereDate('customer_wallet_transactions.created_at', '>=', Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d'));
		}

		if (!empty($to_date)) {
			$query->whereDate('customer_wallet_transactions.created_at', '<=', Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d'));
		}

		// Sorting
		$query->orderBy('customer_wallet_transactions.id', 'DESC');

		// Get the total count of records
		$total = $query->count();

		// Apply pagination if limit is provided
		if ($limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		// Execute the query
		$result = $query->get();

		return [
			"totalRecords" => $total,
			"result" => $result,
		];
	}
}
