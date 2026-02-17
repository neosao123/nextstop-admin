<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\CustomerWalletTransaction;
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

class CustomerController extends Controller
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
	 * customer list index page
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	 
	public function index()
	{
		try{
           return view('customer.index');
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the customer index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the customer list.');
        }
	}
	
	/*
	 * customer list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	 
	public function list(Request $request)
	{
		try {
			$limit = $request->length;
			$offset = $request->start;
			$search = $request->input('search.value') ?? "";
			$customer = $request->customer ?? "";
			$phone_number = $request->phone_number ?? "";
			$account_status = $request->account_status ?? "";
			$email = $request->email ?? "";
			
			$filteredData = Customer::filterCustomer($search, $limit, $offset, $customer, $phone_number, $email, $account_status);
			$total = $filteredData['totalRecords'];
			$records = $filteredData['result'];
			$data = [];

			// Check permissions once for all rows
			$canView = Auth::guard('admin')->user()->can('Customer.View');
			$canEdit = Auth::guard('admin')->user()->can('Customer.Edit');
			$canDelete = Auth::guard('admin')->user()->can('Customer.Delete');
			$canBlock = Auth::guard('admin')->user()->can('Customer.Block');
			$canRating = Auth::guard('admin')->user()->can('Customer.Rating');
			$canWalletTransaction = Auth::guard('admin')->user()->can('Customer.Wallet-Transaction');
			$canWallet = Auth::guard('admin')->user()->can('Customer.Wallet');
			
			$showActions = $canView || $canEdit || $canDelete || $canBlock || $canRating || $canWalletTransaction || $canWallet;

			foreach ($records as $row) {
				$dataRow = [];
				
				// Action column (only if user has any permissions)
				if ($showActions) {
					$actionButtons = [];
					
					if ($canView) {
						$actionButtons[] = '<a class="dropdown-item" href="' . url('customers/' . $row->id) . '"><i class="fas fa-eye"></i> View</a>';
					}
					
					if ($row->is_customer_delete == 0) {
						if ($canEdit) {
							$actionButtons[] = '<a class="dropdown-item btn-edit" href="' . url('customers/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i> Edit</a>';
						}
						if ($canDelete) {
							$actionButtons[] = '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"><i class="far fa-trash-alt"></i> Delete</a>';
						}
						if ($canBlock) {
							$blockText = $row->is_block ? '<i class="fas fa-angle-down"></i> Un-Block' : '<i class="fas fa-angle-up"></i> Block';
							$actionButtons[] = '<a class="dropdown-item btn-block" style="cursor: pointer;" data-id="' . $row->id . '" data-val="' . $row->is_block . '">' . $blockText . '</a>';
						}
					}
					
					if ($canRating) {
						$actionButtons[] = '<a class="dropdown-item" href="' . url('customers/rating/' . $row->id) . '"><i class="fas fa-star"></i> Rating</a>';
					}
					
					if ($canWalletTransaction) {
						$actionButtons[] = '<a class="dropdown-item" href="' . url('customers/wallet-transaction/' . $row->id) . '"><i class="fas fa-rupee-sign"></i> Wallet Transaction</a>';
					}
					
					if ($canWallet) {
						$actionButtons[] = '<a class="dropdown-item wallet-add" style="cursor: pointer;" data-val="' . ($row->customer_wallet_balance ?? 0) . '" data-id="' . $row->id . '"><i class="fas fa-rupee-sign"></i> Wallet</a>';
					}

					$action = '<span class="text-start">
						<div class="dropdown font-sans-serif position-static">
							<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
								aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
							</button>
							<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-' . $row->id . '">
								<div class="bg-white py-2">' . implode('', $actionButtons) . '</div>
							</div>
						</div>
					</span>';

					$dataRow[] = $action;
				}

				// Customer name
				$dataRow[] = $row->customer_first_name . " " . $row->customer_last_name;
				
				// Email
				$dataRow[] = $row->customer_email;
				
				// Phone
				$dataRow[] = $row->customer_phone;
				
				// Wallet balance
				$dataRow[] = $row->customer_wallet_balance ?? 0;
				
				// Referral wallet
				$dataRow[] = $row->customer_referral_wallet ?? 0;
				
				// Referral code
				$dataRow[] = $row->customer_referral_code ?? "";
				
				// Referred customer
				$dataRow[] = $row->referred_customer_first_name . " " . $row->referred_customer_last_name;
				
				// Status
				$status = $row->is_customer_delete == 1 
					? '<div><span class="badge rounded-pill badge-soft-dark">' . __('index.deleted') . '</span></div>'
					: ($row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">' . __('index.active') . '</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.in_active') . '</span></div>');
				$dataRow[] = $status;
				
				// Block status
				$blockstatus = $row->is_block == 1 
					? '<div><span class="badge rounded-pill badge-soft-success">Yes</span></div>'
					: '<div><span class="badge rounded-pill badge-soft-danger">No</span></div>';
				$dataRow[] = $blockstatus;

				$data[] = $dataRow;
			}

			return response()->json([
				"draw" => intval($request->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data
			], 200);
			
		} catch (\Exception $ex) {
			LogHelper::logError(
				'An error occurred while fetching the customer list', 
				$ex->getMessage(), 
				__FUNCTION__, 
				basename(__FILE__), 
				__LINE__, 
				request()->path(), 
				Auth::guard('admin')->user()->id ?? null
			);
			return response()->json([
				"message" => "An error occurred while fetching the customer list",
			], 500);
		}
	}
	
	
	/*
	 * get customer list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	
	public function get_customers(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = Customer::where(function ($query) use ($search) {
					$query->where('customer_first_name', 'like', '%' . $search . '%')
						->orWhere('customer_last_name', 'like', '%' . $search . '%');
				})
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					$fullName = trim($item->customer_first_name . ' ' . $item->customer_last_name);
					$html[] = ['id' => $item->id, 'text' => $fullName];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the customers list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
  }
  
    
    /*
	 * get email list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	
	public function get_email(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = Customer::where(function ($query) use ($search) {
					$query->where('customer_email', 'like', '%' . $search . '%');
				})
				
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					
					$html[] = ['id' => $item->id, 'text' => $item->customer_email];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the customers email list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
  }
  
    /*
	 * get mobile list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	
	public function get_mobile(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = Customer::where(function ($query) use ($search) {
					$query->where('customer_phone', 'like', '%' . $search . '%');
				})
				
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					
					$html[] = ['id' => $item->id, 'text' => $item->customer_phone];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the customers phone list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
   }
   
   
   /*
	 * unique id list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */
	public function get_trips(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Trip::where('trip_unique_id', 'like', '%' . $search . '%')
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$html[] = ['id' => $item->id, 'text' => $item->trip_unique_id];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the unique list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}
	
	
	/*
	 * vehicle list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
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
  
    //excel downloading
    public function excel_download(Request $r)
	{
		try {
			$customer = $r->customer ?? "";
			$phone_number = $r->phone_number ?? "";
			$email = $r->email ?? "";
			$search = "";
			$limit = null; // No limit for Excel export
			$offset = null; // No offset for Excel export
            $account_status=$r->account_status??"";
			// Fetch the filtered customer data
			$filteredData = Customer::filterCustomer($search, $limit, $offset, $customer, $phone_number, $email,$account_status);
			$records = $filteredData['result'];

			if ($records->isEmpty()) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');
				
				if ($row->is_customer_delete == 1) {
						$status = 'Deleted';
				} else {
					$status = $row->is_active == 1
						? 'Active'
						: 'In-Active>';
				}
				
				
				$blockStatus = $row->is_block ? 'Yes' : 'No';
				$csvData[] = [
					'Full Name' => $row->customer_first_name . " " . $row->customer_last_name,
					'Email' => $row->customer_email,
					'Phone Number' => $row->customer_phone,
					'Wallet'=>$row->customer_wallet_balance??0,
					'Referral Wallet'=>$row->customer_referral_wallet??0,
					'Referral Code'=>$row->customer_referral_code??"",
					'Refer By'=>$row->referred_customer_first_name." ".$row->referred_customer_last_name, 
					'Status' => $status,
					'Blocked' => $blockStatus,
					'Created At' => $formattedDate,
				];
			}

			$csvFileName = 'Customers_' . date('d-m-Y') . '.csv';
			$csvFile = fopen('php://temp', 'w+');
			fputcsv($csvFile, array_keys($csvData[0])); // Add headers

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
			LogHelper::logError('An error occurred while downloading the customer list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while generating the CSV file.",
			], 500);
		}
	}
	
	//pdf downloading
	public function pdf_download(Request $r)
	{
		$customer = $r->customer ?? "";
		$phone_number = $r->phone_number ?? "";
		$email = $r->email ?? "";
		$account_status=$r->account_status??"";
		$search = "";
		$limit = null; // No limit for PDF export
		$offset = null; // No offset for PDF export

		$filteredData = Customer::filterCustomer($search, $limit, $offset, $customer, $phone_number, $email,$account_status);
		$records = $filteredData['result'];

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
								<th>Full Name</th>
								<th>Email</th>
								<th>Phone Number</th>
								<th>Wallet</th>
								<th>Referral Code</th>
								<th>Refer By</th>
								<th>Status</th>
								<th>Blocked</th>
								<th>Created At</th>
							</tr>
						</thead>';
		$htmlContent .= '<tbody>';

		foreach ($records as $row) {
			$createdDate = Carbon::parse($row->created_at)->format('d-m-Y');
			
			$status = '';
			if ($row->is_customer_delete == 1) {
				$status = '<span class="badge rounded-pill badge-soft-dark">' . __('index.deleted') . '</span>';
			} else {
				$status = $row->is_active == 1
					? '<span class="badge rounded-pill badge-soft-success">' . __('index.active') . '</span>'
					: '<span class="badge rounded-pill badge-soft-danger">' . __('index.in_active') . '</span>';
			}
			
			//$status = $row->is_active ? '<span class="badge-soft-success">Active</span>' : '<span class="badge-soft-danger">In-Active</span>';
			$blockstatus = $row->is_block ? '<span class="badge-soft-success">Yes</span>' : '<span class="badge-soft-danger">No</span>';

			$htmlContent .= '<tr>';
			$htmlContent .= '<td>' . $row->customer_first_name . " " . $row->customer_last_name . '</td>';
			$htmlContent .= '<td>' . $row->customer_email . '</td>';
			$htmlContent .= '<td>' . $row->customer_phone . '</td>';
			$htmlContent .= '<td>' . $row->customer_wallet_balance . '</td>';
			$htmlContent .= '<td>' . $row->customer_referral_code . '</td>';
			$htmlContent .= '<td>' . $row->referred_customer_first_name . " " . $row->referred_customer_last_name . '</td>';
			$htmlContent .= '<td>' . $status . '</td>';
			$htmlContent .= '<td>' . $blockstatus . '</td>';
			$htmlContent .= '<td>' . $createdDate . '</td>';
			$htmlContent .= '</tr>';
		}

		$htmlContent .= '</tbody></table>';

		$pdf = PDF::loadHTML($htmlContent);

		return $pdf->download('Customers.pdf');
	}


	
	 /*
	 * block unblock customer
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	
	 public function block_unblock_customer(String $id){
		try {
			// Find the user by ID
			$customer = Customer::find($id);

			// Check if the customer exists
			if (!$customer) {
				
				// Log the error
                LogHelper::logError('An error occurred while block-unblock the customer', 'The invalid customer',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
				//return error response
				return response()->json([
					'success' => false,
					'error' => 'Customer not found.'
				]);
			}

			// Toggle block/unblock status
			if ($customer->is_block == 0) {
				// Block the customer
				$customer->is_block = 1;
				$customer->is_active = 0;
			} else {
				// Unblock the customer
				$customer->is_block = 0;
				$customer->is_active = 1;
			}

			// Save customer status
			if ($customer->save()) {
				
			//success log
			LogHelper::logSuccess('Customer block/unblock action successful.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $customer->id);
			//return response to js function
			return response()->json(['success' => true]);
			} else {
				
				// Log the error
                LogHelper::logError('An error occurred while block-unblock the customer', 'Failed to save customer block/unblock status.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
			    //Return resposne to user
				return response()->json(['success' => false, 'error' => 'Failed to update customer status.']);
			}
		} catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while block/unblock customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			 //Return resposne to user
			return redirect()->back()->with('error', 'An error occurred while block/unblock customer.');
		}
	}
	
	
	 /*
	 * remove customer
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	public function destroy(string $id)
    {
        try {
            // Find the customer id
            $customer = Customer::find($id);

            // Check if the customer exists
            if (!$customer) {
                
				//log error
				LogHelper::logError('An error occurred while deleting the customer', 'Customer not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
				// Return error response
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found.'
                ]);
            }

            // Soft delete the customer by setting is_delete flag
            $customer->is_delete = 1;
            $customer->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
             LogHelper::logError('An error occurred while deleting the customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
                "message" => "An error occurred while deleting the customer",
            ], 500);
        }
    }
	
	
	 /* show customer
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	public function show(string $id)
	{
		try {
            $customer = Customer::select(
					"customers.*", 
					"referred_customer.customer_first_name as referred_customer_first_name", 
					"referred_customer.customer_last_name as referred_customer_last_name", 
					"referred_customer.customer_referral_code as referred_customer_code"
				)
				->leftJoin('customers as referred_customer', 'customers.customer_referral_by_id', '=', 'referred_customer.id')
				->where('customers.id', $id)  // Corrected this line
				->where('customers.is_delete', 0)  // Added chaining for 'is_delete'
				->first();
			
			if (!$customer) {
                // Log the error
                LogHelper::logError('An error occurred while view the customer', 'The invalid customer',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid customer.');
            }
            return view('customer.show', compact('customer'));
        } catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while view the customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the customer.');
        }
	}
	
	
	 /*
	 * edit customer
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	public function edit(string $id)
	{
		try {
            $customer =Customer::where('id', $id)->where('is_delete', 0)->first();
			
			if (!$customer) {
                // Log the error
                LogHelper::logError('An error occurred while edit the customer', 'The invalid customer',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid customer.');
            }
            return view('customer.edit', compact('customer'));
        } catch (\Exception $ex) {            
            // Log the error
             LogHelper::logError('An error occurred while edit the customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the customer.');
        }
	}
	
	 /*
	 * update customer
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	 
	public function update(Request $request, string $id)
	{
		try {
			$validator = Validator::make($request->all(), [
				'customer_first_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'customer_last_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'customer_phone' => [
					'required',
					'digits:10',
					'numeric',
					Rule::unique('customers')->where(function ($query) use ($id) {
						return $query->where('is_delete', 0)
							->where('id', '!=', $id)
							->where('is_active', 1);
					}),
				],
				'customer_email' => [
					'required',
					'email',
					'min:2',
					'max:150',
					'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
					Rule::unique('customers')->where(function ($query) use ($id) {
						return $query->where('is_delete', 0)
							->where('id', '!=', $id)
							->where('is_active', 1);
					}),
				],
				'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			$customer = Customer::find($id);
			if (!$customer) {
				return redirect()->back()->with('error', 'Invalid customer.');
			}

			$customer->customer_first_name = $request->customer_first_name;
			$customer->customer_last_name = $request->customer_last_name;
			$customer->customer_phone = $request->customer_phone;
			$customer->customer_email = $request->customer_email;
			$customer->is_active = $request->has('is_active') ? 1 : 0;

			if ($request->hasFile('avatar')) {
				$file = $request->file('avatar');
				$imageName = 'customer-' . time() . '.' . $file->getClientOriginalExtension();
				$path = Storage::disk('public')->putFileAs('customers', $file, $imageName);
				$customer->customer_avatar = $path;
			}

			$customer->save();

			return redirect('customers')->with('success', 'Customer updated successfully.');
			
		     //success log
			LogHelper::logSuccess('The customer updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $customer->id);
			
		} catch (\Exception $ex) {
			// Log the error
             LogHelper::logError('An error occurred while updating the customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			
			return redirect()->back()->with('error', 'An error occurred while updating the customer.');
		}
	}

     /*
	 * delete customer profile image
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	 
	
    public function delete_avatar(Request $r)
    {
        try{
			$customer = Customer::find($r->id);
			
			if (!$customer) {                
				 // Log the error
				 LogHelper::logError('An error occurred while delete the customer avatar', 'The invalid customer',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->id);
				 // Return error response to the user
					return redirect()->back()->with('error', 'The invalid customer.');
            }
			
			
			Storage::disk('public')->delete($customer->customer_avatar);
			$customer->update(['customer_avatar' => null]);
            //success log			
			LogHelper::logSuccess('The avatar image deleted successfully',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->id);
			//return response
			return redirect('customers/'.$r->id.'/edit')->with('success', 'Avatar deleted successfully');
		}catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while deleting the avatar image of customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while delete customer avatar.');
        }
    }
	
	
    /*
	 * rating list index page
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */
	 
	public function rating_index(Request $r)
	{
		try{
		   $customerId=$r->id;
           return view('customer.rating',compact('customerId'));
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the customer rating page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the customer rating list.');
        }
	}
	
	
	/*
	 * rating list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */
	 
	public function rating_list(Request $r)
	{
	  try{
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			
			$customer = $r->customer ?? "";
			$trip=$r->trip ??"";
			$driver=$r->driver??"";
			
			$filteredData =  Customer::filterRating($search, $limit, $offset, $customer, $trip,$driver);

			$total = $filteredData['totalRecords'];

			$records =  $filteredData['result'];

			$data = [];
			$srno = $offset + 1;
			if ($records->count() > 0) {
				for ($i = 0; $i < $records->count(); $i++) {
					$row = $records[$i];
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y');

					$data[] = [
						$row->customer_first_name." ".$row->customer_last_name, 
						$row->driver_first_name." ".$row->driver_last_name, 
						$row->trip_unique_id,
						$row->rating_value,
						$row->rating_description,
						$formattedDate
					];
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
            LogHelper::logError('An error occurred while the customer rating list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
            return response()->json([
                "message" => "An error occurred while fetching the customer rating list",
            ], 500);
        }
	}
	
	
    /*
	 * transaction list index page
	 * seemashelar@neosao
	 * dt: 6-jan-2025
	 */
	 
	public function transaction_index(Request $r)
	{
		try{
		   $customerId=$r->id;
           return view('customer.transaction',compact('customerId'));
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the customer transaction page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the customer transaction list.');
        }
	}
	
	
	/*
	 * transaction list
	 * seemashelar@neosao
	 * dt: 6-jan-2025
	 */
	 
	public function transaction_list(Request $r)
	{
	  try{
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			
			$customer = $r->customer ?? "";
			$trip=$r->trip ??"";
			$from_date=$r->from_date??"";
			$to_date=$r->to_date??"";
            $type=$r->type??"";
			$filteredData =  Customer::filterWalletTransaction($search, $limit, $offset, $customer, $trip,$from_date,$to_date,$type);

			$total = $filteredData['totalRecords'];

			$records =  $filteredData['result'];

			$data = [];
			$srno = $offset + 1;
			if ($records->count() > 0) {
				for ($i = 0; $i < $records->count(); $i++) {
					$row = $records[$i];
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y');

                    $action = '
						<span class="text-start">
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
									<div class="bg-white py-2">';
					
					$action .= '<a class="dropdown-item" href="' . url('customers/wallet-transaction/show/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
					
					$action .= '</div></div></div></span>';
					$data[] = [
					     $action,
						$row->customer_first_name." ".$row->customer_last_name, 
						$row->trip_unique_id,
						$row->payment_order_id,
						$row->amount,
						$row->type,
						$row->message,
						$row->status,
						$formattedDate,
                       
					];
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
            LogHelper::logError('An error occurred while the customer transaction list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
            return response()->json([
                "message" => "An error occurred while fetching the customer transaction list",
            ], 500);
        }
	}

     /* show wallet transaction
	 * seemashelar@neosao
	 * dt: 1-jan-2024
	 */
	public function show_wallet_transaction(string $id)
	{
		try {
            $walletTransaction =CustomerWalletTransaction::where('id', $id)
                               ->where('is_delete', 0)
                               ->first();
			
			if (!$walletTransaction) {
                // Log the error
                LogHelper::logError('An error occurred while view the wallet transaction', 'The invalid wallet Transaction',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid wallet transaction.');
            }
            return view('customer.viewtransaction', compact('walletTransaction'));
        } catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while view the wallet transaction', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the wallet transaction.');
        }
	} 
	
	
	public function wallet_operation(Request $r){
		try{
			// Find the customer id
            $customer = Customer::find($r->customerid);

            // Check if the customer exists
            if (!$customer) {
                
				//log error
				LogHelper::logError('An error occurred while deleting the customer', 'Customer not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->customerid);
				// Return error response
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found.'
                ]);
            }
			
			if($r->operation=="deposit"){
				//wallet transaction deposit
				$walletTransaction=new CustomerWalletTransaction;
				$walletTransaction->customer_id=$r->customerid;
				$walletTransaction->type="deposit";
				$walletTransaction->message=$r->reason;
				$walletTransaction->amount=$r->amount;
				$walletTransaction->status="success";
				$walletTransaction->save();
                
				LogHelper::logSuccess(
					"Customer wallet transaction. Amount deposit: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerid
				);
				
				$customer->customer_wallet_balance += $r->amount;
				$customer->save();
				
				LogHelper::logSuccess(
					"Customer wallet updated successfully. Amount deposit: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerid 
				);
			}
			if($r->operation=="deduction"){
				//wallet transaction deduction
                $walletTransaction=new CustomerWalletTransaction;
				$walletTransaction->customer_id=$r->customerid;
				$walletTransaction->type="deduction";
				$walletTransaction->message=$r->reason;
				$walletTransaction->amount=$r->amount;
				$walletTransaction->status="success";
				$walletTransaction->save();

				LogHelper::logSuccess(
					"Customer wallet transaction. Amount deducted: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerid
				);

                $customer->customer_wallet_balance -= $r->amount;
				$customer->save();
				
				LogHelper::logSuccess(
					"Customer wallet updated successfully. Amount deducted: {$r->amount}",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerid 
				);
			}
			
		   // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
             LogHelper::logError('An error occurred while update wallet of customer by admin', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
                "message" => "An error occurred while update wallet of customer by admin",
            ], 500);
        }
	}
	
	
	
}
