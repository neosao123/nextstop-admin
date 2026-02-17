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
use App\Models\ServiceableZone;
use Illuminate\Validation\Rule;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ServiceableZoneController extends Controller
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
            return view('serviceable-zone.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the serviceable zone index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the serviceable zone list.');
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
			$data = array();
			
			$filteredData = ServiceableZone::filterServiceableZone($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('ServiceableZone.Edit');
			$canView = Auth::guard('admin')->user()->can('ServiceableZone.View');
			$canDelete = Auth::guard('admin')->user()->can('ServiceableZone.Delete');
			$showActions = $canEdit || $canView || $canDelete;

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y h:i:s A');
					
					$dataRow = [];
					
					// Add action column only if user has any permissions
					if ($showActions) {
						$action = '<span class="text-start">
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="serviceablezone-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="serviceablezone-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('serviceable-zone/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('serviceable-zone/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->serviceable_zone_name;
					$dataRow[] = $formattedDate;
					$dataRow[] = $row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
					
					$data[] = $dataRow;
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
			LogHelper::logError('An error occurred while the serviceable zone list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the serviceable zone list",
			], 500);
		}
	}
	
	
	/**
	 * Show the form for creating a new resource.
     * @author seemashelar@neosao
     * 
     */
    public function create()
    {
        try {
            return view('serviceable-zone.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while creating the serviceable zone list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the serviceable zone list.');
        }
    }
    
	/**
	 * Show the form for creating a new resource.
     * @author seemashelar@neosao
     * 
     */
	 
    public function store(Request $request)
    {
        $request->validate([
			'serviceable_zone_name' => [
				'required',
				'min:2',
				'max:150',
				'regex:/^[a-zA-Z\s]+$/',
				Rule::unique('serviceable_zones')->where(function ($query) {
					return $query->where('is_delete', '=', '0');
				})
			],
			'co_ordinates'=>'required',
			'is_active' => 'nullable'
		], [
			'serviceable_zone_name.required' => 'The serviceable zone name field is required.',
			'serviceable_zone_name.min' => 'The serviceable zone name must be at least 2 characters long.',
			'serviceable_zone_name.max' => 'The serviceable zone name must not exceed 150 characters.',
			'serviceable_zone_name.regex' => 'The serviceable zone name may only contain letters and spaces.',
			'serviceable_zone_name.unique' => 'The serviceable zone has already been taken.',
			'co_ordinates.required'=>'The Co-ordinates is required.',
		]);

        try {
            // Create and save the serviceable zone
			
			$coordinatesArray = json_decode($request['co_ordinates'], true);
			$last = $coordinatesArray[0];
			$coordinatesArray[] =  $last;

			// Create Point objects from the coordinates
			$pointsOne = array_map(function ($coordinate) {
				return new Point((float) $coordinate[1], (float) $coordinate[0]);
			}, $coordinatesArray);
            $lineString = new LineString($pointsOne);
            $polygon = new Polygon([$lineString]);
			
			//save data in table 
            $serviceable_zone = new ServiceableZone;
            $serviceable_zone->serviceable_zone_name = $request->serviceable_zone_name;
            $serviceable_zone->serviceable_area= $polygon;
			$serviceable_zone->is_active = $request->is_active ? 1 : 0;
            $serviceable_zone->save();
              
			//success response
            LogHelper::logSuccess('The serviceable zone added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $serviceable_zone->id);
            
			// Return success response
            return redirect('serviceable-zone')->with('success', 'Serviceable zone added successfully.');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the serviceable zone', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the serviceable zone.');
        }
    }
	
	/**
	 * Display the specified resource.
     * @author seemashelar@neosao
     * 
     */

	 public function show(string $id)
    {
        try {
            $serviceable_zone = ServiceableZone::where('id', $id)->where('is_delete', 0)->first();
            if (!$serviceable_zone) {
                // Log the error
                LogHelper::logError('An error occurred while view the serviceable zone', 'The invalid vehicle',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid serviceable zone.');
            }
            $coordinates = $serviceable_zone->serviceable_area;
            return view('serviceable-zone.show', compact('serviceable_zone','coordinates'));
        } catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while view the serviceable zone', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while show the serviceable zone.');
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
            $serviceable_zone = ServiceableZone::where('id', $id)->where('is_delete', 0)->first();
            if (!$serviceable_zone) {
                // Log the error
                LogHelper::logError('An error occurred while edit the serviceable zone', 'The invalid serviceable zone',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid serviceable zone.');
            }
			 // Get coordinates from serviceable_area and decode them
			$coordinates = $serviceable_zone->serviceable_area;
            return view('serviceable-zone.edit', compact('serviceable_zone','coordinates'));
        } catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while edit the serviceable zone', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while editserviceable zone the serviceable zone.');
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
			'serviceable_zone_name' => [
				'required',
				'min:2',
				'max:150',
				'regex:/^[a-zA-Z\s]+$/',
				Rule::unique('serviceable_zones')->where(function ($query) use($id){
					return $query->where('is_delete', '=', '0')->where("id", '!=', $id);
				})
			],
			'is_active' => 'nullable'
		], [
			'serviceable_zone_name.required' => 'The serviceable zone name field is required.',
			'serviceable_zone_name.min' => 'The serviceable zone name must be at least 2 characters long.',
			'serviceable_zone_name.max' => 'The serviceable zone name must not exceed 150 characters.',
			'serviceable_zone_name.regex' => 'The serviceable zone name may only contain letters and spaces.',
			'serviceable_zone_name.unique' => 'The serviceable zone has already been taken.',
		]);

        try {
			
			// Create and save the serviceable zone
			
			$coordinatesArray = json_decode($request['co_ordinates'], true);
			$last = $coordinatesArray[0];
			$coordinatesArray[] =  $last;

			// Create Point objects from the coordinates
			$pointsOne = array_map(function ($coordinate) {
				return new Point((float) $coordinate[1], (float) $coordinate[0]);
			}, $coordinatesArray);
            $lineString = new LineString($pointsOne);
            $polygon = new Polygon([$lineString]);
			
            // Create and save the serviceable zone
            $serviceable_zone = ServiceableZone::find($id);
            $serviceable_zone->serviceable_zone_name = $request->serviceable_zone_name;
            $serviceable_zone->serviceable_area= $polygon;
			$serviceable_zone->is_active = $request->is_active ? 1 : 0;
            $serviceable_zone->save();
              
			//success response
            LogHelper::logSuccess('The serviceable zone updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $serviceable_zone->id);
            
			// Return success response
            return redirect('serviceable-zone')->with('success', 'Serviceable zone updated successfully.');
        } catch (\Exception $ex) {
             // Log the error
            LogHelper::logError('An error occurred while updating the serviceable zone', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the serviceable zone.');
        }
    }
	
	
	  /**
	 * @author seemashelar@neosao
     * Remove the specified resource from storage.
     * 
     */
    public function destroy(string $id)
    {
        try {
            // Find the serviceable zone by ID
           $serviceable_zone = ServiceableZone::find($id);
		   
            // Check if the serviceable zone exists
            if (!$serviceable_zone) {
                LogHelper::logError('An error occurred while deleting the serviceable zone', 'Serviceable zone not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'Serviceable zone not found.']);
            }

            // Soft delete the serviceable zone by setting is_delete flag
            $serviceable_zone->is_delete = 1;
            $serviceable_zone->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the serviceable zone', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the serviceable zone .']);
        }
    }


}