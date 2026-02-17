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

class DriverPaymentHistoryController extends Controller
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
	 * driver payment history list index page
	 * seemashelar@neosao
	 */
	 
	public function index()
	{
		try{
           return view('driver-payment-history.index');
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the driver payment history index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the driver payment history list.');
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
	  try{
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			
			$driver = $r->driver ?? "";
			$from_date=$r->from_date??"";
			$to_date=$r->to_date??"";
            $type=$r->type??"";
			$status=$r->status??"";
			
			$filteredData = DriverEarning::filterPaymentHistory($search, $limit, $offset, $driver,$from_date,$to_date,$type,$status);
			
			$total = $filteredData['totalRecords'];

			$records =  $filteredData['result'];
			
			
			// Check permissions once for all rows
			
			$canView = Auth::guard('admin')->user()->can('Driver Payment History.View');
			
			$showActions = $canView;

			$data = [];
			$srno = $offset + 1;
			if ($records->count() > 0) {
				for ($i = 0; $i < $records->count(); $i++) {
					$row = $records[$i];
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
						if ($canView) {
						  $action .= '<a class="dropdown-item" href="' . url('driver-payment-history/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
						}
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
				   }
				   
				   $dataRow[] =$row->driver_first_name." ".$row->driver_last_name;
				   $dataRow[] =$row->amount;
				   $dataRow[] =$row->type;
				   $dataRow[] =$row->message;
				   $dataRow[] =$row->status;
				   $dataRow[] =$formattedDate;
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
	   }catch (\Exception $ex) {
			// Log the error
            LogHelper::logError('An error occurred while the driver payment history list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
            return response()->json([
                "message" => "An error occurred while fetching the driver payment history list",
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
                LogHelper::logError('An error occurred while view the driver wallet transaction payment history ', 'The invalid driver wallet transaction',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid wallet transaction.');
            }
            return view('driver-payment-history.show', compact('walletTransaction'));
        } catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while view the driver wallet transaction payment history', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view driver wallet transaction payment history.');
        }
	}
	
	public function excel_download(Request $r)
	{
		try {
			$search = "";
			$limit = null;
			$offset = null;

			$driver = $r->driver ?? "";
			$from_date = $r->from_date ?? "";
			$to_date = $r->to_date ?? "";
			$type = $r->type ?? "";
			$status = $r->status ?? "";

			$filteredData = DriverEarning::filterPaymentHistory($search, $limit, $offset, $driver, $from_date, $to_date, $type, $status);
			$records = $filteredData['result'];

			if ($records->isEmpty()) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$formattedDate = "\t" . Carbon::parse($row->created_at)->format('d-m-Y');
				$csvData[] = [
					'Driver Name' => $row->driver_first_name . " " . $row->driver_last_name,
					'Amount' => $row->amount,
					'Type' => $row->type,
					'Message' => $row->message,
					'Status' => $row->status,
					'Date' => $formattedDate, 
				];
			}

			$csvFileName = 'Driver_Payment_History_' . date('d-m-Y') . '.csv';
			$csvFile = fopen('php://temp', 'w+');
			fputcsv($csvFile, array_keys($csvData[0]));

			foreach ($csvData as $row) {
				fputcsv($csvFile, $row);
			}

			rewind($csvFile);
			$csvContent = stream_get_contents($csvFile);
			fclose($csvFile);

			$headers = [
				"Content-Type" => "text/csv",
				"Content-Disposition" => "attachment; filename=$csvFileName",
				"Pragma" => "no-cache",
				"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
				"Expires" => "0",
			];

			return response()->make($csvContent, 200, $headers);
		} catch (\Exception $ex) {
			LogHelper::logError('Error while downloading driver payment history (Excel)', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "Failed to generate CSV."], 500);
		}
	}
	
	
	public function pdf_download(Request $r)
	{
		try {
			$search = "";
			$limit = null;
			$offset = null;

			$driver = $r->driver ?? "";
			$from_date = $r->from_date ?? "";
			$to_date = $r->to_date ?? "";
			$type = $r->type ?? "";
			$status = $r->status ?? "";

			$filteredData = DriverEarning::filterPaymentHistory($search, $limit, $offset, $driver, $from_date, $to_date, $type, $status);
			$records = $filteredData['result'];

			$html = '<style>
						table { width: 100%; border-collapse: collapse; }
						th, td { border: 1px solid black; padding: 5px; font-size: 12px; }
						th { background-color: #f2f2f2; }
					 </style>';

			$html .= '<h3>Partner Payment History</h3>';
			$html .= '<table>
						<thead>
							<tr>
								<th>Partner Name</th>
								<th>Amount</th>
								<th>Type</th>
								<th>Message</th>
								<th>Status</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>';

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');
				$html .= "<tr>
							<td>{$row->driver_first_name} {$row->driver_last_name}</td>
							<td>{$row->amount}</td>
							<td>{$row->type}</td>
							<td>{$row->message}</td>
							<td>{$row->status}</td>
							<td>{$formattedDate}</td>
						  </tr>";
			}

			$html .= '</tbody></table>';

			$pdf = \PDF::loadHTML($html);
			return $pdf->download('Partner_Payment_History_' . date('d-m-Y') . '.pdf');
		} catch (\Exception $ex) {
			LogHelper::logError('Error while downloading driver payment history (PDF)', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "Failed to generate PDF."], 500);
		}
	}
	
	
	public function payout_excel_download(Request $r){
		try{
				$search = "";
			$limit = null;
			$offset = null;

			$driver = $r->driver ?? "";
			$from_date = $r->from_date ?? "";
			$to_date = $r->to_date ?? "";
			$type = $r->type ?? "";
			$status = $r->status ?? "";

			$filteredData = DriverEarning::filterPayout($search, $limit, $offset, $driver, $from_date, $to_date, $type, $status);
			$records = $filteredData['result'];

			if ($records->isEmpty()) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$formattedDate = "\t" . Carbon::parse($row->created_at)->format('d-m-Y');
				
				$csvData[] = [
					'Driver Name' => $row->driver_first_name . " " . $row->driver_last_name,
					'Account Number' => $row->driver_bank_account_number ? '="' . $row->driver_bank_account_number . '"' : "",
					'IFSC'=>$row->driver_bank_ifsc_code??"",
					'Branch Name'=>$row->driver_bank_branch_name??"",
					'Amount' => $row->amount,
					'Status' => $row->status,
					'Date' => $formattedDate, 
				];
			}

			$csvFileName = 'Partner_Payment_History_' . date('d-m-Y') . '.csv';
			$csvFile = fopen('php://temp', 'w+');
			fputcsv($csvFile, array_keys($csvData[0]));

			foreach ($csvData as $row) {
				fputcsv($csvFile, $row);
			}

			rewind($csvFile);
			$csvContent = stream_get_contents($csvFile);
			fclose($csvFile);

			$headers = [
				"Content-Type" => "text/csv",
				"Content-Disposition" => "attachment; filename=$csvFileName",
				"Pragma" => "no-cache",
				"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
				"Expires" => "0",
			];

			return response()->make($csvContent, 200, $headers);
			
		}catch (\Exception $ex) {
			LogHelper::logError('Error while downloading payout (Excel) download', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "Failed to generate CSV."], 500);
		}
	}
	
	
	public function payout_pdf_download(Request $r){
		try{
		    $search = "";
			$limit = null;
			$offset = null;

			$driver = $r->driver ?? "";
			$from_date = $r->from_date ?? "";
			$to_date = $r->to_date ?? "";
			$type = $r->type ?? "";
			$status = $r->status ?? "";

			$filteredData = DriverEarning::filterPayout($search, $limit, $offset, $driver, $from_date, $to_date, $type, $status);
			$records = $filteredData['result'];

			$html = '<style>
						table { width: 100%; border-collapse: collapse; }
						th, td { border: 1px solid black; padding: 5px; font-size: 12px; }
						th { background-color: #f2f2f2; }
					 </style>';

			$html .= '<h3>Payout</h3>';
			$html .= '<table>
						<thead>
							<tr>
								<th>Partner Name</th>
								<th>Account Number</th>
								<th>IFSC</th>
								<th>Branch Name</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>';

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');
				$html .= "<tr>
							<td>{$row->driver_first_name} {$row->driver_last_name}</td>
							<th>{$row->driver_bank_account_number}</td>
							<td>{$row->driver_bank_ifsc_code}</td>
							<td>{$row->driver_bank_branch_name}</td>
							<td>{$row->amount}</td>
							<td>{$row->status}</td>
							<td>{$formattedDate}</td>
						  </tr>";
			}

			$html .= '</tbody></table>';

			$pdf = \PDF::loadHTML($html);
			return $pdf->download('Payout_' . date('d-m-Y') . '.pdf');
		}catch (\Exception $ex) {
			LogHelper::logError('Error while downloading payout (PDF) download', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "Failed to generate CSV."], 500);
		}
	}


	
}