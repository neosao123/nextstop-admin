<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

//  Models
use App\Models\VehicleType;

class VehicleTypeController extends Controller
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
     * @author shreyasm@neosao
     * Display a index of the resource.
     */
    public function index()
    {
        try {
            return view('vehicle-type.index');
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while the vehicle type list', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the vehicle type list.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Display a listing of the resource.
     */
   public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$data = array();
			$filteredData = VehicleType::filterVehicleType($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('Vehicle-Type.Edit');
			$canView = Auth::guard('admin')->user()->can('Vehicle-Type.View');
			$canDelete = Auth::guard('admin')->user()->can('Vehicle-Type.Delete');
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
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('vehicle-type/' . $row['id'] . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit')  . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('vehicle-type/' . $row['id']) . '"> <i class="far fa-folder-open"></i> ' . __('index.view')  . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete text-danger" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete')  . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->id;
					$dataRow[] = $row->vehicle_type;
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
			Log::error('An error occurred while the vehicle type list', [
				'user_id' => auth()->id(),
				'function' => __FUNCTION__,
				'file' => basename(__FILE__),
				'line' => __LINE__,
				'path' => __FILE__,
				'exception' => $ex->getMessage(),
			]);

			return response()->json([
				"message" => "An error occurred while the vehicle type list",
			], 500);
		}
	}

    /**
     * @author shreyasm@neosao
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('vehicle-type.add');
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while create the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the vehicle type.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_type' => [
                    'required',
                    Rule::unique('vehicle_types', 'vehicle_type')->where(function ($query) {
                        return $query->where('is_delete', 0);
                    }),
                ],
                'is_active' => 'nullable'
            ], [
                'vehicle_type.required' => 'The vehicle type field is required.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the vehicle type
            $vehicle_type = new VehicleType;
            $vehicle_type->vehicle_type = $request->vehicle_type;
            $vehicle_type->is_active = $request->is_active ? 1 : 0;
            $vehicle_type->save();

            Log::info('The vehicle type added successfully.', [
                'id' => $vehicle_type->id,
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
            ]);
            // Return success response
            return redirect('vehicle-type')->with('success', 'Vehicle type added successfully.');
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while saving the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the vehicle type.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $vehicle_type = VehicleType::where('id', $id)->where('is_delete', 0)->first();
            if (!$vehicle_type) {
                // Log the error
                Log::error('An error occurred while show the vehicle type', [
                    'id' => $id,
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'The invalid vehicle type'
                ]);

                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle type.');
            }
            return view('vehicle-type.show', compact('vehicle_type'));
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while show the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while show the vehicle type.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $vehicle_type = VehicleType::where('id', $id)->where('is_delete', 0)->first();
            if (!$vehicle_type) {
                // Log the error
                Log::error('An error occurred while edit the vehicle type', [
                    'id' => $id,
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'The invalid vehicle type'
                ]);

                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle type.');
            }
            return view('vehicle-type.edit', compact('vehicle_type'));
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while edit the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the vehicle type.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_type' => [
                    'required',
                    Rule::unique('vehicle_types')->where(function ($query) use ($id) {
                        return $query->where('id', '!=', $id)
                            ->where('is_delete', 0);
                    }),
                ],
                'is_active' => 'nullable'
            ], [
                'vehicle_type.required' => 'The vehicle type field is required.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the vehicle type
            $vehicle_type = VehicleType::find($id);
            if (!$vehicle_type) {
                Log::error('An error occurred while saving the vehicle type', [
                    'id' => $id,
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'The invalid vehicle type.',
                ]);

                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle type.');
            }
            $vehicle_type->vehicle_type = $request->vehicle_type;
            $vehicle_type->is_active = $request->is_active ? 1 : 0;
            $vehicle_type->update();

            Log::info('The vehicle type updated successfully.', [
                'id' => $id,
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
            ]);
            // Return success response
            return redirect('vehicle-type')->with('success', 'Vehicle type updated successfully.');
        } catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while update the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the vehicle type.');
        }
    }

    /**
     * @author shreyasm@neosao
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Find the vehicle type by ID
            $vehicle_type = VehicleType::find($id);

            // Check if the vehicle type exists
            if (!$vehicle_type) {
                Log::error('An error occurred while deleting the vehicle type', [
                    'user_id' => auth()->id(),
                    'function' => __FUNCTION__,
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'path' => __FILE__,
                    'exception' => 'Vehicle type not found.',
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Vehicle type not found.'
                ]);
            }

            // Soft delete the vehicle type by setting is_delete flag
            $vehicle_type->is_delete = 1;
            $vehicle_type->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            Log::error('An error occurred while deleting the vehicle type', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the vehicle type.'
            ]);
        }
    }
}
