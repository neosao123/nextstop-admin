<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Setting;
use Illuminate\Validation\Rule;


class SettingController extends Controller
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
            return view('setting.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the setting index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the setting list.');
        }
    }
	
	
	 /**
     * Display a listing of the resource.
	 * @author seemashelar@neosao
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search.value') ?? "";
            $limit = $request->length;
            $offset = $request->start;
            $srno = $offset + 1;
            $data = array();
            $filteredData =  Setting::filterSetting($search, $limit, $offset);
            $total = $filteredData['totalRecords'];
            $result =  $filteredData['result'];
			
			$canEdit = Auth::guard('admin')->user()->can('Setting.Edit');
			$canView = Auth::guard('admin')->user()->can('Setting.View');
			$canDelete = Auth::guard('admin')->user()->can('Setting.Delete');
			$showActions = $canEdit || $canView || $canDelete;

			
            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y h:i:s A');
                   $dataRow = [];
				   
                  // Add action column only if user has any permissions
					if ($showActions) {
                        $action = '
                    <span class="text-start">
                        <div class="dropdown font-sans-serif position-static">
                            <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
                                aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                                <div class="bg-white py-2">';
                        if ($canEdit) {
                            $action .= '<a class="dropdown-item btn-edit " href="' . url('setting/' . $row['id'] . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit')  . ' </a>';
                        }
                        if ($canView) {
                            $action .= '<a class="dropdown-item btn-edit" href="' . url('setting/' . $row['id']) . '"> <i class="far fa-folder-open"></i> ' . __('index.view')  . '</a>';
                        }
                        
                        $action .= '</div></div></div></span>';
						$dataRow[] = $action;
                    }


                    if ($row->is_active == 1) {
                        $status =   '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>';
                    } else {
                        $status =   '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
                    }
					$dataRow[] = $row->setting_name;
					$dataRow[] =$row->setting_value;
					$dataRow[] = $formattedDate;
					$data[] = $dataRow;
                    //$srno++;
                }
            }
            return response()->json([
                "draw" => intval($request->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total, 
                "data" => $data,
                "result" => $result
            ], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while the setting list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            return response()->json([
                "message" => "An error occurred while the setting list",
            ], 500);
        }
    }
	
	
	/**
	 * Edit the specified resource.
     * @author seemashelar@neosao
     * 
     */
	 public function edit(string $id)
    {
        try {
            $setting = Setting::where('id', $id)->where('is_delete', 0)->first();
            if (!$setting) {
                // Log the error
                LogHelper::logError('An error occurred while edit the setting', 'The invalid setting',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid setting value.');
            }
			 
            return view('setting.edit', compact('setting'));
        } catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while edit the setting', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the setting.');
        }
    }
	
	/**
	 * Update the specified resource.
     * @author seemashelar@neosao
     * 
    */
	
	public function update(Request $request,string $id)
    {
        $request->validate([
			'setting_name' => [
				'required'
			],
			'setting_value' => [
				'required'
			],
			'is_active' => 'nullable',
			'is_update_compulsory' => 'nullable'
		], [
			'setting_name.required' => 'The setting name field is required.',
			'setting_value.required' => 'The setting value field is required.',
		]);

        try {
			
            // Create and save the setting
            $setting = Setting::find($id);
            $setting->setting_name = $request->setting_name;
            $setting->setting_value= $request->setting_value;
			$setting->is_active = $request->is_active ? 1 : 0;
			$setting->is_update_compulsory = $request->is_update_compulsory ? 1 : 0;
            $setting->save();
              
			//success response
            LogHelper::logSuccess('The setting updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $setting->id);
            
			// Return success response
            return redirect('setting')->with('success', 'Setting updated successfully.');
        } catch (\Exception $ex) {
             // Log the error
            LogHelper::logError('An error occurred while updating the setting', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the setting.');
        }
    }
	
	
	/**
	 * show the specified resource.
     * @author seemashelar@neosao
     * 
     */
	 public function show(string $id)
    {
        try {
            $setting = Setting::where('id', $id)->where('is_delete', 0)->first();
            if (!$setting) {
                // Log the error
                LogHelper::logError('An error occurred while view the setting', 'The invalid setting',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid setting value.');
            }
			 
            return view('setting.show', compact('setting'));
        } catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while show the setting', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while show the setting.');
        }
    }
	
	
	
	
}