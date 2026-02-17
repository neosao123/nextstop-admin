<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class PermissionController extends Controller
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
	 * Permission list
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
    public function index()
    {
		try{
			$permissions = Permission::join('permission_groups', 'permission_groups.id', '=', 'permissions.group_id')->paginate(10);
			$groups = PermissionGroup::all();
			return  view('configuration.permissions.index', compact('permissions', 'groups'));
		}catch(\Exception $ex) {
			// Log the error
            Log::error('An error occurred while fetching permission', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			
			 // Return error response to the user
           return redirect()->back()->with('error', 'An error occurred while fetching permissions.');
		}
    }

    /*
	 * Permission add
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
    public function add_permission()
    {
		try{
			//add permission index page
			$user = $this->user;
			return view('configuration.permissions.add', compact('user'));
		}catch(\Exception $ex) {
			// Log the error
            Log::error('An error occurred while add permission', [
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
	 * Permission store
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
    public function store(Request $request)
    {
		
			$validated = $request->validate([
				'group' => 'required',
				'section' => 'required',
				'group_id' => 'required'
			],[
				'group.required' => 'Group is required.',
				'section.required' => 'Section is required.',
				'group_id.required' => 'Group ID is required.',
			]);
			try{
			//check permission
			$permission = Permission::where('name', $request->group . '.' . $request->section)->count();
			if ($permission > 0) {
				
				//error log
				Log::error('An error occurred while creating premission', [
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'The invalid role.',
                ]);
				
				//return error response
				return redirect('/configuration/permissions')->with('error', 'Similar permission already exists');
			}
			//premission create
			$result=Permission::create((['group_id' => $request->group_id, 'name' => $request->group . '.' . $request->section, 'guard_name' => 'admin']));
			
			//success log
			 Log::info('The Permission added successfully.', [
				'user_id' => auth()->id(),
				'id'=>$result->id,
				'function' => __FUNCTION__,
				'file' => basename(__FILE__),
				'line' => __LINE__,
				'path' => __FILE__,
			]);
			
			$user = User::find(1);
			$user->givePermissionTo($request->group . '.' . $request->section);
			
			//return success response
			return redirect('/configuration/permissions')->with('success', 'Record added successfully');
		}catch(\Exception $ex) {
			  // Log the error
				Log::error('An error occurred while saving the permission', [
					'user_id' => auth()->id(),
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
					'exception' => $ex->getMessage(),
				]);

				// Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the permission.');
		}
		
    }
}
