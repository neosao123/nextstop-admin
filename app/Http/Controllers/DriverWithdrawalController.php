<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\DriverEarning;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;
// Helper
use App\Helpers\LogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Classes\Notificationlibv_3;

use App\Models\Setting;

class DriverWithdrawalController extends Controller
{
    protected $user;
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->user = Auth::guard('admin')->user();
			return $next($request);
		});
	}
	
	
	/*
	 * driver earning list index page
	 * seemashelar@neosao
	 */
	 
	public function index()
	{
		try{
			
			$miniBalance=0;
			$getMinimumBalance=Setting::where("id",9)->first();			
			if(!empty($getMinimumBalance)){
				$miniBalance=$getMinimumBalance->setting_value;
			}
           return view('driver-earning.index',compact("miniBalance"));
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the driver earning index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the driver earning list.');
        }
	}
	
	
	/*
	 * get driver list
	 * seemashelar@neosao
	 */
	
	public function get_drivers(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = Driver::where(function ($query) use ($search) {
					$query->where('driver_first_name', 'like', '%' . $search . '%')
						->orWhere('driver_last_name', 'like', '%' . $search . '%');
				})
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					$fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name);
					$html[] = ['id' => $item->id, 'text' => $fullName];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the driver earning list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
    }
  
  

	/*
	 * transaction list
	 * seemashelar@neosao
	 * 
	 */
	 
	public function list(Request $r)
	{
		try {
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			
			$driver = $r->driver ?? "";
			$from_date = $r->from_date ?? "";
			$to_date = $r->to_date ?? "";
			$type = $r->type ?? "";
			$status = $r->status ?? "";
			
			$filteredData = DriverEarning::filterTransaction($search, $limit, $offset, $driver, $from_date, $to_date, $type, $status);
			
			$total = $filteredData['totalRecords'];
			$records = $filteredData['result'];
			
			// Check permissions once for all rows
			$canView = Auth::guard('admin')->user()->can('Driver Earning.View');
			$canwallet = Auth::guard('admin')->user()->can('Driver Earning.Wallet-Transaction');
			$showActions = $canView || $canwallet;

			$data = [];
			$srno = $offset + 1;
			
			if ($records->count() > 0) {
				foreach ($records as $row) {
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y');
					$dataRow = [];
					
					if ($showActions) {
						$action = '
							<span class="text-start">
								<div class="dropdown font-sans-serif position-static">
									<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
										aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
									</button>
									<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
										<div class="bg-white py-2">';
						
						if (Auth::guard('admin')->user()->can('Driver Earning.View')) {
							$action .= '<a class="dropdown-item" href="' . url('driver-earning/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
						}
						
						if (Auth::guard('admin')->user()->can('Driver Earning.Wallet-Transaction')) {
							if ($row->status == "pending") {
								$action .= '<a class="dropdown-item wallet-add" style="cursor: pointer;" data-val="' . $row->driver_wallet . '" data-id="' . $row->id . '" data-driver-id="' . $row->driver_id . '" data-amount="' . $row->amount . '"> <i class="fas fa-rupee-sign"></i> Wallet</a>';
							}
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}
					
					$dataRow[] = $row->driver_first_name . " " . $row->driver_last_name;
					$dataRow[] = $row->amount;
					$dataRow[] = $row->type;
					$dataRow[] = $row->message;
					$dataRow[] = $row->status;
					$dataRow[] = $formattedDate;
					
					$data[] = $dataRow;
					$srno++;
				}
			}
			
			return response()->json([
				"draw" => intval($r->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data
			], 200);
			
		} catch (\Exception $ex) {
			// Log the error
			LogHelper::logError('An error occurred while the driver transaction list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
			return response()->json([
				"message" => "An error occurred while fetching the driver transaction list",
			], 500);
		}
	}
	
	public function driver_withdrawal_operation(Request $r){
		try{
			// Find the driver id
            $driver = Driver::find($r->driverid);
		    

            // Check if the driver exists
            if (!$driver) {
                
				//log error
				LogHelper::logError('An error occurred while withdrawal request operation', 'driver not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->customerid);
				// Return error response
                return response()->json([
                    'success' => false,
                    'error' => 'Driver not found.'
                ]);
            }
			
			if($r->operation=="approved"){
				//withdrawal request is approved
				$DriverEarning = DriverEarning::find($r->requestid);
				$DriverEarning->message=$r->reason;
				$DriverEarning->status="approved";
				$DriverEarning->save();
                
				LogHelper::logSuccess(
					"Driver wallet transaction. Amount deducted: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->driverid
				);
				
				$driver->driver_wallet -= $r->amount;
				$driver->save();
				
				//notification send to driver
				
				
			    $title = "Withdrawal Request";
                $statusMessage = "Rs. {$r->amount} withdrawal request is approved.";
				
				if(!empty($driver->driver_firebase_token)){
					$DeviceIdsArr[] = $driver->driver_firebase_token;
					$dataArr = array();
					$dataArr['device_id'] = $DeviceIdsArr;
					$dataArr['message'] = $statusMessage;
					$dataArr['title'] = $title;					
					$notification['device_id'] = $DeviceIdsArr;
					$notification['message'] = $statusMessage;
					$notification['title'] = $title;					
					$noti = new Notificationlibv_3;
					$result = $noti->sendNotification($dataArr, $notification); 
					Log::info("Wihdrawal request approved notification result", ['result' => $result]); 
					
				}	
				
				LogHelper::logSuccess(
					"Driver wallet updated successfully. Amount deducted: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerid 
				);
			}
			if($r->operation=="rejected"){
				//wallet transaction deduction
                $DriverEarning = DriverEarning::find($r->requestid);
				$DriverEarning->message=$r->reason??"";
				$DriverEarning->status="rejected";
				$DriverEarning->save();
				
				$title = "Withdrawal Request";
                $statusMessage = "Rs. {$r->amount} withdrawal request is rejected.";
				
				if(!empty($driver->driver_firebase_token)){
					$DeviceIdsArr[] = $driver->driver_firebase_token;
					$dataArr = array();
					$dataArr['device_id'] = $DeviceIdsArr;
					$dataArr['message'] = $statusMessage;
					$dataArr['title'] = $title;					
					$notification['device_id'] = $DeviceIdsArr;
					$notification['message'] = $statusMessage;
					$notification['title'] = $title;					
					$noti = new Notificationlibv_3;
					$result = $noti->sendNotification($dataArr, $notification); 
					Log::info("Wihdrawal request rejected notification result", ['result' => $result]); 
					
				}	
				
				
			}
			
		   // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
             LogHelper::logError('An error occurred while update wallet of driver by admin', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
                "message" => "An error occurred while update wallet of driver by admin",
            ], 500);
        }
	}
	
	public function show(string $id)
	{
		try {
            $walletTransaction =DriverEarning::with("driver")
			                   ->where('id', $id)
                               ->where('is_delete', 0)
                               ->first();
			
			if (!$walletTransaction) {
                // Log the error
                LogHelper::logError('An error occurred while view the wallet transaction', 'The invalid wallet Transaction',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid wallet transaction.');
            }
            return view('driver-earning.show', compact('walletTransaction'));
        } catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while view the wallet transaction', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the wallet transaction.');
        }
	}
	
}