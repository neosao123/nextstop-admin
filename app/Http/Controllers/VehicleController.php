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
use App\Models\Vehicle;
use App\Models\VehicleType;


class VehicleController extends Controller
{

    /**
     * Display a index page of the resource.
     * @author shreyasm@neosao
     */
    public function index()
    {
        try {
            return view('vehicle.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the vehicle index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the vehicle list.');
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
				'search_vehicle_type' => $request->search_vehicle_type,
				'search_vehicle_max_load_capacity' => $request->search_vehicle_max_load_capacity,
				'search_vehicle_per_km_delivery_charge' => $request->search_vehicle_per_km_delivery_charge
			];

			$filteredData = Vehicle::filterVehicles($search, $limit, $offset, $filterArray);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('Vehicle.Edit');
			$canView = Auth::guard('admin')->user()->can('Vehicle.View');
			$canDelete = Auth::guard('admin')->user()->can('Vehicle.Delete');
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
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="vehicle-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="vehicle-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('vehicle/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('vehicle/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete text-danger" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->vehicle_name;
					$dataRow[] = $row->vehicleType->vehicle_type ?? 'N/A';
					$dataRow[] = $row->vehicle_dimensions;
					$dataRow[] = $row->vehicle_max_load_capacity;
					$dataRow[] = $row->vehicle_fixed_km;
					$dataRow[] = $row->vehicle_fixed_km_delivery_charge;
					$dataRow[] = $row->vehicle_per_km_delivery_charge;
					$dataRow[] = $row->vehicle_per_km_extra_delivery_charge;
					$dataRow[] = $row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
					$dataRow[] = $formattedDate;
					
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
			LogHelper::logError('An error occurred while the vehicle list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the vehicle list",
			], 500);
		}
	}


    public function excelDownloadVehicle(Request $request)
    {
        $search = $request->input('search.value') ?? "";
        $limit = $request->length ?? 0;
        $offset = $request->start ?? 0;

        $filterArray = [
            'search_vehicle_type' =>  $request->search_vehicle_type,
            'search_vehicle_max_load_capacity' =>  $request->search_vehicle_max_load_capacity,
            'search_vehicle_per_km_delivery_charge' => $request->search_vehicle_per_km_delivery_charge
        ];

        // Fetch vehicles with filtering
        $filteredData = Vehicle::filterVehicles($search, $limit, $offset, $filterArray); // Assuming you have a method for filtering
        $records = $filteredData['result'];

        // Prepare data for CSV export
        $csvData = [];
        foreach ($records as $row) {
            $status = $row->is_active == 1 ? 'Active' : 'In-Active';
            $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');

            $csvData[] = [
                $row->vehicle_name,
                $row->vehicleType->vehicle_type ?? 'N/A', // Assuming a relationship exists
                $row->vehicle_dimensions,
                $row->vehicle_max_load_capacity,
                $row->vehicle_per_km_delivery_charge,
                $row->vehicle_per_km_extra_delivery_charge,
                $status,
                $formattedDate,
            ];
        }
        $csvFileName = 'Vehicle.csv';
        $csvFile = fopen('php://temp', 'w+');
        // Add headers
        fputcsv($csvFile, [
            "Vehicle Name",
            "Vehicle Type",
            "Vehicle Dimensions (L X B X H)",
            "Vehicle Maximum Load Capacity (kgs)",
            "Vehicle Per KM Delivery Charge",
            "Vehicle Per KM Extra Delivery Charge",
            "Status",
            "Created Date",
        ]);

        // Add data rows
        foreach ($csvData as $row) {
            fputcsv($csvFile, $row);
        }

        rewind($csvFile);
        $csvContent = stream_get_contents($csvFile);
        fclose($csvFile);

        // Set response headers
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        ];

        return Response::make($csvContent, 200, $headers);
    }

    public function pdfDownloadVehicle(Request $request)
    {
        $search = $request->input('search.value') ?? "";
        $limit = $request->length ?? 0;
        $offset = $request->start ?? 0;

        $filterArray = [
            'search_vehicle_type' =>  $request->search_vehicle_type,
            'search_vehicle_max_load_capacity' =>  $request->search_vehicle_max_load_capacity,
            'search_vehicle_per_km_delivery_charge' => $request->search_vehicle_per_km_delivery_charge
        ];

        // Fetch vehicles with filtering
        $filteredData = Vehicle::filterVehicles($search, $limit, $offset, $filterArray); // Assuming you have a method for filtering
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
						</style>';
        $htmlContent .= '<table>';
        $htmlContent .= '<thead><tr><th>Vehicle Namee</th><th>Vehicle Type</th><th>Vehicle Dimensions (L X B X H)</th><th>Vehicle Maximum Load Capacity (kgs)</th><th>Vehicle Per KM Delivery Charge</th><th>Vehicle Per KM Extra Delivery Charge</th><th>Status</th><th>Created Date</th></tr></thead>';
        $htmlContent .= '<tbody>';

        foreach ($records as $row) {

            $status = $row->is_active == 1 ? 'Active' : 'In-Active';
            $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');


            $htmlContent .= '<tr>';
            $htmlContent .= '<td>' . $row->vehicle_name ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->vehicleType->vehicle_type ?? 'N/A' . '</td>';
            $htmlContent .= '<td>' . $row->vehicle_dimensions ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->vehicle_max_load_capacity ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->vehicle_per_km_delivery_charge ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->vehicle_per_km_extra_delivery_charge  ?? '-' . '</td>';
            $htmlContent .= '<td>' . $status . '</td>';
            $htmlContent .= '<td>' . $formattedDate . '</td>';
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody></table>';

        // Generate the PDF using a PDF library like dompdf or any PDF generator
        $pdf = PDF::loadHTML($htmlContent);

        return $pdf->download('Pending-driver.pdf');
    }

    /** 
     * @author shreyasm@neosao
     */
    public function importexcel()
    {
        try {
            return view('vehicle.import');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the goods type import page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the goods type import.');
        }
    }

    /** 
     * @author shreyasm@neosao
     */
    public function validateExcel(Request $request)
    {
        $rowArray = $request->convertedIntoArray; // Ensure this contains the parsed Excel rows
        $statusArrData = ['active', 'inactive']; // Valid statuses
        $errors = [];
        $returnRowArray = [];

        $errors = [];
        $duplicateVehicleNames = [];

        if (!empty($rowArray)) {
            // Count occurrences of vehicle names within the Excel data
            $vehicleNameCount = [];
            foreach ($rowArray as $index => $row) {
                $vehicleName = trim($row[2] ?? ''); // Assuming 'vehicle_name' is in column 2
                if (!empty($vehicleName)) {
                    $vehicleNameCount[$vehicleName][] = $index + 1; // Store row numbers where the name appears
                }
            }

            // Prepare duplicate vehicle name error messages
            foreach ($vehicleNameCount as $vehicleName => $rows) {
                if (count($rows) > 1) {
                    $duplicateVehicleNames[] = "Vehicle name '$vehicleName' is duplicated in rows: " . implode(', ', $rows) . ".";
                }
            }

            // Add duplicate error messages to the main error list
            if (!empty($duplicateVehicleNames)) {
                foreach ($duplicateVehicleNames as $duplicateError) {
                    $errors['duplicates'][] = $duplicateError;
                }
            }


            // dd($rowArray);
            foreach ($rowArray as $index => $row) {
                // Skip validation if the row is empty
                if (empty(array_filter($row))) {
                    continue;
                }

                // Prepare individual row data
                $rowData = [
                    'vehicle_type' => trim($row[1] ?? ''),
                    'vehicle_name' => trim($row[2] ?? ''),
                    'vehicle_dimensions' => trim($row[3] ?? ''),
                    'vehicle_max_load_capacity' => trim($row[4] ?? ''),
                    'vehicle_per_km_delivery_charge' => trim($row[5] ?? ''),
                    'vehicle_per_km_extra_delivery_charge' => trim($row[6] ?? ''),
                    'vehicle_description' => trim($row[7] ?? ''),
                    'vehicle_rule' => trim($row[8] ?? ''),
                    'status' => trim($row[9] ?? '')
                ];

                $rules = [
                    'vehicle_type' => [
                        'required',
                        'numeric',
                        Rule::exists('vehicle_types', 'id') // Replace 'vehicle_types' with your actual table name
                            ->where('is_delete', 0) // Optional: Add conditions (e.g., only active types)
                    ],
                    'vehicle_name' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('vehicles', 'vehicle_name')->where('is_delete', 0) // Replace 'vehicles' with your actual table name
                    ],
                    'vehicle_dimensions' => 'required|string|max:255',
                    'vehicle_max_load_capacity' => 'required|numeric|min:0',
                    'vehicle_per_km_delivery_charge' => 'required|numeric|min:0',
                    'vehicle_per_km_extra_delivery_charge' => 'required|numeric|min:0',
                    'vehicle_description' => 'nullable|string',
                    'vehicle_rule' => 'nullable|string',
                    'status' => ['required', Rule::in($statusArrData)]
                ];

                $messages = [
                    'vehicle_type.required' => "Row " . ($index + 1) . " - The vehicle type is required.",
                    'vehicle_type.numeric' => "Row " . ($index + 1) . " - The vehicle type must be a valid number.",
                    'vehicle_type.exists' => "Row " . ($index + 1) . " - The vehicle type is invalid.",
                    'vehicle_name.required' => "Row " . ($index + 1) . " - The vehicle name is required.",
                    'vehicle_name.string' => "Row " . ($index + 1) . " - The vehicle name must be a string.",
                    'vehicle_name.max' => "Row " . ($index + 1) . " - The vehicle name must not exceed 255 characters.",
                    'vehicle_name.unique' => "Row " . ($index + 1) . " - The vehicle name must be unique.",
                    'vehicle_dimensions.required' => "Row " . ($index + 1) . " - The vehicle dimensions are required.",
                    'vehicle_dimensions.string' => "Row " . ($index + 1) . " - The vehicle dimensions must be a string.",
                    'vehicle_dimensions.max' => "Row " . ($index + 1) . " - The vehicle dimensions must not exceed 255 characters.",
                    'vehicle_max_load_capacity.required' => "Row " . ($index + 1) . " - The max load capacity is required.",
                    'vehicle_max_load_capacity.numeric' => "Row " . ($index + 1) . " - The max load capacity must be a valid number.",
                    'vehicle_max_load_capacity.min' => "Row " . ($index + 1) . " - The max load capacity must be at least 0.",
                    'vehicle_per_km_delivery_charge.required' => "Row " . ($index + 1) . " - The per KM delivery charge is required.",
                    'vehicle_per_km_delivery_charge.numeric' => "Row " . ($index + 1) . " - The per KM delivery charge must be a valid number.",
                    'vehicle_per_km_delivery_charge.min' => "Row " . ($index + 1) . " - The per KM delivery charge must be at least 0.",
                    'vehicle_per_km_extra_delivery_charge.required' => "Row " . ($index + 1) . " - The per KM extra delivery charge is required.",
                    'vehicle_per_km_extra_delivery_charge.numeric' => "Row " . ($index + 1) . " - The per KM extra delivery charge must be a valid number.",
                    'vehicle_per_km_extra_delivery_charge.min' => "Row " . ($index + 1) . " - The per KM extra delivery charge must be at least 0.",
                    'vehicle_description.string' => "Row " . ($index + 1) . " - The vehicle description must be a string.",
                    'vehicle_rule.string' => "Row " . ($index + 1) . " - The vehicle rule must be a string.",
                    'status.required' => "Row " . ($index + 1) . " - The status is required.",
                    'status.in' => "Row " . ($index + 1) . " - The status must be either active or inactive instead of " . $rowData['status'],
                ];

                // Validate the row data
                $validator = Validator::make($rowData, $rules, $messages);

                if ($validator->fails()) {
                    // Collect errors for the row
                    $errors[$index + 1] = array_merge($errors[$index + 1] ?? [], $validator->errors()->all());
                    $returnRowArray[] = $index;
                }
            }
        }

        // Prepare error messages
        $msg = '';

        if (!empty($errors['duplicates'])) {
            foreach ($errors['duplicates'] as $duplicateError) {
                $msg .= "<li><p class='mb-1'><span class='text-warning'>$duplicateError</span></p></li>";
            }
        }

        foreach ($errors as $rowNumber => $rowErrors) {
            if ($rowNumber === 'duplicates') {
                continue; // Skip duplicates (already added above)
            }
            foreach ($rowErrors as $error) {
                $msg .= "<li><p class='mb-1'><span class='text-warning'>$error</span></p></li>";
            }
        }

        if ($msg != '') {
            $msg = '<ul>' . $msg . '</ul>';
        }

        // Return response
        return response()->json([
            "msg" => $msg,
            "rowArr" => json_encode($returnRowArray)
        ]);
    }


    /** 
     * @author shreyasm@neosao
     */
    public function uploadexcel(Request $r)
    {
        // Define the upload path
        $path = 'excel/';

        // Get the uploaded file
        $fileItem = $r->file('uploadFile');
        $filenameItemImage = $fileItem->getClientOriginalName();
        $inputFileName = $path . $filenameItemImage;

        try {
            // Check if the file already exists and delete it if so
            if (file_exists($inputFileName)) {
                unlink($inputFileName);
            }

            // Move the uploaded file to the specified path
            $fileItem->move($path, $filenameItemImage);

            // Identify the file type and create a reader
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);

            // Load the Excel file
            $objPHPExcel = $objReader->load($inputFileName);
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

            // Decode row exceptions if any
            $rowExcepts = json_decode($r->rowExcepts, true) ?? [];

            // Remove unwanted rows based on exceptions
            if (!empty($rowExcepts)) {
                foreach ($rowExcepts as $row) {
                    unset($allDataInSheet[$row + 2]);  // Skipping the header row
                }
            }

            // Prepare for data insertion
            $inserdata = [];
            $total_line = 0;
            $cntr = 0;

            foreach ($allDataInSheet as $i => $value) {
                // Skip empty rows
                if (count(array_filter($value)) == 0) {
                    continue;
                }

                $total_line++;

                // Prepare the data for insertion
                $inserdata[$i] = [
                    'vehicle_type_id' => $value['A'],
                    'vehicle_name' => $value['B'],
                    'vehicle_dimensions' => $value['C'],
                    'vehicle_max_load_capacity' => $value['D'],
                    'vehicle_per_km_delivery_charge' => $value['E'],
                    'vehicle_per_km_extra_delivery_charge' => $value['F'],
                    'vehicle_description' => $value['G'],
                    'vehicle_rules' => $value['H'],
                    'is_active' => $value['I'] === 'active' ? 1 : 0,
                ];

                // Insert the record and track success
                $result = Vehicle::create($inserdata[$i]);

                if ($result) {
                    $cntr++;
                }

                // Add the result ID to the response array
                $itemArr[] = ['id' => $result ? $result->id : null];
            }

            // Prepare the final response
            if ($total_line === $cntr) {
                $response = [
                    'status' => true,
                    'text' => "Total: $total_line record" . ($total_line === 1 ? ' is' : 's') . ' saved',
                ];
            } else {
                $response = [
                    'status' => false,
                    'text' => "Total Records: $total_line Successful: $cntr Unsuccessful: " . ($total_line - $cntr),
                ];
            }

            return response()->json($response);
        } catch (Exception $e) {
            // Handle exceptions and return error response
            LogHelper::logError('An error occurred while the uploading excel import file', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'status' => false,
                'text' => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }


    /**
     * Fetching the vehicle list.
     * @author shreyasm@neosao
     */
    public function vehicletype(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');
            $result = VehicleType::where('vehicle_type', 'like', '%' . $search . '%')
                ->where('is_delete', 0)
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $html[] = ['id' => $item->id, 'text' => $item->vehicle_type];
                }
            }
            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the vehicle type dropdown list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @author shreyasm@neosao
     */
    public function create()
    {
        try {
            return view('vehicle.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while create the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the vehicle.');
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
                'vehicle_type' => 'required',
                'vehicle_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('vehicles', 'vehicle_name')->where('is_delete', 0), // Replace 'vehicles' with your actual table name
                ],
                'vehicle_dimensions' => 'required|string|max:255',
                'vehicle_max_load_capacity' => 'required|numeric|min:0',
				'vehicle_fixed_km_delivery_charge' => 'required|numeric|min:0',
                'vehicle_per_km_delivery_charge' => 'required|numeric|min:0',
                'vehicle_per_km_extra_delivery_charge' => 'required|numeric|min:0',
				'vehicle_fixed_km' => 'required|integer|min:0',
                'vehicle_icon' => 'nullable|image|mimes:jpeg,jpg,png', // Optional image validation
                'is_active' => 'nullable'
            ];

            // Define the custom error messages
            $messages = [
                'vehicle_type.required' => 'The vehicle type field is required.',
                'vehicle_name.required' => 'The vehicle name field is required.',
                'vehicle_dimensions.required' => 'The vehicle dimensions field is required.',
                'vehicle_max_load_capacity.required' => 'The max load capacity field is required.',
                'vehicle_per_km_delivery_charge.required' => 'The per KM delivery charge field is required.',
                'vehicle_per_km_extra_delivery_charge.required' => 'The per KM extra delivery charge field is required.',
                'vehicle_name.unique' => 'The vehicle name has already been taken.',
                'vehicle_name.max' => 'The vehicle name cannot be more than 255 characters.',
                'vehicle_dimensions.string' => 'The vehicle dimensions must be a string.',
                'vehicle_dimensions.max' => 'The vehicle dimensions cannot be more than 255 characters.',
                'vehicle_max_load_capacity.numeric' => 'The max load capacity must be a number.',
                'vehicle_max_load_capacity.min' => 'The max load capacity must be at least 0.',
				
				'vehicle_per_fixed_delivery_charge.numeric' => 'The fixed KM delivery charge must be a number.',
                'vehicle_per_fixed_delivery_charge.min' => 'The fixed KM delivery charge must be at least 0.',
				
                'vehicle_per_km_delivery_charge.numeric' => 'The per KM delivery charge must be a number.',
                'vehicle_per_km_delivery_charge.min' => 'The per KM delivery charge must be at least 0.',
                'vehicle_per_km_extra_delivery_charge.numeric' => 'The per KM extra delivery charge must be a number.',
                'vehicle_per_km_extra_delivery_charge.min' => 'The per KM extra delivery charge must be at least 0.',
                'vehicle_icon.image' => 'The vehicle icon must be an image.',
                'vehicle_icon.mimes' => 'The vehicle icon must be a file of type: jpeg, jpg, png.',
                'is_active.nullable' => 'The is active field is optional.',
				'vehicle_fixed_km.required' => 'The fixed km value is required',
				'vehicle_fixed_km.integer' => 'The fixed km must be a whole number',
				'vehicle_fixed_km.min' => 'The fixed km cannot be negative',
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

            // Create and save the vehicle
            $vehicle = new Vehicle;
            $vehicle->vehicle_type_id = $request->vehicle_type;
            $vehicle->vehicle_name = $request->vehicle_name;
            $vehicle->vehicle_dimensions = $request->vehicle_dimensions;
            $vehicle->vehicle_max_load_capacity = $request->vehicle_max_load_capacity;
			$vehicle->vehicle_fixed_km_delivery_charge = $request->vehicle_fixed_km_delivery_charge;
            $vehicle->vehicle_per_km_delivery_charge = $request->vehicle_per_km_delivery_charge;
            $vehicle->vehicle_per_km_extra_delivery_charge = $request->vehicle_per_km_extra_delivery_charge;
            $vehicle->vehicle_description = $request->vehicle_description;
            $vehicle->vehicle_rules = $request->vehicle_rules;
			$vehicle->vehicle_fixed_km=$request->vehicle_fixed_km;
            $vehicle->is_active = $request->is_active ? 1 : 0;

            // Handle the icon upload
            if ($request->hasFile('vehicle_icon')) {
                $file = $request->file('vehicle_icon');
                $imageName = 'vehicle-icon-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('vehicle-icon', $file, $imageName);
                $vehicle->vehicle_icon = $path; // Save the image name in the database
            }

            $vehicle->save();
            LogHelper::logSuccess('The vehicle added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $vehicle->id);

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Vehicle added successfully.',
                'vehicle' => $vehicle // Optionally return the created vehicle data
            ], 200); // Respond with success
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response to the user
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the vehicle.'
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
            $vehicle = Vehicle::where('id', $id)->where('is_delete', 0)->first();
            $vehicle_type = VehicleType::get();

            if (!$vehicle) {
                // Log the error
                LogHelper::logError('An error occurred while view the vehicle', 'The invalid vehicle',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle.');
            }
            if ($vehicle_type->isEmpty()) {
                // Log the error
                LogHelper::logError('An error occurred while view the vehicle', 'The invalid vehicle type',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle type of selected vehicle.');
            }

            return view('vehicle.show', compact('vehicle', 'vehicle_type'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the vehicle.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @author shreyasm@neosao
     */
    public function edit(string $id)
    {
        try {
            $vehicle = Vehicle::where('id', $id)->where('is_delete', 0)->first();
            $vehicle_type = VehicleType::get();

            if (!$vehicle) {
                // Log the error
                LogHelper::logError('An error occurred while edit the vehicle', 'The invalid vehicle',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle.');
            }
            if ($vehicle_type->isEmpty()) {
                // Log the error
                LogHelper::logError('An error occurred while edit the vehicle', 'The invalid vehicle type',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid vehicle type of selected vehicle.');
            }

            return view('vehicle.edit', compact('vehicle', 'vehicle_type'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the vehicle.');
        }
    }

    /**
     * Update the specified resource in storage.
     * @author shreyasm@neosao
     */
    public function update(Request $request, string $id)
    {
        try {
            // Define the validation rules
            $rules = [
                'id' => 'required',
                'vehicle_type' => 'required',
                'vehicle_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('vehicles', 'vehicle_name')->where('is_delete', 0)->ignore($id) // Replace 'vehicles' with your actual table name
                ],
				'vehicle_fixed_km' => 'required|integer|min:0',
				'vehicle_fixed_km_delivery_charge' => 'required|numeric|min:0',
                'vehicle_dimensions' => 'required|string|max:255',
                'vehicle_max_load_capacity' => 'required|numeric|min:0',
                'vehicle_per_km_delivery_charge' => 'required|numeric|min:0',
                'vehicle_per_km_extra_delivery_charge' => 'required|numeric|min:0',
                'vehicle_icon' => 'nullable|image|mimes:jpeg,jpg,png', // Optional image validation
                'is_active' => 'nullable'
            ];

            // Define the custom error messages
            $messages = [
                'id.required' => 'The ID is required',
                'vehicle_type.required' => 'The vehicle type field is required.',
                'vehicle_name.required' => 'The vehicle name field is required.',
                'vehicle_dimensions.required' => 'The vehicle dimensions field is required.',
                'vehicle_max_load_capacity.required' => 'The max load capacity field is required.',
                'vehicle_per_km_delivery_charge.required' => 'The per KM delivery charge field is required.',
                'vehicle_per_km_extra_delivery_charge.required' => 'The per KM extra delivery charge field is required.',
                'vehicle_name.unique' => 'The vehicle name has already been taken.',
                'vehicle_name.max' => 'The vehicle name cannot be more than 255 characters.',
                'vehicle_dimensions.string' => 'The vehicle dimensions must be a string.',
                'vehicle_dimensions.max' => 'The vehicle dimensions cannot be more than 255 characters.',
                'vehicle_max_load_capacity.numeric' => 'The max load capacity must be a number.',
                'vehicle_max_load_capacity.min' => 'The max load capacity must be at least 0.',
                'vehicle_per_km_delivery_charge.numeric' => 'The per KM delivery charge must be a number.',
                'vehicle_per_km_delivery_charge.min' => 'The per KM delivery charge must be at least 0.',
                'vehicle_per_km_extra_delivery_charge.numeric' => 'The per KM extra delivery charge must be a number.',
                'vehicle_per_km_extra_delivery_charge.min' => 'The per KM extra delivery charge must be at least 0.',
                'vehicle_icon.image' => 'The vehicle icon must be an image.',
                'vehicle_icon.mimes' => 'The vehicle icon must be a file of type: jpeg, jpg, png.',
                'is_active.nullable' => 'The is active field is optional.',
				'vehicle_fixed_km.required' => 'The fixed km value is required',
				'vehicle_fixed_km.integer' => 'The fixed km must be a whole number',
				'vehicle_fixed_km.min' => 'The fixed km cannot be negative',
					
				'vehicle_per_fixed_delivery_charge.numeric' => 'The fixed KM delivery charge must be a number.',
                'vehicle_per_fixed_delivery_charge.min' => 'The fixed KM delivery charge must be at least 0.',
				
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

            // Find the vehicle
            $vehicle = Vehicle::findOrFail($id); // Ensure vehicle exists

            // Update vehicle attributes
            $vehicle->vehicle_type_id = $request->vehicle_type;
            $vehicle->vehicle_name = $request->vehicle_name;
            $vehicle->vehicle_dimensions = $request->vehicle_dimensions;
            $vehicle->vehicle_max_load_capacity = $request->vehicle_max_load_capacity;
            $vehicle->vehicle_fixed_km_delivery_charge = $request->vehicle_fixed_km_delivery_charge;
			$vehicle->vehicle_per_km_delivery_charge = $request->vehicle_per_km_delivery_charge;
            $vehicle->vehicle_per_km_extra_delivery_charge = $request->vehicle_per_km_extra_delivery_charge;
            $vehicle->vehicle_description = $request->vehicle_description;
            $vehicle->vehicle_rules = $request->vehicle_rules;
            $vehicle->vehicle_fixed_km=$request->vehicle_fixed_km;
			$vehicle->is_active = $request->is_active ? 1 : 0;

            // Handle the image upload
            if ($request->hasFile('vehicle_icon')) {
                $file = $request->file('vehicle_icon');
                $imageName = 'vehicle-icon-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('vehicle-icon', $file, $imageName);
                $vehicle->vehicle_icon = $path; // Save the image name in the database
            }

            $vehicle->save();

            // Log success
            LogHelper::logSuccess('The vehicle updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $vehicle->id);

            // Return success response for AJAX request
            return response()->json([
                'status' => 200,
                'message' => 'Vehicle updated successfully.',
                'data' => $vehicle
            ], 200); // Use 200 OK status code

        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response for AJAX request
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the vehicle.',
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
            // Find the vehicle type by ID
            $vehicle = Vehicle::find($id);

            // Check if the vehicle type exists
            if (!$vehicle) {
                LogHelper::logError('An error occurred while deleting the vehicle', 'Vehicle not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'Vehicle not found.']);
            }

            // Soft delete the vehicle by setting is_delete flag
            $vehicle->is_delete = 1;
            $vehicle->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the vehicle', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the vehicle .']);
        }
    }

    /**
     * Remove the specified vehicle image from storage.
     * @author shreyasm@neosao
     */
    public function deletevehicleicon($id)
    {
        try {
            // Find the vehicle by ID
            $vehicle = Vehicle::find($id);
            if ($vehicle && $vehicle->vehicle_icon) {
                // Check if the file exists in the storage disk and delete it
                if (Storage::disk('public')->exists($vehicle->vehicle_icon)) {
                    Storage::disk('public')->delete($vehicle->vehicle_icon); // Delete the file
                }
                $vehicle->vehicle_icon = null;
                $vehicle->save();
                LogHelper::logSuccess('The vehicle icon deleted successfully',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                return response()->json(['success' => true, 'message' => 'Icon deleted successfully.']);
            }
            LogHelper::logError('An error occurred while deleting the vehicle icon', 'Vehicle or icon not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'message' => 'Vehicle or icon not found.'], 404);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while deleting the vehicle icon', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Handle error and return response
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the vehicle icon'], 500);
        }
    }
}
