<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class DriverEarning extends Model
{
    use HasFactory;
	protected $table = 'driver_earnings';

    protected $fillable = [
        'trip_id',
        'driver_id',
        'type',
        'message',
        'amount',
        'status',
        'is_active',
        'is_delete',
        'paymentMode',
		'payment_status',
		'payment_response',
		'payment_id',
		'payment_order_id',
		'added_form',
		'minimum_wallet_amount'
    ];
	public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
	//filter incentives function which is used for incentives list
	 public static function filterIncentives(string $search = "", $limit = 0, $offset = 0, string $driver = "")
    {
        $query = self::select("driver_earnings.*", 'driver_first_name','driver_last_name')
            ->join('drivers', 'drivers.id', '=', 'driver_earnings.driver_id')
            ->where('driver_earnings.is_delete',0)
			->whereIn('driver_earnings.type', ['incentives deposit', 'incentives deduction']);
        if ($driver != "") {
            $query->where('driver_earnings.driver_id', $driver);
        }        
        $query->where(function ($query) use ($search) {
            $query->where('driver_first_name', 'like', "%{$search}%")
			     ->orWhere('driver_last_name', 'like', "%{$search}%")
                ->orWhere('driver_earnings.message', 'like', "%{$search}%")
                ->orWhere('driver_earnings.amount', 'like', "%{$search}%");
        })
        ->orderBy('driver_earnings.id', 'DESC');

        $total = $query->count();
        if ($limit && $limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        $result = $query->get();
        return ["totalRecords" => $total, "result" => $result];
    }
	
	
	//filter transaction function which is used for transaction list
	public static function filterTransaction(string $search = "", $limit = 0, $offset = 0, string $driver = "", string $from_date = "", 
    string $to_date = "", string $type = "", string $status = "")
	{
		$query = self::select("driver_earnings.*", 'driver_first_name', 'driver_last_name','driver_wallet')
			->join('drivers', 'drivers.id', '=', 'driver_earnings.driver_id')
			->where('driver_earnings.is_delete', 0)
			->where('driver_earnings.status', "pending")
			->whereIn('driver_earnings.type', ['withdrawal']);

		// Filter by driver
		if (!empty($driver)) {
			$query->where('driver_earnings.driver_id', $driver);
		}

		// Filter by type
		if (!empty($type)) {
			$query->where('driver_earnings.type', $type);
		}

		// Filter by status
		if (!empty($status)) {
			$query->where('driver_earnings.status', $status);
		}

		// Search filter
		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->where('driver_first_name', 'like', "%{$search}%")
				  ->orWhere('driver_last_name', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.type', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.message', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.status', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.amount', 'like', "%{$search}%");
			});
		}

		// Date range filters
		if (!empty($from_date)) {
			$query->whereDate('driver_earnings.created_at', '>=', Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d'));
		}

		if (!empty($to_date)) {
			$query->whereDate('driver_earnings.created_at', '<=', Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d'));
		}

		// This was the syntax issue (was floating before). Fixed now:
		$query->orderBy('driver_earnings.id', 'DESC');

		// Total records count
		$total = $query->count();

		// Pagination
		if ($limit && $limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		// Final result
		$result = $query->get();

		return ["totalRecords" => $total, "result" => $result];
	}
	
	
	//filter transaction function which is used for transaction list
	public static function filterPaymentHistory(string $search = "", $limit = 0, $offset = 0, string $driver = "", string $from_date = "", 
    string $to_date = "", string $type = "", string $status = "")
	{
		$query = self::select("driver_earnings.*", 'driver_first_name', 'driver_last_name','driver_wallet')
			->join('drivers', 'drivers.id', '=', 'driver_earnings.driver_id')
			->where('driver_earnings.is_delete', 0)
			->where('driver_earnings.status',"!=","pending")
			->whereIn('driver_earnings.type', ['withdrawal','deposit','deduction']);

		// Filter by driver
		if (!empty($driver)) {
			$query->where('driver_earnings.driver_id', $driver);
		}

		// Filter by type
		if (!empty($type)) {
			$query->where('driver_earnings.type', $type);
		}

		// Filter by status
		if (!empty($status)) {
			$query->where('driver_earnings.status', $status);
		}

		// Search filter
		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->where('driver_first_name', 'like', "%{$search}%")
				  ->orWhere('driver_last_name', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.type', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.message', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.status', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.amount', 'like', "%{$search}%");
			});
		}

		// Date range filters
		if (!empty($from_date)) {
			$query->whereDate('driver_earnings.created_at', '>=', Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d'));
		}

		if (!empty($to_date)) {
			$query->whereDate('driver_earnings.created_at', '<=', Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d'));
		}

		// This was the syntax issue (was floating before). Fixed now:
		$query->orderBy('driver_earnings.id', 'DESC');

		// Total records count
		$total = $query->count();

		// Pagination
		if ($limit && $limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		// Final result
		$result = $query->get();

		return ["totalRecords" => $total, "result" => $result];
	}
	
	
	
	public static function filterPayout(string $search = "", $limit = 0, $offset = 0, string $driver = "", string $from_date = "", 
    string $to_date = "", string $type = "", string $status = "")
	{
		$query = self::select("driver_earnings.*", 'driver_first_name', 'driver_last_name','driver_wallet','driver_bank_details.driver_bank_account_number','driver_bank_details.driver_bank_ifsc_code','driver_bank_details.driver_bank_branch_name')
			->join('drivers', 'drivers.id', '=', 'driver_earnings.driver_id')
			->join('driver_bank_details', 'driver_bank_details.driver_id', '=', 'driver_earnings.driver_id')
			->where('driver_earnings.is_delete', 0)
			->where('driver_earnings.status',"=","approved")
			->whereIn('driver_earnings.type', ['withdrawal']);

		// Filter by driver
		if (!empty($driver)) {
			$query->where('driver_earnings.driver_id', $driver);
		}

		// Filter by type
		if (!empty($type)) {
			$query->where('driver_earnings.type', $type);
		}

		// Filter by status
		if (!empty($status)) {
			$query->where('driver_earnings.status', $status);
		}

		// Search filter
		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->where('driver_first_name', 'like', "%{$search}%")
				  ->orWhere('driver_last_name', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.type', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.message', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.status', 'like', "%{$search}%")
				  ->orWhere('driver_earnings.amount', 'like', "%{$search}%");
			});
		}

		// Date range filters
		if (!empty($from_date)) {
			$query->whereDate('driver_earnings.created_at', '>=', Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d'));
		}

		if (!empty($to_date)) {
			$query->whereDate('driver_earnings.created_at', '<=', Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d'));
		}

		// This was the syntax issue (was floating before). Fixed now:
		$query->orderBy('driver_earnings.id', 'DESC');

		// Total records count
		$total = $query->count();

		// Pagination
		if ($limit && $limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		// Final result
		$result = $query->get();

		return ["totalRecords" => $total, "result" => $result];
	}


}
