<?php

namespace App\Http\Controllers;

use Response;
use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Driver;
use App\Models\DriverEarning;


class IncentivesController extends Controller
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
	
	/**
     * Display a index page of the resource.
     * @author seemashelar@neosao
     */
    public function index()
    {
        try {
            return view('incentives.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the incentives index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the incentives list.');
        }
    }
	
	
	/*
	 * incentives list
	 * seemashelar@neosao
	 * dt: 18-jan-2024
	 */
	 
	public function list(Request $r)
	{
	  try{
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			$driver=$r->driver??"";
			$filteredData =  DriverEarning::filterIncentives($search, $limit, $offset,$driver);

			$total = $filteredData['totalRecords']; 

			$records =  $filteredData['result'];

			$data = [];
			$srno = $offset + 1;
			
			// Check permissions once for all rows
			$canView = Auth::guard('admin')->user()->can('Incentives.Edit');
			$canChangeStatus = Auth::guard('admin')->user()->can('Incentives.View');
			$canDelete = Auth::guard('admin')->user()->can('Incentives.Delete');
			$showActions = $canChangeStatus || $canView || $canDelete;


			
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
						/*if ($this->user->can('Incentives.Edit')) {
								$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('incentives/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
							}*/
						
						if ($this->user->can('Incentives.View')) {
							$action .= '<a class="dropdown-item" href="' . url('incentives/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
						}
											
						if ($this->user->can('Incentives.Delete')) {
							$action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> Delete</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
				 }
				 
				 $dataRow[] = $row->driver_first_name." ".$row->driver_last_name;
				 $dataRow[] = $row->type;
				 $dataRow[] = $row->message;
                 $dataRow[] = $row->amount;
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
	   }catch (\Exception $ex) {
			// Log the error
            LogHelper::logError('An error occurred while the incentives list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
            return response()->json([
                "message" => "An error occurred while fetching the incentives list",
            ], 500);
        }
	}
	
	 /*
	 * incentives add
	 * seemashelar@neosao
	 * dt: 20-jan-2025
	 */
	 
	public function create(Request $r){
		try{
		   
			//incentives add 
		   return view('incentives.add');
		   
		}catch (\Exception $ex) {
		   // Log the error
            LogHelper::logError('An error occurred while create the incentives', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');		
			// Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the incentives add.');
        }
	}
	
	 /*
	 * get driver list for user
	 * seemashelar@neosao
	 * dt: 20-jan-2025
	 */
	
	public function get_drivers(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Driver::where(function ($query) use ($search) {
					$query->where('driver_first_name', 'like', '%' . $search . '%')
						  ->orWhere('driver_last_name', 'like', '%' . $search . '%')
						  ->orWhere('driver_phone', 'like', '%' . $search . '%');
				})
				->where("driver_document_verification_status", 1)
				->where("driver_vehicle_verification_status", 1)
				->where("driver_vehicle_document_verification_status", 1)
				->where("driver_training_video_verification_status", 1)
				->where("is_active",1)
				->where("is_delete",0)
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name.' ( '.$item->driver_phone.' ) ');
				$html[] = ['id' => $item->id, 'text' => $fullName];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the driver list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}
	
	public function store(Request $r){
		try{
			// Define the validation rules
            $rules = [
                'driver' => 'required',                
                'reason' => 'required|string|max:800',               
                'amount' => 'required|numeric|min:1',                
				'operation'=>'required'
            ];

            // Define the custom error messages
            $messages = [
				'driver.required' => 'The driver field is required.',
				'reason.required' => 'The reason field is required.',
				'reason.string' => 'The reason must be a valid string.',
				'reason.max' => 'The reason cannot be more than 800 characters.',
				'amount.required' => 'The amount field is required.',
				'amount.numeric' => 'The amount must be a number.',
				'amount.min' => 'The amount must be at least 1.',				
				'operation.required'=>'Operation is required'
			];
            // Perform validation
            $validator = Validator::make($r->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200); // Respond with validation errors
            }
            $driver = Driver::find($r->driver);
			
			if (!$driver) {
                // Log the error
                LogHelper::logError('An error occurred while add incentives', 'The invalid incentives',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driver);
                
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver.');
            }
			
			if($r->operation=="add"){			
				$driver->driver_wallet += $r->amount; 
				$driver->save();
			    
				//driver wallet
				$driverEarning=new DriverEarning;				
				$driverEarning->driver_id=$r->driver;
				$driverEarning->type="incentives deposit";
				$driverEarning->message=$r->reason;
				$driverEarning->amount=$r->amount;
				$driverEarning->status="success";
				$driverEarning->save(); 
				
				LogHelper::logSuccess('Driver incentives deposited successfully by admin', [
					'driver_id' => $r->driver,
					'amount' => $r->amount,
				], __FUNCTION__, basename(__FILE__), __LINE__);
				
				
				// Return success response
				return response()->json([
					'status' => 200,
					'message' => 'Driver incentives added successfully.',
				], 200); // Respond with success
				
				
			}
            if($r->operation=="sub"){			
				$driver->driver_wallet -= $r->amount;
				$driver->save();
			
			    //driver wallet
				$driverEarning=new DriverEarning;				
				$driverEarning->driver_id=$r->driver;
				$driverEarning->type="incentives deduction";
				$driverEarning->message=$r->reason;
				$driverEarning->amount=$r->amount;
				$driverEarning->status="success";
				$driverEarning->save();
				
				LogHelper::logSuccess('Driver incentives deducted successfully by admin', [
					'driver_id' => $r->driver,
					'amount' => $r->amount,
				], __FUNCTION__, basename(__FILE__), __LINE__);
				
			    // Return success response
				return response()->json([
					'status' => 200,
					'message' => 'Fine has been deducted from the wallet.',
				], 200); // Respond with success
				
			}		
			
		}catch (\Exception $ex) {
			LogHelper::logError('An error occurred while store incentives', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}
	
	/**
 * Show the form for editing the specified DriverEarning.
 *
 */
public function edit(string $id)
{
    try {
        $wallet = DriverEarning::where('id', $id)->where('is_delete', 0)->first();
        
        if (!$wallet) {
            // Log the error
            LogHelper::logError('An error occurred while editing the driver earning', 'The invalid driver earning',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            // Return error response to the user
            return redirect()->back()->with('error', 'The invalid driver earning.');
        }
        
        return view('incentives.edit', compact('wallet'));
    } catch (\Exception $ex) {
        // Log the error
        LogHelper::logError('An error occurred while editing the driver earning', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        // Return error response to the user
        return redirect()->back()->with('error', 'An error occurred while editing the driver earning.');
    }
}

public function update(Request $r){
	try{
		// Define the validation rules
            $rules = [             
                'reason' => 'required|string|max:800',               
                'amount' => 'required|numeric|min:1',                
				'operation'=>'required'
            ];

            // Define the custom error messages
            $messages = [
				'driver.required' => 'The driver field is required.',
				'reason.required' => 'The reason field is required.',
				'reason.string' => 'The reason must be a valid string.',
				'reason.max' => 'The reason cannot be more than 800 characters.',
				'amount.required' => 'The amount field is required.',
				'amount.numeric' => 'The amount must be a number.',
				'amount.min' => 'The amount must be at least 1.',				
				'operation.required'=>'Operation is required'
			];
            // Perform validation
            $validator = Validator::make($r->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200); // Respond with validation errors
            }
            $driverEarning = DriverEarning::find($r->id);
			
			if($driverEarning){
				$driver=Driver::find($driverEarning->driver_id);
			    if($driver){
					if($driverEarning->type=="incentives deposit")
					{
						$driver->driver_wallet -= $driverEarning->amount;
				        $driver->save();
						
						// Success message for the subtract (revert) operation
						LogHelper::logSuccess('Driver incentives reverted successfully by admin', [
							'driver_id' => $driverEarning->driver_id,
							'amount' => $driverEarning->amount,
						], __FUNCTION__, basename(__FILE__), __LINE__);
									
					}
					if($driverEarning->type=="incentives deduction")
					{
						$driver->driver_wallet += $driverEarning->amount;
				        $driver->save();
						
						// Success message for the add (revert) operation
						LogHelper::logSuccess('Driver incentives reverted successfully by admin', [
							'driver_id' => $driverEarning->driver_id,
							'amount' => $driverEarning->amount,
						], __FUNCTION__, basename(__FILE__), __LINE__);
					}
				}			
			}
			
			if($r->operation=="add"){
				$driver->driver_wallet += $r->amount;
				$driver->save();
				
				$driverEarning->driver_id=$driverEarning->driver_id;
				$driverEarning->type="incentives deposit";
				$driverEarning->message=$r->reason;
				$driverEarning->amount=$r->amount;
				$driverEarning->status="success";
				$driverEarning->save(); 
				
				LogHelper::logSuccess('Driver incentives deposited successfully by admin', [
					'driver_id' => $driverEarning->driver_id,
					'amount' => $r->amount,
				], __FUNCTION__, basename(__FILE__), __LINE__);
				
				
				// Return success response
				return response()->json([
					'status' => 200,
					'message' => 'Driver incentives deposited successfully..',
				], 200); // Respond with success
			
			}
			if($r->operation=="sub"){
				$driver->driver_wallet -= $r->amount;
				$driver->save();
				
				
				$driverEarning->driver_id=$driverEarning->driver_id;
				$driverEarning->type="incentives deduction";
				$driverEarning->message=$r->reason;
				$driverEarning->amount=$r->amount;
				$driverEarning->status="success";
				$driverEarning->save();
				
				LogHelper::logSuccess('Driver incentives deducted successfully by admin', [
					'driver_id' => $driverEarning->driver_id,
					'amount' => $r->amount,
				], __FUNCTION__, basename(__FILE__), __LINE__);
				
			    // Return success response
				return response()->json([
					'status' => 200,
					'message' => 'Driver incentives deduct successfully.',
				], 200); // Respond with success
			}
			
			
	}catch (\Exception $ex) {
        // Log the error
        LogHelper::logError('An error occurred while updating the driver earning', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        // Return error response to the user
        return redirect()->back()->with('error', 'An error occurred while updating the driver earning.');
    }
}


/**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    try {
        // Find the driver earning by ID
        $driverEarning = DriverEarning::find($id);

        // Check if the driver earning exists
        if (!$driverEarning) {
            LogHelper::logError('An error occurred while deleting the driver earning', 'Driver earning not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'Driver earning not found.']);
        }

        // Soft delete the driver earning by setting is_delete flag (if you want soft delete)
        $driverEarning->is_delete = 1;
        $driverEarning->save();

        if($driverEarning){
			$driver=Driver::find($driverEarning->driver_id);
			if($driver){
				if($driverEarning->type=="incentives deposit")
				{
					$driver->driver_wallet -= $driverEarning->amount;
					$driver->save();
					
					// Success message for the subtract (revert) operation
					LogHelper::logSuccess('Driver incentives reverted successfully by admin', [
						'driver_id' => $driverEarning->driver_id,
						'amount' => $driverEarning->amount,
					], __FUNCTION__, basename(__FILE__), __LINE__);
								
				}
				if($driverEarning->type=="incentives deduction")
				{
					$driver->driver_wallet += $driverEarning->amount;
					$driver->save();
					
					// Success message for the add (revert) operation
					LogHelper::logSuccess('Driver incentives reverted successfully by admin', [
						'driver_id' => $driverEarning->driver_id,
						'amount' => $driverEarning->amount,
					], __FUNCTION__, basename(__FILE__), __LINE__);
				}
			}			
		}
		
        // Return success response
        return response()->json(['success' => true]);

    } catch (\Exception $ex) {
        // Log the error and return error response
        LogHelper::logError('An error occurred while deleting the driver earning', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        return response()->json(['success' => false, 'error' => 'An error occurred while deleting the driver earning.']);
    }
}

	/**
 * Show the form for view the specified DriverEarning.
 *
 */
public function show(string $id)
{
    try {
        $wallet = DriverEarning::where('id', $id)->where('is_delete', 0)->first();
        
        if (!$wallet) {
            // Log the error
            LogHelper::logError('An error occurred while showing the driver earning', 'The invalid driver earning',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            // Return error response to the user
            return redirect()->back()->with('error', 'The invalid driver earning.');
        }
        
        return view('incentives.show', compact('wallet'));
    } catch (\Exception $ex) {
        // Log the error
        LogHelper::logError('An error occurred while showing the driver earning', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        // Return error response to the user
        return redirect()->back()->with('error', 'An error occurred while showing the driver earning.');
    }
}


public function pdf_download(Request $r)
{
    try {
        $driver = $r->driver ?? "";
        $search = $r->input('search.value') ?? "";
        $limit = $r->length ?? 0;
        $offset = $r->start ?? 0;

        $filteredData = DriverEarning::filterIncentives($search, $limit, $offset, $driver);
        $records = $filteredData['result'];

        if ($records->isEmpty()) {
            return response()->json(["message" => "No data available for download."], 204);
        }

        $htmlContent = '<style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        table, th, td {
                            border: 1px solid black;
                            padding: 4px;
                            font-size: 12px;
                            text-align: left;
                        }
                        .badge-soft-success {
                            color: #28a745;
                        }
                        .badge-soft-danger {
                            color: #dc3545;
                        }
                    </style>';
                    
        $htmlContent .= '<table>';
        $htmlContent .= '<thead>
                            <tr>
                                <th>Driver Name</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Amount</th>
                                <th>Created At</th>
                            </tr>
                        </thead>';
        $htmlContent .= '<tbody>';

        foreach ($records as $row) {
            $createdDate = Carbon::parse($row->created_at)->format('d-m-Y');

            $htmlContent .= '<tr>';
            $htmlContent .= '<td>' . $row->driver_first_name . " " . $row->driver_last_name . '</td>';
            $htmlContent .= '<td>' . $row->type . '</td>';
            $htmlContent .= '<td>' . $row->message . '</td>';
            $htmlContent .= '<td>' . $row->amount . '</td>';
            $htmlContent .= '<td>' . $createdDate . '</td>';
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody></table>';

        $pdf = PDF::loadHTML($htmlContent);
        return $pdf->download('Incentives.pdf');
        
    } catch (\Exception $ex) {
        // Log the error and return a response with error message
        LogHelper::logError('An error occurred while downloading the incentives list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        return response()->json(["message" => "An error occurred while generating the PDF file."], 500);
    }
}
public function excel_download(Request $r)
{
    try {
        // Parameters for pagination and filtering
        $limit = $r->length??0;
        $offset = $r->start??0;
        $search = $r->input('search.value') ?? "";
        $driver = $r->driver ?? "";

        // Fetch the filtered incentives data
        $filteredData = DriverEarning::filterIncentives($search, $limit, $offset, $driver);
        $records = $filteredData['result'];

        if ($records->count() == 0) {
            return response()->json(["message" => "No data available for download."], 204);
        }

        // Prepare the CSV data array
        $csvData = [];
        foreach ($records as $row) {
            $carbonDate = Carbon::parse($row->created_at);
            $formattedDate = $carbonDate->format('d-m-Y');

            $csvData[] = [
                'Driver Name' => $row->driver_first_name . " " . $row->driver_last_name,
                'Type' => $row->type,
                'Message' => $row->message,
                'Amount' => $row->amount,
                'Created At' => $formattedDate,
            ];
        }

        // Create CSV file in memory
        $csvFileName = 'Incentives_' . date('d-m-Y') . '.csv';
        $csvFile = fopen('php://temp', 'w+');
        fputcsv($csvFile, array_keys($csvData[0])); // Add headers

        foreach ($csvData as $row) {
            fputcsv($csvFile, $row);
        }

        rewind($csvFile);
        $csvContent = stream_get_contents($csvFile);
        fclose($csvFile);

        // Set response headers for CSV download
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        return response()->make($csvContent, 200, $headers);
        
    } catch (\Exception $ex) {
        // Log the error and return response with error message
        LogHelper::logError('An error occurred while downloading the incentives list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        return response()->json([
            "message" => "An error occurred while generating the CSV file.",
        ], 500);
    }
}


}