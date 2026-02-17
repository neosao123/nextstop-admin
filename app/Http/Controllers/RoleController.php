<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Classes\ActivityLog;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RoleController extends Controller
{
    public $user;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();
            return $next($request);
        });
    }
	
	 /*
	 * role master
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 
    public function index()
    {
		try{
           return view('configuration.role.index');
		}catch (\Exception $ex) {
			 // Log the error
			 Log::error('An error occurred while the role list', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the role list.');
        }
    }
	
	/*
	 * role list
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 

    public function list(Request $r)
    {
		try{
			$search = $r->input('search.value');
			$limit = $r->length;
			$offset = $r->start;
			$srno = $offset + 1;
			$dataCount = 0;
			$data = array();
            //Role List
			$result = Role::where(function ($query) use ($search) {
				$query->where('name', 'LIKE', '%' . $search . '%');
			})->orderBy('id', 'desc')->limit($limit)->skip($offset)->get();
			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$action =   '<div class="text-end">';
					if ($this->user->can('Role.Delete')) {
						$action .= '<a class="btn btn-default border-300 btn-sm btn-delete-role me-1 text-600" data-role_id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><span class="far fa-trash-alt text-danger"></span></button>';
					}
					if ($this->user->can('Role.Edit')) {
						$action .= '<a class="btn btn-default border-300 btn-sm btn-edit-role me-1 text-600 shadow-none" data-role_id="' . $row['id'] . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><span class="far fa-edit text-warning"></span></button>';
					}
					$action .= '</div>';
					$data[] = array(
						$row->name, 
						$action
					);
					$srno++;
				}
				$dataCount = Role::where(function ($query) use ($search) {
					$query->where('name', 'LIKE', '%' . $search . '%');
				})->orderBy('id', 'desc')->count();
			}
			// Return success response
			return response()->json([
				"draw" => intval($r->draw),
				"recordsTotal" => $dataCount,
				"recordsFiltered" => $dataCount,
				"data" => $data,
			], 200);
		}catch (\Exception $ex) {
		    // Log the error
			 Log::error('An error occurred while the role list', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			
			
            return response()->json([
                "message" => "An error occurred while the role list",
            ], 500);
        }
    }
	
	/*
	 * role save
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 

    public function store(Request $r)
    {
		try{
			$rules = array(
				'name' => [
					'required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:50',
					Rule::unique('roles'),
				],
			);
			$messages = array(
				'name.required' => 'The name is required',
				'name.regex' => 'The name field should only contain alphabetic characters and spaces.',
				'name.max' => 'Maximum limit reached of 50 characters',
				'name.min' => 'Minimum 2 characters are required',
				'name.unique' => 'The name is already exist.',
			);
			$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json(['errors' => $validator->errors()], 200);
			} else {
				$data = array(
					'name' => ucfirst($r->name),
					'guard_name' => 'admin',
				);
				//role create 
				$result=Role::create($data);
				
				//success log
				 Log::info('The Role added successfully.', [
				    'id'=>$result->id,
					'user_id' => auth()->id(),					
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
				]);
				
			    // Return success response
				return response()->json(['status' => 200, 'msg' => "Record added successfully.", 'data' => $data], 200);
			}
		}catch (\Exception $ex) {
			// Log the error
            Log::error('An error occurred while saving the role', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

        }
    }
    /*
	 * role edit
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 
    public function edit(Request $r)
    {
	   try{
			$id = $r->id;
			$role = Role::find($id);
			if ($role) {
				return response()->json(["status" => 200, "msg" => "Data found", "data" => $role], 200);
			}
			return response()->json(["msg" => "Data Not Found"], 400);
	   }catch (\Exception $ex) {
		   
			// Log the error
            Log::error('An error occurred while fetching role', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
       }
    }
	
	/*
	 * role update
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 

    public function update(Request $r)
    {
	  try{
			$id = $r->id;
			$rules = array(
				'id' => 'required',
				'name' => [
					'required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:50',
					Rule::unique('roles')->where(function ($query) use ($id) {
						return $query->where('id', '!=', $id);
					})
				],
			);
			$messages = array(
				'id.required' => 'Missing Id',
				'name.required' => 'The name is required',
				'name.regex' => 'The name field should only contain alphabetic characters and spaces.',
				'name.max' => 'Maximum limit reached of 50 characters',
				'name.min' => 'Minimum 2 characters are required',
				'name.unique' => 'The name is already exist.'
			);
			$validator = Validator::make($r->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json(['errors' => $validator->errors()], 200);
			} else {
				$data = array(
					'name' => ucfirst($r->name),
				);
				
				//update role
				$role = Role::find($r->id);
				$role->update($data);
				
				//success log
				Log::info('The role updated successfully.', [
				    'id'=>$r->id,
					'user_id' => auth()->id(),					
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
				]);
				
				return response()->json(['status' => 200, 'msg' => "Record updated successfully."], 200);
			}
	   }catch (\Exception $ex) {
		   
			 // Log the error
            Log::error('An error occurred while updating the role master', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
            return response()->json(['status' => 300, 'msg' => "Something went to wrong"], 200);
       }
    }

     /*
	 * role delete
	 * seemashelar@neosao
	 * dt: 21-oct-2024
	 */
	 
    public function destroy($id)
    {
	   try{
		    $role = Role::find($id);
		    $users = User::where("role_id",$id)->count();
		    if($users>0){
				return response()->json(['status' => 400, 'message' => 'Unable to delete records. This role is currently assigned to one or more users.']);
			}		   
			//$role = Role::find($id);
			$role->delete();
			
			//success log
			Log::info('The role delete successfully.', [
			'id'=>$id,
			'user_id' => auth()->id(),				
			'function' => __FUNCTION__,
			'file' => basename(__FILE__),
			'line' => __LINE__,
			'path' => __FILE__,
			]);
		   
		   //success response
			return response()->json(['status' => 200, 'message' => 'Record deleted successfully.']);
			
	   }catch (\Exception $ex) {
			// Log the error
            Log::error('An error occurred while delete role', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
        }
    }
}
