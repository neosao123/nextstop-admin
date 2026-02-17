<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;

class UserPermissionsController extends Controller
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
	 * permission index page
	 * seemashelar@neosao
	 * dt: 24-oct-2024
	 */
    public function index(string $id)
    {
        try{
			if ($id) {
				//get users
				$user = User::with('roles')->where('id', $id)->first();
                
				//get permission group 
				$groups = PermissionGroup::join('permissions', 'permission_groups.id', '=', 'permissions.group_id')
					->select('permission_groups.*')
					->distinct()
					->get();
				//get permissions
				$permissions = Permission::get();
				if ($user) {
					$directPermission = $user->getDirectPermissions();
					$permissionsViaRoles = $user->getPermissionsViaRoles();
					return view('user-permissions.index', compact('id', 'user', 'groups', 'permissions', 'directPermission', 'permissionsViaRoles'));
				}
				
				// Log the error
                Log::error('An error occurred while permission list', [
                    'id' => $id,
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'The invalid user'
                ]);				
				
				//return error response 
				return  redirect('/user')->with('error', 'User was not found or got removed');
			}
			return  redirect('/user')->with('error', 'Invalid url . Please try again!');
		}catch (\Exception $ex) {
			 // Log the error
			 Log::error('An error occurred while the permission list', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the permission list.');
        }
    }

    /*
	 * permission setting to specific user
	 * seemashelar@neosao
	 * dt: 24-oct-2024
	 */
	 
    public function setPermission($id, Request $r)
    {
	   try{
			$id = $r->id;
			$permissioId = $r->permissionId;
			$mode = $r->mode;
			$user = User::find($id);
			if ($user) {
				$permissions = Permission::where('id', $permissioId)->first();
				if ($mode === "revoke") {
					//success log
					Log::info('The user remove permission successfully.', [
						'id' => $user->id,
						'user_id' => auth()->id(),
						'function' => __FUNCTION__,
						'file' => basename(__FILE__),
						'line' => __LINE__,
						'path' => __FILE__,
					]);

					//permission set
					$user->revokePermissionTo($permissions);
					//return success response
					return response()->json(['status' => 200, 'message' => 'Permission removed successfully'], 200);
				} else {
					//success log
					Log::info('The user set permission successfully.', [
						'id' => $user->id,
						'user_id' => auth()->id(),
						'function' => __FUNCTION__,
						'file' => basename(__FILE__),
						'line' => __LINE__,
						'path' => __FILE__,
					]);
                    //permission set
					$user->givePermissionTo($permissions);
					//return success response
					return response()->json(['status' => 200, 'message' => 'Permission added successfully'], 200);
				}
			}
			
			// Log the error
			Log::error('An error occurred while permission apply', [
				'id' => $id,
				'user_id' => auth()->id(),
				'function' => __FUNCTION__,
				'file' => basename(__FILE__),
				'line' => __LINE__,
				'path' => __FILE__,
				'exception' => 'The invalid user'
			]);				
			
           //return failed resposne			
			return response()->json(['message' => 'failed'], 200);
	   }catch (\Exception $ex) {
			 // Log the error
			 Log::error('An error occurred while the permission apply', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the permission apply.');
       }
    }
}