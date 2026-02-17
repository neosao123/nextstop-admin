<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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
use Spatie\Permission\Models\Permission;
class UserController extends Controller
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
	 * user list index page
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
	 
	public function index()
	{
		try{
           return view('user.index');
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the user index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the user list.');
        }
	}
	
	/*
	 * user list
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
	 
	public function list(Request $r)
	{
		try {
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			$user = $r->user ?? "";
			$role = $r->role ?? "";
			
			$filteredData = User::filterUser($search, $limit, $offset, $user, $role);
			$total = $filteredData['totalRecords'];
			$records = $filteredData['result'];

			$data = [];
			$srno = $offset + 1;

			// Check if user has any action permissions
			$hasActionPermissions = $this->user->canany([
				'User.View',
				'User.Edit',
				'User.Permissions',
				'User.Delete',
				'User.Block'
			]);

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');

				// Status badges
				$status = $row->is_active == 1 
					? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
					: '<div><span class="badge rounded-pill badge-soft-danger">In-Active</span></div>';

				$blockstatus = $row->is_block == 1
					? '<div><span class="badge rounded-pill badge-soft-success">Yes</span></div>'
					: '<div><span class="badge rounded-pill badge-soft-danger">No</span></div>';

				// Action dropdown
				$action = '';
				if ($hasActionPermissions) {
					$action = '
					<span class="text-start">
						<div class="dropdown font-sans-serif position-static">
							<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" 
								id="user-dropdown-'.$row->id.'" data-bs-toggle="dropdown" 
								data-boundary="window" aria-haspopup="true" aria-expanded="false">
								<span class="fas fa-ellipsis-h fs--1"></span>
							</button>
							<div class="dropdown-menu dropdown-menu-end border py-0" 
								aria-labelledby="user-dropdown-'.$row->id.'">
								<div class="bg-white py-2">';

					if ($this->user->can('User.View')) {
						$action .= '<a class="dropdown-item" href="'.url('users/'.$row->id).'">
									<i class="fas fa-eye"></i> View</a>';
					}
					if ($this->user->can('User.Edit')) {
						$action .= '<a class="dropdown-item btn-edit" href="'.url('users/'.$row->id.'/edit').'">
									<i class="fas fa-edit"></i> Edit</a>';
					}
					if ($this->user->can('User.Permissions')) {
						$action .= '<a class="dropdown-item" href="'.url('user/'.$row->id.'/permissions').'">
									<i class="fas fa-check-double"></i> Permissions</a>';
					}
					if ($this->user->can('User.Delete')) {
						$action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" 
									data-id="'.$row->id.'">
									<i class="far fa-trash-alt"></i> Delete</a>';
					}
					if ($this->user->can('User.Block')) {
						$blockText = ($row->is_block == 1) ? 'Un-Block' : 'Block';
						$blockIcon = ($row->is_block == 1) ? 'angle-down' : 'angle-up';
						$action .= '<a class="dropdown-item btn-block" style="cursor: pointer;" 
									data-id="'.$row->id.'" data-val="'.$row->is_block.'">
									<i class="fas fa-'.$blockIcon.'"></i> '.$blockText.'</a>';
					}

					$action .= '</div></div></div></span>';
				}

				// Build row data
				$rowData = [];
				
				// Only include action column if user has any permissions
				if ($hasActionPermissions) {
					$rowData[] = $action;
				}
				
				$rowData[] = $row->first_name." ".$row->last_name;
				$rowData[] = $row->role_name;
				$rowData[] = $row->email;
				$rowData[] = $row->phone_number;
				$rowData[] = $status;
				$rowData[] = $blockstatus;

				$data[] = $rowData;
			}

			return response()->json([
				"draw" => intval($r->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data
			], 200);

		} catch (\Exception $ex) {
			LogHelper::logError(
				'An error occurred while fetching the user list',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				request()->path(),
				$this->user->id ?? null
			);

			return response()->json([
				"message" => "An error occurred while fetching the user list",
			], 500);
		}
	}
	 /*
	 * user add
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
	 
	public function create(Request $r){
		try{
		   
			//user add 
		   return view('user.add');
		   
		}catch (\Exception $ex) {
		   // Log the error
            LogHelper::logError('An error occurred while create the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');		
			// Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the user add.');
        }
	}
	
	public function store(Request $request){
		 //check validation
		
		try{
			
			$validator = Validator::make($request->all(), [
				'role' => 'required',
				'first_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'last_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'phone_number' => [
					'required', 
					'digits:10', 
					'numeric',
					Rule::unique('users')->where(function ($query) {
						return $query->where('is_delete', '=', '0');
					})
				],
				'email' => [
					'required',
					'email',
					'min:2', 
					'max:150',
					'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
					Rule::unique('users')->where(function ($query) {
						return $query->where('is_delete', '=', '0');
					})
				],
				'password' => 'required|min:6|max:20|regex:/^\S+$/',
				'password_confirmation' => 'required|same:password|regex:/^\S+$/',
				'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
			], [
				'role.required' => 'Role is required.',
				'first_name.required' => 'First name is required.',
				'first_name.regex' => 'The first name must only contain alphabets and spaces.',
				'first_name.min' => 'The First name must be at least 2 characters long.',
				'first_name.max' => 'The First name cannot exceed 150 characters.',
				'last_name.required' => 'Last name is required.',
				'last_name.regex' => 'The last name must only contain alphabets and spaces.',
				'last_name.min' => 'The Last name must be at least 2 characters long.',
				'last_name.max' => 'The Last name cannot exceed 150 characters.',
				'phone_number.required' => 'The Phone number is required.',
				'phone_number.digits' => 'The Phone number must be exactly :digits digits.',
				'phone_number.numeric' => 'Please enter a valid number.',
				'phone_number.unique' => 'The phone number has already been taken.',
				'email.required' => 'The Email is required.',
				'email.email' => 'Please enter a valid email address.',
				'email.unique' => 'The email has already been taken.',
				'email.min' => 'The Email must be at least 2 characters long.',
				'email.max' => 'The Email cannot exceed 150 characters.',
				'password.required' => 'Password is required.',
				'password.min' => 'The password must be at least :min characters.',
				'password.max' => 'The password must not exceed :max characters.',
				'password_confirmation.required' => 'Confirm Password is required.',
				'password_confirmation.same' => 'Password does not match confirm password.',
				'password.regex' => 'Enter a valid password.',
				'password_confirmation.regex' => 'Enter a valid confirm password.',
				'avatar.image' => 'The file must be an image.',
				'avatar.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}
			
			$user = new User;
			$user->role_id=$request->role;
			$user->first_name=$request->first_name;
			$user->last_name=$request->last_name;
			$user->phone_number=$request->phone_number;
			$user->email=$request->email;
			$user->password=Hash::make($request->password);
			$user->is_active = $request->is_active ? 1 : 0;
			$user->is_delete = 0;
			$user->is_block=0;
			// Handle the avatar image upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $imageName = 'avatar-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('avatar', $file, $imageName);
                $user->avatar= $path; // Save the image name in the database
            }
			
			$user->save();
			
			//give permissions to user other than admin
			if($request->role!=1){			
				$permissions = Permission::where('id', "152")->first();			
				if($permissions){
					$users = User::find($user->id);
					$users->givePermissionTo($permissions);
				}
			}
			//permission ended
			
			
			
            //success log
			LogHelper::logSuccess('The user added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $user->id);
            // Return success response
            return redirect('users')->with('success', 'User added successfully.');
			
		}catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the user.');
        }
		
	}
	
	 /*
	 * get role list for user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	
	public function get_role(Request $r)
	{
		try {
			$html = [];
			$search = $r->input('search');
			$result = Role::where('name', 'like', '%' . $search . '%')
				->where('id', '!=', 1)
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					$html[] = ['id' => $item->id, 'text' => $item->name];
				}
			}
			return response()->json($html);
			
		} catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the role dropdown list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
        }
	}
	
	
	/*
	 * get user list
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	
	public function get_users(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = User::where(function ($query) use ($search) {
					$query->where('first_name', 'like', '%' . $search . '%')
						->orWhere('last_name', 'like', '%' . $search . '%');
				})
				->where('id', '!=', 1)
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					$fullName = trim($item->first_name . ' ' . $item->last_name);
					$html[] = ['id' => $item->id, 'text' => $fullName];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the users list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
	}
	
	
    /*
	 * remove user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	public function destroy(string $id)
    {
        try {
            // Find the user id
            $user = User::find($id);

            // Check if the user exists
            if (!$user) {
                
				//log error
				LogHelper::logError('An error occurred while deleting the user', 'User not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
				// Return error response
                return response()->json([
                    'success' => false,
                    'error' => 'User not found.'
                ]);
            }

            // Soft delete the user by setting is_delete flag
            $user->is_delete = 1;
            $user->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
             LogHelper::logError('An error occurred while deleting the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
                "message" => "An error occurred while deleting the user",
            ], 500);
        }
    }

    /*
	 * edit user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	public function edit(string $id)
	{
		try {
            $user =User::with('role')->where('id', $id)->where('is_delete', 0)->first();
			
			if (!$user) {
                // Log the error
                LogHelper::logError('An error occurred while edit the user', 'The invalid user',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid user.');
            }
            return view('user.edit', compact('user'));
        } catch (\Exception $ex) {            
            // Log the error
             LogHelper::logError('An error occurred while edit the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the user.');
        }
	}
	
	  
	 /*
	 * update user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	 
	public function update(Request $request,string $id){
		
		try {
			//check validation
		    $validator = Validator::make($request->all(), [
				'role' => 'required',
				'first_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'last_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
				'phone_number' => [
					'required', 
					'digits:10', 
					'numeric',
					Rule::unique('users')->where(function ($query) use ($id) {
						return $query->where('is_delete', '=', '0')
							->where('id', '!=', $id);
					}),
				],
				'email' => [
					'required',
					'email',
					'min:2', 
					'max:150',
					'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
					Rule::unique('users')->where(function ($query) use ($id) {
						return $query->where('is_delete', '=', '0')
							->where('id', '!=', $id);
					}),
				],
				'password' => 'nullable|min:6|max:20|regex:/^\S+$/',
				'password_confirmation' => 'nullable|same:password|regex:/^\S+$/',
				'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
			], [
				'role.required' => 'Role is required.',
				'first_name.required' => 'First name is required.',
				'first_name.regex' => 'The first name must only contain alphabets and spaces.',
				'first_name.min' => 'The First name must be at least 2 characters long.',
				'first_name.max' => 'The First name cannot exceed 150 characters.',
				'last_name.required' => 'Last name is required.',
				'last_name.regex' => 'The last name must only contain alphabets and spaces.',
				'last_name.min' => 'The Last name must be at least 2 characters long.',
				'last_name.max' => 'The Last name cannot exceed 150 characters.',
				'phone_number.required' => 'The Phone number is required.',
				'phone_number.digits' => 'The Phone number must be exactly :digits digits.',
				'phone_number.numeric' => 'Please enter a valid number.',
				'phone_number.unique' => 'The phone number has already been taken.',
				'email.required' => 'The Email is required.',
				'email.email' => 'Please enter a valid email address.',
				'email.unique' => 'The email has already been taken.',
				'email.min' => 'The Email must be at least 2 characters long.',
				'email.max' => 'The Email cannot exceed 150 characters.',
				'password.min' => 'The password must be at least :min characters.',
				'password.max' => 'The password must not exceed :max characters.',
				'password_confirmation.same' => 'Password does not match the confirm password.',
				'password.regex' => 'Enter a valid password.',
				'avatar.image' => 'The file must be an image.',
				'avatar.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			
			//update user
            $user = User::find($id);
			
			if (!$user) {
                
				// Log the error
                LogHelper::logError('An error occurred while update the user', 'The invalid user',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid user.');
            }
			
			$user->role_id=$request->role;
			$user->first_name=$request->first_name;
			$user->last_name=$request->last_name;
			$user->phone_number=$request->phone_number;
			$user->email=$request->email;
			if($request->password!="" && $request->password_confirmation!=""){
			    $user->password=Hash::make($request->password);
			}
			$user->is_active = $request->is_active ? 1 : 0;
			$user->is_delete = 0;
			
			// Handle the avatar image upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $imageName = 'avatar-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('avatar', $file, $imageName);
                $user->avatar= $path; // Save the image name in the database
            }
			$user->update();
			//success log
			LogHelper::logSuccess('The user updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $user->id);
            // Return success response
            return redirect('users')->with('success', 'User updated successfully.');
			
		}catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while updating the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while update the user.');
        }
	}
	 
	
    /*
	 * show user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	public function show(string $id)
	{
		try {
            $user =User::with('role')->where('id', $id)->where('is_delete', 0)->first();
			
			if (!$user) {
                // Log the error
                LogHelper::logError('An error occurred while view the user', 'The invalid user',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid user.');
            }
            return view('user.show', compact('user'));
        } catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while view the user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the user.');
        }
	}
	

    
	 /*
	 * delete user profile image
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	 
	public function delete_avatar(Request $r,String $id)
    {
        try{
			$user = User::find($r->id);
			
			if (!$user) {                
				 // Log the error
				 LogHelper::logError('An error occurred while delete the user avatar', 'The invalid user',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->id);
				 // Return error response to the user
					return redirect()->back()->with('error', 'The invalid user.');
            }
			
			
			Storage::disk('public')->delete($user->avatar);
			$user->update(['avatar' => null]);
            //success log			
			LogHelper::logSuccess('The avatar image deleted successfully',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->id);
			//return response
			return redirect('users/'.$r->id.'/edit')->with('success', 'Avatar deleted successfully');
		}catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while deleting the avatar image', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while delete avatar.');
        }
    }
	
	
    /*
	 * block unblock user
	 * seemashelar@neosao
	 * dt: 23-oct-2024
	 */
	 
	 
	 public function block_unblock_user(String $id){
		try {
			// Find the user by ID
			$user = User::find($id);

			// Check if the user exists
			if (!$user) {
				
				// Log the error
                LogHelper::logError('An error occurred while block-unblock the user', 'The invalid user',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
				//return error response
				return response()->json([
					'success' => false,
					'error' => 'User not found.'
				]);
			}

			// Toggle block/unblock status
			if ($user->is_block == 0) {
				// Block the user
				$user->is_block = 1;
				$user->is_active = 0;
			} else {
				// Unblock the user
				$user->is_block = 0;
				$user->is_active = 1;
			}

			// Save user status
			if ($user->save()) {
				
			//success log
			LogHelper::logSuccess('User block/unblock action successful.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $user->id);
			//return response to js function
			return response()->json(['success' => true]);
			} else {
				
				// Log the error
                LogHelper::logError('An error occurred while block-unblock the user', 'Failed to save user block/unblock status.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
			    //Return resposne to user
				return response()->json(['success' => false, 'error' => 'Failed to update user status.']);
			}
		} catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while block/unblock user', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			 //Return resposne to user
			return redirect()->back()->with('error', 'An error occurred while block/unblock user.');
		}
	}
	
	//excel downloading
	public function excel_download(Request $r)
	{
		try {
			$user = $r->user ?? "";
			$role = $r->role ?? "";
			$search = "";
			$limit = $r->length ? (int)$r->length : null; // Cast to int
            $offset = $r->start ? (int)$r->start : 0; // Cast to int

			// Fetch the filtered user data
			$filteredData = User::filterUser($search, $limit, $offset, $user, $role);
			$records = $filteredData['result'];

			if (empty($records)) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$carbonDate = Carbon::parse($row->created_at);
				$formattedDate = $carbonDate->format('d-m-Y');

				$status = ($row->is_active == 1) ? 'Active' : 'In-Active';
				$blockStatus = ($row->is_block == 1) ? 'Yes' : 'No';

				$csvData[] = [
					'Full Name' => $row->first_name . " " . $row->last_name,
					'Role' => $row->role_name,
					'Email' => $row->email,
					'Phone Number' => $row->phone_number,
					'Status' => $status,
					'Blocked' => $blockStatus,
					'Created At' => $formattedDate,
				];
			}

			// Create CSV file in memory
			$csvFileName = 'Users_' . date('d-m-Y') . '.csv';
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
			// Log the error and return a response with error message
			LogHelper::logError('An error occurred while downloading the user list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while generating the CSV file.",
			], 500);
		}
	}
	
    //pdf download
	public function pdf_download(Request $r)
	{
		$user = $r->user ?? "";
		$role = $r->role ?? "";
		$search = $r->input('search.value') ?? "";
		$limit = $r->length ?? null;
		$offset = $r->start ?? 0;

		$filteredData = User::filterUser($search, $limit, $offset, $user, $role);

		$total = $filteredData['totalRecords'];
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
								<th>Role</th>
								<th>Email</th>
								<th>Phone</th>
								<th>Status</th>
								<th>Block Status</th>
							</tr>
						</thead>';
		$htmlContent .= '<tbody>';

		foreach ($records as $row) {
			$createdDate = Carbon::parse($row->created_at)->format('d-m-Y');

			$status = $row->is_active ? '<span class="badge-soft-success">Active</span>' : '<span class="badge-soft-danger">In-Active</span>';
			$blockstatus = $row->is_block ? '<span class="badge-soft-success">Yes</span>' : '<span class="badge-soft-danger">No</span>';

			$htmlContent .= '<tr>';
			$htmlContent .= '<td>' . $row->first_name . " " . $row->last_name . '</td>';
			$htmlContent .= '<td>' . $row->role_name . '</td>';
			$htmlContent .= '<td>' . $row->email . '</td>';
			$htmlContent .= '<td>' . $row->phone_number . '</td>';
			$htmlContent .= '<td>' . $status . '</td>';
			$htmlContent .= '<td>' . $blockstatus . '</td>';
			$htmlContent .= '</tr>';
		}

		$htmlContent .= '</tbody></table>';

		$pdf = PDF::loadHTML($htmlContent);

		return $pdf->download('Users.pdf');
	}




}