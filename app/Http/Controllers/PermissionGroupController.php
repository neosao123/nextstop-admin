<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class PermissionGroupController extends Controller
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
	 * Permission Group list
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */
	 
    public function index()
    {
	    try{	
           $permissionGroups = PermissionGroup::paginate(15);
           return view('configuration.permission-groups.index', compact('permissionGroups'));
		}catch (\Exception $ex) {
			// Log the error
            Log::error('An error occurred while fetching permission group', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
            
		   // Return error response to the user
           return redirect()->back()->with('error', 'An error occurred while fetching permission group.');
        }
    }
	
	/*
	 * Create
	 * seemashelar@neosao
	 * dt: 22-oct-2024
	 */

    public function create()
    {
		try{
            return view('configuration.permission-groups.create');
		}catch (\Exception $ex) {
			// Log the error
            Log::error('An error occurred while creating permission group', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
            
		   // Return error response to the user
           return redirect()->back()->with('error', 'An error occurred while creating permission group.');
        }
    }

    public function store(Request $request)
    {
		
			$request->validate([
				'group_name' => 'required|max:100',
				'slug' => 'required|unique:permission_groups',
			],[
				'group_name.required' => 'Group name is required.',
				'group_name.max' => 'Group name must not exceed 100 characters.',
				'slug.required' => 'Slug is required.',
				'slug.unique' => 'Slug has already been taken.',
			]);
			try{
				//permission group create
				$result=PermissionGroup::create($request->all());

				//success log
				 Log::info('The Permission group added successfully.', [
					'user_id' => auth()->id(),
					'id'=>$result->id,
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
				]);
				
				// Return success response
				return redirect('configuration/permission-groups')
					->with('success', 'Permission Group created successfully.');
		  }catch(\Exception $ex) {
			  // Log the error
				Log::error('An error occurred while saving the permission group', [
					'user_id' => auth()->id(),
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
					'exception' => $ex->getMessage(),
				]);

				// Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the permission group.');
		  }
    }
}
