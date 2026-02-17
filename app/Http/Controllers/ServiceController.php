<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a index page of the resource.
     * @author seemashelar@neosao
     */
    public function index()
    {
        try {
            return view('service.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the service index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the service list.');
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
			// Fetch services with filtering
			$filteredData = Service::filterServices($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			$canEdit = Auth::guard('admin')->user()->can('Service.Edit');
			$canView = Auth::guard('admin')->user()->can('Service.View');
			$canDelete = Auth::guard('admin')->user()->can('Service.Delete');
			$showActions = $canEdit || $canView || $canDelete;

			

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y h:i:s A');
					$action = '';
                    $dataRow = [];
					
                    if ($row->service_icon != "") {
						$service_icon = '<a href="' . asset('storage/' . $row->service_icon) . '" target="_blank">View Service Icon</a>';
					} else {
						$service_icon = '';
					}
					
					// Check permissions for actions
					if ($showActions) {
						$action = '
						<span class="text-start">
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="service-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="service-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						if (Auth::guard('admin')->user()->can('Service.Edit')) {
							$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('service/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if (Auth::guard('admin')->user()->can('Service.View')) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('service/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Set service status
					$status = $row->is_active == 1
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';

                    $dataRow[] = $row->service_name;
					$dataRow[] =$service_icon;
					$dataRow[] =$status;
					$dataRow[] = $formattedDate;
					$data[] = $dataRow;					
					$srno++;
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
			LogHelper::logError('An error occurred while fetching the service list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the service list",
			], 500);
		}
	}
	
	 /**
     * Show the form for editing the specified resource.
     * @author seemashelar@neosao
     */
    public function edit(string $id)
    {
        try {
            $service = Service::where('id', $id)->where('is_delete', 0)->first();
            
            if (!$service) {
                // Log the error
                LogHelper::logError('An error occurred while edit the service', 'The invalid service',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid service.');
            }
            
            return view('service.edit', compact('service'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the service', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the service.');
        }
    }
	
	
	/**
     * Update the specified resource in storage.
     * @author seemashelar@neosao
     */
    public function update(Request $request, string $id)
    {
        try {
            // Define the validation rules
            $rules = [
                'id' => 'required',
                'service_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('services', 'service_name')->where('is_delete', 0)->ignore($id) 
                ],
                'service_icon' => 'nullable|image|mimes:jpeg,jpg,png',
                'is_active' => 'nullable'
            ];

            // Define the custom error messages
            $messages = [
                'id.required' => 'The ID is required',                
                'service_name.required' => 'The service name field is required.',
                'service_icon.image' => 'The service icon must be an image.',
                'service_icon.mimes' => 'The service icon must be a file of type: jpeg, jpg, png.',
                'is_active.nullable' => 'The is active field is optional.',
            ];

            // Perform validation
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200);
            }

            // Find the service
            $service = Service::findOrFail($id);

            // Update service attributes
            $service->service_description = $request->service_description;
            $service->is_active = $request->is_active ? 1 : 0;

            // Handle the image upload
            if ($request->hasFile('service_icon')) {
                $file = $request->file('service_icon');
                $imageName = 'service-icon-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('service-icon', $file, $imageName);
                $service->service_icon = $path; // Save the image name in the database
            }

            $service->save();

            // Log success
            LogHelper::logSuccess('The service updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $service->id);

            // Return success response for AJAX request
            return response()->json([
                'status' => 200,
                'message' => 'Service updated successfully.',
                'data' => $service
            ], 200); // Use 200 OK status code

        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the service', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response for AJAX request
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the service.',
                'error' => $ex->getMessage()
            ], 500); // Use 500 Internal Server Error status code
        }
    }

    /**
     * Show the form for view the specified resource.
     * @author seemashelar@neosao
     */
    public function show(string $id)
    {
        try {
            $service = Service::where('id', $id)->where('is_delete', 0)->first();
            
            if (!$service) {
                // Log the error
                LogHelper::logError('An error occurred while view the service', 'The invalid service',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid service.');
            }
            
            return view('service.show', compact('service'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the service', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the service.');
        }
    }
	
	

}
