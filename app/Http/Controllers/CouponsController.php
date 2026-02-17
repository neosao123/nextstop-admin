<?php

namespace App\Http\Controllers;

use Response;
use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Coupons;

class CouponsController extends Controller
{
    /**
     * Display a index page of the resource.
     * @author shreyasm@neosao
     */
    public function index()
    {
        try {
            return view('coupon.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the coupon index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the coupon list.');
        }
    }

    /**
     * Display a listing of the resource.
     * @author shreyasm@neosao
     */
	   public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$data = array();
			
			$filterArray = [
				'search_coupon_type' => $request->search_coupon_type,
				'search_status' => $request->search_status,
				'from_date' => $request->from_date ?? null,
				'to_date' => $request->to_date ?? null,
			];

			$filteredData = Coupons::filterCoupons($search, $limit, $offset, $filterArray);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('Coupon.Edit');
			$canView = Auth::guard('admin')->user()->can('Coupon.View');
			$canDelete = Auth::guard('admin')->user()->can('Coupon.Delete');
			$showActions = $canEdit || $canView || $canDelete;

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$startDate = Carbon::parse($row->coupon_start_date)->format('d-m-Y h:i:s A');
					$endDate = Carbon::parse($row->coupon_end_date)->format('d-m-Y h:i:s A');
					
					$dataRow = [];
					
					// Add action column only if user has any permissions
					if ($showActions) {
						$action = '<span class="text-start">
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="coupon-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="coupon-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('coupon/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('coupon/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete text-danger" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->coupon_code;
					$dataRow[] = $row->coupon_type === 'flat' ? __('index.coupon_type_flat') : __('index.coupon_type_percent');
					$dataRow[] = $row->coupon_amount_or_percentage;
					$dataRow[] = $row->coupon_cap_limit;
					$dataRow[] = $row->coupon_min_order_amount;
					$dataRow[] = $row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
					$dataRow[] = $startDate;
					$dataRow[] = $endDate;
					
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
			LogHelper::logError('An error occurred while the coupon list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the coupon list",
			], 500);
		}
	}

    /**
     * Show the form for creating a new resource.
     * @author shreyasm@neosao
     */
    public function create()
    {
        try {
            return view('coupon.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while create the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the coupon.');
        }
    }

    /**
     * Store a newly created resource in storage.
     * @author shreyasm@neosao
     */
    public function store(Request $request)
    {
        try {
            // Define the validation rules
            $rules = [
                'coupon_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('coupons', 'coupon_code')->where('is_delete', 0), // Ensure coupon_code is unique
                ],
                'coupon_type' => 'required|in:flat,percent', // Must be one of the valid types
                'coupon_amount_or_percentage' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->coupon_type == 'percent') {
                            if ($value < 0.01 || $value > 100) {
                                $fail('The ' . $attribute . ' must be between 0.01 and 100.');
                            }
                        }
                    },
                ],
                'coupon_cap_limit' => 'nullable|numeric|min:0', // Cap is optional but must be non-negative
                'coupon_min_order_amount' => 'required|numeric|min:0', // Minimum order amount must be non-negative
                'coupon_description' => 'nullable|string|max:1000', // Optional description with a character limit
                'coupon_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // Optional image with max size of 2MB
                'is_active' => 'nullable',
				'coupon_start_date' => 'required|date',
			    'coupon_end_date' => 'required|date|after_or_equal:coupon_start_date',
            ];

            $messages = [
                'coupon_code.required' => 'The coupon code field is required.',
                'coupon_code.unique' => 'The coupon code has already been taken.',
                'coupon_code.max' => 'The coupon code cannot exceed 50 characters.',
                'coupon_type.required' => 'The coupon type field is required.',
                'coupon_type.in' => 'The coupon type must be either flat or percent.',
                'coupon_amount_or_percentage.required' => 'The coupon amount or percentage field is required.',
                'coupon_amount_or_percentage.numeric' => 'The coupon amount or percentage must be a number.',
                'coupon_cap_limit.numeric' => 'The coupon cap limit must be a number.',
                'coupon_cap_limit.min' => 'The coupon cap limit must be at least 0.',
                'coupon_min_order_amount.required' => 'The minimum order amount field is required.',
                'coupon_min_order_amount.numeric' => 'The minimum order amount must be a number.',
                'coupon_min_order_amount.min' => 'The minimum order amount must be at least 0.',
                'coupon_description.string' => 'The coupon description must be a valid string.',
                'coupon_description.max' => 'The coupon description cannot exceed 1000 characters.',
                'coupon_image.image' => 'The coupon image must be a valid image file.',
                'coupon_image.mimes' => 'The coupon image must be a file of type: jpeg, jpg, png.',
                'coupon_image.max' => 'The coupon image size cannot exceed 2MB.',
				'coupon_start_date.required' => 'The coupon start date is required.',
				'coupon_start_date.date' => 'The  coupon start date must be a valid date.',
				'coupon_end_date.required' => 'The coupon end date is required.',
				'coupon_end_date.date' => 'The coupon end date must be a valid date.',
				'apply_on.required' => 'The apply on field is required.',
				'coupon_end_date.after_or_equal'=>'Coupon end date should be greater than or equal to coupon start date.',
            ];

            // Perform validation
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200); // Respond with validation errors
            }

            // Create and save the coupon
            $coupon = new Coupons;
            $coupon->coupon_code = $request->coupon_code;
            $coupon->coupon_type = $request->coupon_type;
            $coupon->coupon_amount_or_percentage = $request->coupon_amount_or_percentage;
            $coupon->coupon_cap_limit = $request->coupon_cap_limit ?? null;
            $coupon->coupon_min_order_amount = $request->coupon_min_order_amount;
            $coupon->coupon_description = $request->coupon_description ?? null;
			$coupon->coupon_start_date=date("Y-m-d",strtotime($request->coupon_start_date));
			$coupon->coupon_end_date=date("Y-m-d",strtotime($request->coupon_end_date));
			
            $coupon->is_active = $request->has('is_active') ? 1 : 0;
            $coupon->is_delete = 0;

            // Handle optional coupon image
            if ($request->hasFile('coupon_image')) {
                $file = $request->file('coupon_image');
                $imageName = 'coupon-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('coupon-images', $file, $imageName);
                $coupon->coupon_image = $path; // Save the path in the database
            }

            $coupon->save();
            LogHelper::logSuccess('The coupon added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $coupon->id);

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Coupon added successfully.',
                'coupon' => $coupon // Optionally return the created coupon data
            ], 200); // Respond with success
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response to the user
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the coupon.'
            ], 500); // Respond with error
        }
    }


    /**
     * Display the specified resource.
     * @author shreyasm@neosao
     */
    public function show(string $id)
    {
        try {
            $coupon = Coupons::where('id', $id)->where('is_delete', 0)->first();

            if (!$coupon) {
                // Log the error
                LogHelper::logError('An error occurred while view the coupon', 'The invalid coupon',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid coupon.');
            }
            return view('coupon.show', compact('coupon'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the coupon.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @author shreyasm@neosao
     */
    public function edit(string $id)
    {
        try {
            $coupon = Coupons::where('id', $id)->where('is_delete', 0)->first();
            if (!$coupon) {
                // Log the error
                LogHelper::logError('An error occurred while edit the coupon', 'The invalid coupon',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid coupon.');
            }
            return view('coupon.edit', compact('coupon'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the coupon.');
        }
    }

    /**
     * Update the specified resource in storage.
     * @author shreyasm@neosao
     */
    public function update(Request $request, string $id)
    {

        try {
            $rules = [
                'coupon_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('coupons', 'coupon_code')->where(function ($query) use ($id) {
                        return $query->where('is_delete', 0)->where('id', '!=', $id);
                    }),
                ],
                'coupon_type' => 'required|in:flat,percent', // Must be one of the valid types
                'coupon_amount_or_percentage' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->coupon_type == 'percent') {
                            if ($value < 0.01 || $value > 100) {
                                $fail('The ' . $attribute . ' must be between 0.01 and 100.');
                            }
                        }
                    },
                ],
                'coupon_cap_limit' => 'nullable|numeric|min:0', // Cap is optional but must be non-negative
                'coupon_min_order_amount' => 'required|numeric|min:0', // Minimum order amount must be non-negative
                'coupon_description' => 'nullable|string|max:1000', // Optional description with a character limit
                'coupon_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // Optional image with max size of 2MB
                'is_active' => 'nullable',
				'coupon_start_date' => 'required|date',
			    'coupon_end_date' => 'required|date|after_or_equal:coupon_start_date',
            ];

            $messages = [
                'coupon_code.required' => 'The coupon code field is required.',
                'coupon_code.unique' => 'The coupon code has already been taken.',
                'coupon_code.max' => 'The coupon code cannot exceed 50 characters.',
                'coupon_type.required' => 'The coupon type field is required.',
                'coupon_type.in' => 'The coupon type must be either flat or percent.',
                'coupon_amount_or_percentage.required' => 'The coupon amount or percentage field is required.',
                'coupon_amount_or_percentage.numeric' => 'The coupon amount or percentage must be a number.',
                'coupon_cap_limit.numeric' => 'The coupon cap limit must be a number.',
                'coupon_cap_limit.min' => 'The coupon cap limit must be at least 0.',
                'coupon_min_order_amount.required' => 'The minimum order amount field is required.',
                'coupon_min_order_amount.numeric' => 'The minimum order amount must be a number.',
                'coupon_min_order_amount.min' => 'The minimum order amount must be at least 0.',
                'coupon_description.string' => 'The coupon description must be a valid string.',
                'coupon_description.max' => 'The coupon description cannot exceed 1000 characters.',
                'coupon_image.image' => 'The coupon image must be a valid image file.',
                'coupon_image.mimes' => 'The coupon image must be a file of type: jpeg, jpg, png.',
                'coupon_image.max' => 'The coupon image size cannot exceed 2MB.',
				'coupon_start_date.required' => 'The coupon start date is required.',
				'coupon_start_date.date' => 'The  coupon start date must be a valid date.',
				'coupon_end_date.required' => 'The coupon end date is required.',
				'coupon_end_date.date' => 'The coupon end date must be a valid date.',
				'apply_on.required' => 'The apply on field is required.',
				'coupon_end_date.after_or_equal'=>'Coupon end date should be greater than or equal to coupon start date.',
            ];

            // Perform validation
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200); // Respond with validation errors
            }

            // Create and save the coupon
            $coupon = Coupons::findOrFail($id);
            $coupon->coupon_code = $request->coupon_code;
            $coupon->coupon_type = $request->coupon_type;
            $coupon->coupon_amount_or_percentage = $request->coupon_amount_or_percentage;
            $coupon->coupon_cap_limit = $request->coupon_cap_limit ?? null;
            $coupon->coupon_min_order_amount = $request->coupon_min_order_amount;
            $coupon->coupon_description = $request->coupon_description ?? null;
            $coupon->coupon_start_date=date("Y-m-d",strtotime($request->coupon_start_date));
			$coupon->coupon_end_date=date("Y-m-d",strtotime($request->coupon_end_date));
			
			$coupon->is_active = $request->has('is_active') ? 1 : 0;
            $coupon->is_delete = 0;

            // Handle optional coupon image
            if ($request->hasFile('coupon_image')) {
                if ($coupon->coupon_image != '') {
                    if (Storage::disk('public')->exists($coupon->coupon_image)) {
                        Storage::disk('public')->delete($coupon->coupon_image); // Delete the file
                    }
                }
                $file = $request->file('coupon_image');
                $imageName = 'coupon-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('coupon-images', $file, $imageName);
                $coupon->coupon_image = $path; // Save the path in the database
            }

            $coupon->save();
            LogHelper::logSuccess('The coupon updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $coupon->id);

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Coupon updated successfully.',
                'coupon' => $coupon // Optionally return the created coupon data
            ], 200); // Respond with success

        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response for AJAX request
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the coupon.',
                'error' => $ex->getMessage()
            ], 500); // Use 500 Internal Server Error status code
        }
    }

    /**
     * Remove the specified resource from storage.
     * @author shreyasm@neosao
     */
    public function destroy(string $id)
    {
        try {
            // Find the coupon type by ID
            $coupon = Coupons::find($id);

            // Check if the coupon type exists
            if (!$coupon) {
                LogHelper::logError('An error occurred while deleting the coupon', 'Coupon not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'Coupon not found.']);
            }

            // Soft delete the coupon by setting is_delete flag
            $coupon->is_delete = 1;
            $coupon->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the coupon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the coupon .']);
        }
    }

    /**
     * Remove the specified coupon image from storage.
     * @author shreyasm@neosao
     */
    public function deletecouponimage($id)
    {
        try {
            // Find the coupon by ID
            $coupon = Coupons::find($id);
            if ($coupon && $coupon->coupon_image) {
                // Check if the file exists in the storage disk and delete it
                if (Storage::disk('public')->exists($coupon->coupon_image)) {
                    Storage::disk('public')->delete($coupon->coupon_image); // Delete the file
                }
                $coupon->coupon_image = null;
                $coupon->save();
                LogHelper::logSuccess('The coupon image deleted successfully',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
            }
            LogHelper::logError('An error occurred while deleting the coupon image', 'Coupon or image not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'message' => 'Coupon or image not found.'], 404);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while deleting the coupon image', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Handle error and return response
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the coupon image'], 500);
        }
    }
}
