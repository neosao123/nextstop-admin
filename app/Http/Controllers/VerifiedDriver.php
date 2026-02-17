<?php

namespace App\Http\Controllers;

use Response;
use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\ServiceableZone;
use App\Models\DriverBankDetails;
use App\Models\DriverTrainingVideo;
use App\Models\DriverVehicleDetails;
use App\Models\DriverDocumentDetails;
use App\Models\DriverTrainingVideoDetails;
use App\Models\DriverVehicleDocumentDetails;
use App\Models\TrainingVideo;
use App\Classes\Notificationlibv_3;
use Illuminate\Support\Facades\Log;
use App\Models\Driver as DriverModel;

class VerifiedDriver extends Controller
{
    /**
     * Display a index page of the resource.
     * @author shreyasm@neosao
     */
    public function index()
    {
        try {
            return view('verified-driver.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the verified driver index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the verified driver  list.');
        }
    }

    /**
     * Fetching the vehicle list.
     * @author seemashelar@neosao
     */
    public function vehicle(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');
            $result = Vehicle::where('vehicle_name', 'like', '%' . $search . '%')
                ->where('is_delete', 0)
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $html[] = ['id' => $item->id, 'text' => $item->vehicle_name];
                }
            }
            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the vehicle dropdown list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
        }
    }

    /**
     * Fetching the servicable zone list.
     * @author shreyasm@neosao
     */
    public function servicablelocation(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');
            $result = ServiceableZone::where('serviceable_zone_name', 'like', '%' . $search . '%')
                ->where('is_delete', 0)
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $html[] = ['id' => $item->id, 'text' => $item->serviceable_zone_name];
                }
            }
            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the servicable zone dropdown list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
        }
    }

    /**
     * @author shreyasm@neosao
     */
    private function showStatus($case)
    {
        $verification_status = '';
        switch ($case) {
            case 0:
                $verification_status = '<div><span class="badge rounded-pill badge-soft-warning">' . __('index.pending') . '</span></div>';
                break;
            case 1:
                $verification_status = '<div><span class="badge rounded-pill badge-soft-success">' . __('index.verified') . '</span></div>';
                break;
            case 2:
                $verification_status = '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.rejected') . '</span></div>';
                break;
            default:
                $verification_status = '<div><span class="badge rounded-pill badge-soft-secondary"> - </span></div>';
                break;
        }
        return $verification_status;
    }

    /**
     * @author shreyasm@neosao
     */
    private function showStatusForExcelExport($case)
    {
        $verification_status = '';
        switch ($case) {
            case 0:
                $verification_status =  __('index.pending');
                break;
            case 1:
                $verification_status =  __('index.verified');
                break;
            case 2:
                $verification_status =  __('index.rejected');
                break;
            default:
                $verification_status =  '-';
                break;
        }
        return $verification_status;
    }

    /**
     * Display a verified listing of the resource.
     * @author shreyasm@neosao
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search.value') ?? "";
            $limit = $request->length;
            $offset = $request->start;
            $srno = $offset + 1;
            $data = array();


            // Filter Parameters
            $filterArray = [
                'search_name' => $request->search_name ?? '',
                'search_driver_vehicle' => $request->search_driver_vehicle ?? '',
                'search_driver_serviceable_location' => $request->search_driver_serviceable_location ?? '',
                'search_account_status' => $request->search_account_status ?? '',
                'search_verification_status_type' => $request->search_verification_status_type ?? [],
                'search_verification_status' => $request->search_verification_status ?? '',
            ];

            // Fetch driver with filtering
            $filteredData = DriverModel::filterVerifiedDriver($search, $limit, $offset, $filterArray); // Assuming you have a method for filtering
            $total = $filteredData['totalRecords'];
            $result = $filteredData['result'];

            $canEdit = Auth::guard('admin')->user()->can('Pending-Driver.View-Edit');
            $canView = Auth::guard('admin')->user()->can('Pending-Driver.Delete');
            $canDelete = Auth::guard('admin')->user()->can('Pending-Driver.Block');
            $showActions = $canEdit || $canView || $canDelete;

            // dd($result);
            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y h:i:s A');
                    $dataRow = [];

                    // Check permissions for actions
                    if ($showActions) {
                        if ($row->is_driver_delete == 0) {
                            $action = '
                        <span class="text-start">
                            <div class="dropdown font-sans-serif position-static">
                                <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="vehicle-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
                                    aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="vehicle-dropdown-' . $row->id . '">
                                    <div class="bg-white py-2">';

                            if (Auth::guard('admin')->user()->can('Verified-Driver.View-Edit')) {
                                $action .= '<a class="dropdown-item btn-view" href="' . url('driver/verified/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view_or_edit') . '</a>';
                            }

                            if (Auth::guard('admin')->user()->can('Verified-Driver.Delete')) {
                                $action .= '<a class="dropdown-item btn-delete text-danger" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
                            }
                            if (Auth::guard('admin')->user()->can('Verified-Driver.Block')) {
                                if ($row->is_driver_block == 1) {
                                    $action .= '<a class="dropdown-item btn-unblock text-success" data-id="' . $row->id . '"> <i class="fas fa-user-check"></i> ' . __('index.unblock') . '</a>';
                                } else {
                                    $action .= '<a class="dropdown-item btn-block text-danger" data-id="' . $row->id . '"> <i class="fas fa-user-slash"></i> ' . __('index.block') . '</a>';
                                }
                            }
                            if (Auth::guard('admin')->user()->can('Verified-Driver.Rating')) {
                                $action .= '<a class="dropdown-item" href="' . url('driver/verified/rating/' . $row['id']) . '"> <i class="fas fa-star"></i>Rating</a>';
                            }
                        }
                        $action .= '</div></div></div></span>';
                        $dataRow[] = $action;
                    }

                    // Set account status
                    $account_status = '';
                    if ($row->is_driver_delete == 1) {
                        $account_status = '<div><span class="badge rounded-pill badge-soft-dark">' . __('index.deleted') . '</span></div>';
                    } else {
                        $account_status = $row->is_active == 1
                            ? '<div><span class="badge rounded-pill badge-soft-success">' . __('index.active') . '</span></div>'
                            : '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.in_active') . '</span></div>';
                    }

                    if ($row->driver_document_verification_status == 1 && $row->driver_vehicle_document_verification_status == 1) {
                        $driver_document_verification_status = $this->showStatus(1);
                    } elseif ($row->driver_document_verification_status == 2 && $row->driver_vehicle_document_verification_status == 2) {
                        $driver_document_verification_status = $this->showStatus(2);
                    } else {
                        $driver_document_verification_status = $this->showStatus(0);
                    }
                    $driver_vehicle_verification_status = $this->showStatus($row->driver_vehicle_verification_status);
                    $driver_video_training_verification_status = $this->showStatus($row->driver_training_video_verification_status);

                    $admin_status = $this->showStatus($row->admin_verification_status);
                    // Append data to the array
                    $dataRow = array_merge($dataRow, [
                        ucwords($row->driver_first_name .' '. $row->driver_last_name ?? '-'),
                        optional($row->vehicleDetails)->vehicle->vehicle_name ?? '-',
                        optional($row->serviceableZones)->serviceable_zone_name ?? '-',
                        $row->driver_phone ?? '-',
                        $row->driver_email ?? '-',
                        $row->driver_wallet ?? '0.00',
                        $driver_document_verification_status,
                        $driver_vehicle_verification_status,
                        $driver_video_training_verification_status,
                        $admin_status,
                        $account_status,
                        $formattedDate
                    ]);

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
            LogHelper::logError('An error occurred while the driver list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the driver list",
            ], 500);
        }
    }

    /**
     * exporting to excel a verified listing of the resource.
     * @author shreyasm@neosao
     */
    public function excelDownloadVerifiedDrivers(Request $request)
    {
        // Retrieve filters from request, similar to the list function
        $search = $request->input('search.value') ?? "";
        $limit = $request->length ?? 0;
        $offset = $request->start ?? 0;

        $filterArray = [
            'search_name' => $request->search_name ?? '',
            'search_driver_vehicle' => $request->search_driver_vehicle ?? '',
            'search_driver_serviceable_location' => $request->search_driver_serviceable_location ?? '',
            'search_account_status' => $request->search_account_status ?? '',
            'search_verification_status_type' => $request->search_verification_status_type ?? [],
            'search_verification_status' => $request->search_verification_status ?? '',
        ];

        // Fetch filtered data
        $filteredData = DriverModel::filterVerifiedDriver($search, $limit, $offset, $filterArray);
        $records = $filteredData['result'];

        // Prepare data for CSV export
        $csvData = [];
        foreach ($records as $row) {
            if ($row->is_driver_delete == 1) {
                $status = 'Deleted';
            } else {
                $status = $row->is_active == 1
                    ? 'Active'
                    : 'In-Active';
            }
            $driverDocumentStatus = $this->showStatusForExcelExport($row->driver_document_verification_status);
            $driverVehicleStatus = $this->showStatusForExcelExport($row->driver_vehicle_verification_status);
            $driverTrainingStatus = $this->showStatusForExcelExport($row->driver_training_video_verification_status);
            $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');

            $csvData[] = [
                $row->driver_first_name ?? '-',
                $row->driver_last_name ?? '-',
                optional(optional($row->vehicleDetails)->vehicle)->vehicle_name ?? '-',
                optional($row->serviceableZones)->serviceable_zone_name ?? '-',
                $row->driver_phone ?? '-',
                $row->driver_email ?? '-',
                $row->driver_wallet,
                $driverDocumentStatus,
                $driverVehicleStatus,
                $driverTrainingStatus,
                $status,
                $formattedDate,
            ];
        }
        $csvFileName = 'Verified-Partner.csv';
        $csvFile = fopen('php://temp', 'w+');
        // Add headers
        fputcsv($csvFile, [
            "First Name",
            "Last Name",
            "Vehicle",
            "Serviceable Zone",
            "Phone",
            "Email",
            "Wallet",
            "Document Verification Status",
            "Vehicle Verification Status",
            "Training Verification Status",
            "Account Status",
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

    public function pdfDownloadVerifiedDriver(Request $request)
    {
        $search = $request->input('search.value') ?? "";
        $limit = $request->length ?? 0;
        $offset = $request->start ?? 0;

        $filterArray = [
            'search_name' => $request->search_name ?? '',
            'search_driver_vehicle' => $request->search_driver_vehicle ?? '',
            'search_driver_serviceable_location' => $request->search_driver_serviceable_location ?? '',
            'search_account_status' => $request->search_account_status ?? '',
            'search_verification_status_type' => $request->search_verification_status_type ?? [],
            'search_verification_status' => $request->search_verification_status ?? '',
        ];

        // Fetch filtered data
        $filteredData = DriverModel::filterVerifiedDriver($search, $limit, $offset, $filterArray);
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
        $htmlContent .= '<thead><tr><th>First Name</th><th>Last Name</th><th>Vehicle</th><th>Serviceable Zone</th><th>Phone</th><th>Email</th><th>Wallet</th><th>Document Verification Status</th><th>Vehicle Verification Status</th><th>Training Verification Status</th><th>Account Status</th><th>Created Date</th></tr></thead>';
        $htmlContent .= '<tbody>';

        foreach ($records as $row) {

            if ($row->is_driver_delete == 1) {
                $status = 'Deleted';
            } else {
                $status = $row->is_active == 1
                    ? 'Active'
                    : 'In-Active';
            }

            $driverDocumentStatus = $this->showStatusForExcelExport($row->driver_document_verification_status);
            $driverVehicleStatus = $this->showStatusForExcelExport($row->driver_vehicle_verification_status);
            $driverTrainingStatus = $this->showStatusForExcelExport($row->driver_training_video_verification_status);
            $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i:s A');

            $htmlContent .= '<tr>';
            $htmlContent .= '<td>' . $row->driver_first_name ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->driver_last_name ?? '-' . '</td>';
            $htmlContent .= '<td>' . optional(optional($row->vehicleDetails)->vehicle)->vehicle_name ?? '-' . '</td>';
            $htmlContent .= '<td>' . optional($row->serviceableZones)->serviceable_zone_name ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->driver_phone ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->driver_email ?? '-' . '</td>';
            $htmlContent .= '<td>' . $row->driver_wallet ?? '-' . '</td>';
            $htmlContent .= '<td>' . $driverDocumentStatus ?? '-' . '</td>';
            $htmlContent .= '<td>' . $driverVehicleStatus ?? '-' . '</td>';
            $htmlContent .= '<td>' . $driverTrainingStatus ?? '-' . '</td>';
            $htmlContent .= '<td>' . $status . '</td>';
            $htmlContent .= '<td>' . $formattedDate . '</td>';
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody></table>';

        // Generate the PDF using a PDF library like dompdf or any PDF generator
        $pdf = PDF::loadHTML($htmlContent)->setPaper('a4', 'landscape');

        return $pdf->download('Verified-partner.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     * @author shreyasm@neosao
     */
    public function viewOrEdit(string $id)
    {
        try {
            //$driver = DriverModel::where('id', $id)->with('serviceableZones')->where('is_delete', 0)->first();
            $driver = DriverModel::where('id', $id)->with('serviceableZones')->first();
            $driver_personal_document = DriverDocumentDetails::where('driver_id', $id)->where('is_delete', 0)->get();
            $driver_bank_details = DriverBankDetails::where('driver_id', $id)->where('is_delete', 0)->first();


            //get driver vehicle details
            $driver_vehicle_details = DriverVehicleDetails::with('vehicle')->where('driver_id', $id)->where('is_delete', 0)->first();
            $driver_vehicle_document_deatils = DriverVehicleDocumentDetails::where('driver_id', $id)->where('is_delete', 0)->get();

            //get driver training video details

            $driver_training_video = DriverTrainingVideo::where("is_delete", 0)->where('driver_id', $id)->first();
            $driver_training_video_deatils = TrainingVideo::leftJoin('driver_training_videos_details', function ($join) use ($id) {
                $join->on('training_videos.id', '=', 'driver_training_videos_details.training_video_id')
                    ->where('driver_training_videos_details.driver_id', '=', $id)
                    ->where('driver_training_videos_details.is_delete', '=', 0);
            })
                ->where('training_videos.is_delete', 0)
                ->where('training_videos.is_active', 1)
                ->select(
                    'training_videos.id as video_id',
                    'training_videos.video_title',
                    'training_videos.video_path',
                    'driver_training_videos_details.checked_status',
                    'driver_training_videos_details.id as details_id'
                )
                ->get();
            if (!$driver) {
                // Log the error
                LogHelper::logError('An error occurred while view-or-edit the driver', 'The invalid driver',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver.');
            }
            return view('verified-driver.view-or-edit', compact('driver', 'driver_vehicle_details', 'driver_personal_document', 'driver_bank_details', 'driver_vehicle_document_deatils', 'driver_training_video', 'driver_training_video_deatils'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view-or-edit the driver', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view-or-edit the driver.');
        }
    }

    /**
     * Verification of Personal Details.
     */
    public function verifyPersonalDetails(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'personal_verification_status' => [
                    'required',
                    'in:approve,reject',
                ],
                'personal_verification_reason' => [
                    'required_if:personal_verification_status,reject',
                ],
                'document_id' => 'array' // Ensure `document_id` is an array if it is provided
            ], [
                'personal_verification_status.required' => 'Verification status is required.',
                'personal_verification_status.in' => 'Verification status must be either "approve" or "reject".',
                'personal_verification_reason.required_if' => 'Verification reason is required if the status is "reject".',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $verification_status = $request->personal_verification_status == 'approve' ? 1 : 2;

            // Retrieve the driver record
            $driver = DriverModel::find($id);
            if (!$driver) {
                return redirect()->back()->with('error', 'driver not found.');
            }

            // Retrieve or initialize bank details
            $bank_details = DriverBankDetails::where('driver_id', $id)->first();
            if (!$bank_details) {
                LogHelper::logError('Bank details not found for specific driver ID', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                return redirect()->back()->with('error', 'Bank details not found for the driver.');
            }

            // Update verification status for bank details and driver
            $bank_details->is_bank_account_verified = $verification_status;
            $bank_details->bank_verified_by = Auth::user()->id;
            $bank_details->bank_verified_at = now();
            $bank_details->bank_verification_reason = $request->personal_verification_reason ?? '';
            $driver->driver_document_verification_status = $verification_status;
            $driver->driver_vehicle_document_verification_status = $verification_status;

            // Update document status if `document_id` is provided in the request
            if ($request->has('document_id')) {
                foreach ($request->document_id as $doc_id) {
                    $driver_document = DriverDocumentDetails::where('id', $doc_id)->where('driver_id', $id)->first();
                    if ($driver_document) {
                        $driver_document->document_verification_status = $verification_status;
                        $driver_document->document_verified_by = Auth::user()->id;
                        $driver_document->document_verified_at = now();
                        $driver_document->document_verification_reason = $request->personal_verification_reason ?? '';
                        $driver_document->save();
                    }
                }
            }

            // Save all updates
            $driver->save();
            $bank_details->save();


            $statusMessage = "";
            $title = "Personal Details Verification";

            if ($request->personal_verification_status == "approve") {
                $statusMessage = "Personal details approved successfully.";
            } elseif ($request->personal_verification_status == "reject") {
                $statusMessage = "Your personal information has been rejected by the admin. Please review and submit the information again.";
            }

            if (!empty($driver->driver_firebase_token)) {
                $DeviceIdsArr[] = $driver->driver_firebase_token;
                $dataArr = array();
                $dataArr['device_id'] = $DeviceIdsArr;
                $dataArr['message'] = $statusMessage;
                $dataArr['title'] = $title;
                $notification['device_id'] = $DeviceIdsArr;
                $notification['message'] = $statusMessage;
                $notification['title'] = $title;
                $noti = new Notificationlibv_3;
                $result = $noti->sendNotification($dataArr, $notification);
                Log::info("Personal deatils Verification notification result", ['result' => $result]);
            }

            LogHelper::logSuccess('The driver personal details verified successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            if ($request->personal_verification_status == "approve") {
                // Return success response
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner personal details verified successfully.'
                ]);
            }
            if ($request->personal_verification_status == "reject") {
                // Return success response
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner personal details rejected successfully.'
                ]);
            }
            // return redirect('driver')->with('success', 'The driver personal details verified successfully.');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while verifying the driver personal details', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            return redirect()->back()->with('error', 'An error occurred while verifying the driver personal details.');
        }
    }

    /**
     * Update the specified resource in storage.
     * @author shreyasm@neosao
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        try {
            $validationRules =  [
                'driver_first_name' => [
                    'required',
                    'regex:/^[A-Za-z\s]+$/',
                    'min:2',
                    'max:150',
                ],
                'driver_last_name' => [
                    'required',
                    'regex:/^[A-Za-z\s]+$/',
                    'min:2',
                    'max:150',
                ],
                'driver_email' => [
                    'nullable',
                    'email',
                    'min:2',
                    'max:150',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    Rule::unique('drivers', 'driver_email')->where("is_delete", 0)->ignore($id),
                ],
                'driver_phone' => [
                    'required',
                    'digits:10',
                    'integer',
                    Rule::unique('drivers', 'driver_phone')->where("is_delete", 0)->ignore($id),
                ],
                'driver_gender' => [
                    'required',
                    'in:male,female,others'
                ],
                'driver_serviceable_location' => [
                    'required',
                ],
                'driver_bank_name' => [
                    'required',
                    'string',
                    'regex:/^[A-Za-z\s]+$/',
                ],
                'driver_bank_account_number' => [
                    'required',
                    'numeric',
                    'regex:/^\d{9,18}$/',
                ],
                'driver_bank_ifsc_code' => [
                    'required',
                    'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                ],
                'driver_bank_branch_name' => [
                    'required',
                    'max:255'
                ],
            ];

            $messages = [
                'driver_first_name.required' => 'The first name field is required.',
                'driver_first_name.min' => 'The first name must be at least 2 characters long.',
                'driver_first_name.max' => 'The first name cannot exceed 150 characters.',
                'driver_first_name.regex' => 'The first name can contain only letters and spaces.',

                'driver_last_name.required' => 'The last name field is required.',
                'driver_last_name.min' => 'The last name must be at least 2 characters long.',
                'driver_last_name.max' => 'The last name cannot exceed 150 characters.',
                'driver_last_name.regex' => 'The last name can contain only letters and spaces.',

                'driver_email.email' => 'Please enter a valid email address.',
                'driver_email.min' => 'The email must be at least 2 characters long.',
                'driver_email.max' => 'The email cannot exceed 150 characters.',
                'driver_email.regex' => 'Please enter a valid email format.',

                'driver_bank_name.required' => 'The bank name field is required.',
                'driver_bank_name.regex' => 'The bank name can contain only letters and spaces.',

                'driver_bank_account_number.required' => 'The bank account number field is required.',
                'driver_bank_account_number.numeric' => 'The bank account number must be numeric.',
                'driver_bank_account_number.regex' => 'The bank account number must be between 9 to 18 digits.',

                'driver_bank_ifsc_code.required' => 'The IFSC code field is required.',
                'driver_bank_ifsc_code.regex' => 'The IFSC code must be in the valid format.',

                'driver_bank_branch_name.required' => 'The branch name field is required.',
                'driver_bank_branch_name.max' => 'The branch name cannot exceed 255 characters.',

                'driver_phone.required' => 'The phone number field is required.',
                'driver_phone.digits' => 'The phone number must be exactly 10 digits.',
                'driver_phone.integer' => 'The phone number must be a valid integer.',

                'driver_serviceable_location.required' => 'The servicable location field is required.',

                'driver_gender.required' => 'The gender field is required.',
                'driver_gender.in' => 'The selected gender must be male, female, or others.',
            ];



            foreach ($request->document_id as $i => $doc_id) {
                $document_details = DriverDocumentDetails::where('id', $doc_id)
                    ->where('driver_id', $id)
                    ->first();

                // Define regex patterns for specific document types
                $regexPatterns = [
                    'aadhar_card' => 'regex:/^\d{12}$/', // 12-digit number for Aadhar Card
                    'pan_card' => 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', // PAN format (e.g., ABCDE1234F)
                    'driving_license' => 'regex:/^[A-Z]{2}\d{2} \d{4}\d{7}$/', // Driving License (e.g., MH12 3456 7890123)
                ];

                if (!in_array($document_details->document_type, ['bank_passbook_or_cancel_cheque'])) {
                    if (isset($regexPatterns[$document_details->document_type])) {
                        $validationRules["document_number.$i"] = 'required|' . $regexPatterns[$document_details->document_type];
                        $messages["document_number.$i.required"] = 'The document number field is required.';
                        $messages["document_number.$i.regex"] = 'The document number field format is invalid.';
                    } else {
                        $validationRules["document_number.$i"] = 'required'; // No specific regex for this document type
                        $messages["document_number.$i.required"] = 'The document number field is required.';
                    }
                }

                // Validate `document_1` for other document types
                if ($document_details && $document_details->document_1) {
                    $validationRules["document_1_upload.$i"] = 'sometimes|file';
                    $messages["document_1_upload.$i.sometimes"] = 'Document upload is optional for this document.';
                } else {
                    $validationRules["document_1_upload.$i"] = 'required|file';
                    $messages["document_1_upload.$i.required"] = 'The document upload field is required.';
                    $messages["document_1_upload.$i.file"] = 'The uploaded document must be a valid file.';
                }

                // Require `document_2` for "Aadhar Card" if needed
                if ($document_details && $document_details->document_type === 'aadhar_card') {
                    if ($document_details->document_2) {
                        $validationRules["document_2_upload.$i"] = 'sometimes|file';
                        $messages["document_2_upload.$i.sometimes"] = 'Document upload is optional for this document.';
                    } else {
                        $validationRules["document_2_upload.$i"] = 'required|file';
                        $messages["document_2_upload.$i.required"] = 'The document upload field is required.';
                        $messages["document_2_upload.$i.file"] = 'The uploaded document must be a valid file.';
                    }
                }
            }

            foreach ($request->document_id as $i => $doc_id) {
                $vehicle_document_details = DriverVehicleDocumentDetails::where('id', $doc_id)
                    ->where('driver_id', $id)
                    ->first();

                // If no document details are found, skip this iteration
                if (!$vehicle_document_details) {
                    continue;
                }

                // Define regex patterns for specific vehicle document types
                $regexPatternsvehicle = [
                    'rc_book' => 'regex:/^[A-Z]{2} \d{2} [A-Z]{2} \d{4}$/',  // RC Book format
                    'insurance' => 'regex:/^[A-Z0-9]{5,}$/',  // Insurance number format
                    // Add other document type patterns as needed
                ];

                // Apply required and regex validation for other document types
                if (isset($regexPatternsvehicle[$vehicle_document_details->document_type])) {
                    $validationRules["vehicle_document_number.$i"] = 'required|' . $regexPatternsvehicle[$vehicle_document_details->document_type];
                    $messages["vehicle_document_number.$i.required"] = 'The vehicle document number field is required.';
                    $messages["vehicle_document_number.$i.regex"] = 'The vehicle document number format is invalid.';
                } else {
                    $validationRules["vehicle_document_number.$i"] = 'required';
                    $messages["vehicle_document_number.$i.required"] = 'The vehicle document number field is required.';
                }

                // Validate `document_1` for other document types
                if ($vehicle_document_details && $vehicle_document_details->document_file_path) {
                    $validationRules["vehicle_document_upload.$i"] = 'sometimes|file';
                    $messages["vehicle_document_upload.$i.sometimes"] = 'Document upload is optional for this document.';
                } else {
                    $validationRules["vehicle_document_upload.$i"] = 'required|file';
                    $messages["vehicle_document_upload.$i.required"] = 'The document upload field is required.';
                    $messages["vehicle_document_upload.$i.file"] = 'The uploaded document must be a valid file.';
                }
            }

            $validator = Validator::make($request->all(), $validationRules, $messages);

            // Sanitize errors by removing array indices
            $sanitizedErrors = [];
            foreach ($validator->errors()->getMessages() as $key => $messages) {
                // Remove the array index from the key (e.g., `document_number.0` -> `document_number`)
                $baseKey = preg_replace('/\.\d+$/', '', $key);

                // If this base key hasn't been set in sanitizedErrors, add the first error message
                if (!isset($sanitizedErrors[$baseKey])) {
                    $sanitizedErrors[$baseKey] = $messages[0];
                }
            }

            foreach ($sanitizedErrors as $key => $errorMessage) {
                $validator->errors()->add($key, $errorMessage);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200);
            }


            // Update and save the driver
            $driver = DriverModel::find($id);
            $driver->driver_first_name = $request->driver_first_name;
            $driver->driver_last_name = $request->driver_last_name;
            $driver->driver_email = $request->driver_email;
            $driver->driver_phone = $request->driver_phone;
            $driver->driver_gender = $request->driver_gender;
            $driver->driver_serviceable_location = $request->driver_serviceable_location;
            $driver->is_active = $request->driver_is_active ? 1 : 0;
            if ($request->hasFile("driver_photo")) {
                $file = $request->file('driver_photo');
                $extension = $file->getClientOriginalExtension();
                $fileName = 'personal-document-' . time() . '-' . $file->getClientOriginalName();
                // Store the file in 'public/personal-document' directory
                $path = Storage::disk('public')->putFileAs('personal-document', $file, $fileName);

                if ($driver) {
                    $driver->driver_photo = $path;
                }
            }
            $driver->save();

            $bank_details = DriverBankDetails::where('driver_id', $id)->first();
            $bank_details->driver_bank_name = $request->driver_bank_name;
            $bank_details->driver_bank_account_number = $request->driver_bank_account_number;
            $bank_details->driver_bank_ifsc_code = $request->driver_bank_ifsc_code;
            $bank_details->driver_bank_branch_name = $request->driver_bank_branch_name;
            $bank_details->save();

            // Process each personal document uploaded file
            for ($i = 0; $i < count($request->document_id); $i++) {

                // Retrieve the corresponding document ID and details
                $doc_id = $request->document_id[$i];
                $document_details = DriverDocumentDetails::where('id', $doc_id)
                    ->where('driver_id', $id)
                    ->first();

                $document_details->document_number = $request->document_number[$i];
                // Check if the specific file at index $i is available and valid
                if ($request->hasFile("document_1_upload.$i")) {
                    $file = $request->file('document_1_upload')[$i];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'personal-document-' . time() . '-' . $file->getClientOriginalName();

                    // Store the file in 'public/personal-document' directory
                    $path = Storage::disk('public')->putFileAs('personal-document', $file, $fileName);


                    if ($document_details) {
                        $document_details->document_1_file_type = $extension;
                        $document_details->document_1 = $path;
                    }
                }
                // Check if the specific file at index $i is available and valid
                if ($request->hasFile("document_2_upload.$i")) {
                    $file = $request->file('document_2_upload')[$i];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'personal-document-' . time() . '-' . $file->getClientOriginalName();

                    // Store the file in 'public/personal-document' directory
                    $path = Storage::disk('public')->putFileAs('personal-document', $file, $fileName);

                    if ($document_details) {
                        $document_details->document_2_file_type = $extension;
                        $document_details->document_2 = $path;
                    }
                }
                $document_details->save(); // Ensure you save the updated model
            }


            // Process each vehicle document uploaded file
            for ($i = 0; $i < count($request->vehicle_document_id); $i++) {

                // Retrieve the corresponding document ID and details
                $doc_id = $request->vehicle_document_id[$i];

                $vehicle_document_details = DriverVehicleDocumentDetails::where('id', $doc_id)
                    ->where('driver_id', $id)
                    ->first();

                $vehicle_document_details->document_number = $request->vehicle_document_number[$i];

                // Check if the specific file at index $i is available and valid
                if ($request->hasFile("vehicle_document_upload.$i")) {
                    $file = $request->file('vehicle_document_upload')[$i];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'vehicle-document-' . time() . '-' . $file->getClientOriginalName();

                    // Store the file in 'public/vehicle-document' directory
                    $path = Storage::disk('public')->putFileAs('vehicle-document', $file, $fileName);


                    if ($vehicle_document_details) {
                        $vehicle_document_details->document_file_type = $extension;
                        $vehicle_document_details->document_file_path = $path;
                    }
                }
                $vehicle_document_details->save();  // Ensure you save the updated model
            }


            LogHelper::logSuccess('The Partner updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Partner updated successfully.'
            ]);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while updating the Partner', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the Partner.'
            ], 500);
        }
    }

    /**
     * @author shreyasm@neosao
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Find the driver by ID
            $driver = DriverModel::find($id);



            // Check if the driver exists
            if (!$driver) {
                LogHelper::logError('An error occurred while deleting the Partner', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json([
                    'success' => false,
                    'error' => 'Partner not found.'
                ]);
            }

            // Soft delete the driver by setting is_delete flag
            $driver->is_active = 0;
            $driver->is_delete = 1;
            $driver->save();

            // Return success response
            return response()->json(['success' => true,'driver'=> $driver]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the Partner', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the Partner.'
            ]);
        }
    }

    /**
     * @author shreyasm@neosao
     * Remove the specified resource from storage.
     */
    public function blockOrUnblock(string $id, string $type)
    {
        try {
            // Find the driver by ID
            $driver = DriverModel::find($id);

            // Check if the driver exists
            if (!$driver) {
                LogHelper::logError('An error occurred while deleting the Partner', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json([
                    'success' => false,
                    'error' => 'Partner not found.'
                ]);
            }

            if ($type && $type == "block") {
                $driver->is_active=0;
                $driver->is_driver_block = 1;
                LogHelper::logSuccess('Partner block successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            } else {
                $driver->is_active=1;
                $driver->is_driver_block = 0;
                LogHelper::logSuccess('Partner unblock successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            }
            $driver->save();
            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the Partner', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the Partner.'
            ]);
        }
    }

    /**
     * @author seemashelar@neosao
     * vehicle information update.
     */
    public function vehicle_information_update(Request $request, string $id)
    {

        try {
            $validator = Validator::make($request->all(), [
                'driver_vehicle_number' => [
                    'required',
                    'regex:/^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/',
                    'min:2',
                    'max:15',
                ]
            ], [
                'driver_vehicle_number.required' => 'Please enter the vehicle number.',
                'driver_vehicle_number.min' => 'Vehicle number must be at least 2 characters long.',
                'driver_vehicle_number.max' => 'Vehicle number cannot exceed 150 characters.',
                'driver_vehicle_number.regex' => 'Vehicle number can contain only letters, numbers, and spaces.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            // dd($request->all());
            // Create and save the driver vehicle details
            $driver_vehicle_details = DriverVehicleDetails::find($request->vehicle_id);
            $driver_vehicle_details->vehicle_number = $request->driver_vehicle_number;
            $driver_vehicle_details->vehicle_id = $request->driver_vehicle;
            $driver_vehicle_details->is_active = 1;
            $driver_vehicle_details->is_delete = 0;

            // Handle the vehicle photo upload
            if ($request->hasFile('vehicle_photo')) {
                $file = $request->file('vehicle_photo');
                $imageName = 'vehicle-photo-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('vehicle-photo', $file, $imageName);
                $driver_vehicle_details->vehicle_photo = $path; // Save the image name in the database
            }

            $driver_vehicle_details->update();



            LogHelper::logSuccess('The Partner vehicle details updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Partner vehicle details updated successfully.'
            ]);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the Partner vehicle details', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the Partner vehicle details.');
        }
    }

    /**
     * @author seemashelar@neosao
     * vehicle information verify.
     */
    public function vehicle_information_verify(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_verification_status' => [
                    'required',
                    'in:approve,reject',
                ],
                'vehicle_verification_reason' => [
                    'required_if:vehicle_verification_status,reject',
                ]
            ], [
                'vehicle_verification_status.required' => 'Vehicle status is required.',
                'vehicle_verification_status.in' => 'Vehicle status must be either "approve" or "reject".',
                'vehicle_verification_reason.required_if' => 'Vehicle reason is required if the status is "reject".',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $verification_status = $request->vehicle_verification_status == 'approve' ? 1 : 2;

            // Retrieve the driver record
            $driver = DriverModel::find($id);
            if (!$driver) {
                return redirect()->back()->with('error', 'driver vehicle not found.');
            }

            $driver->driver_vehicle_verification_status = $verification_status;
            $driver->driver_vehicle_document_verification_status = $verification_status;
            $driver->save();

            $driver_vehicle_details = DriverVehicleDetails::find($request->vehicle_id);
            $driver_vehicle_details->vehicle_verification_reason = $request->vehicle_verification_reason;
            $driver_vehicle_details->save();


            $statusMessage = "";
            $title = "Vehicle Verification";

            if ($verification_status == 1) {
                $statusMessage = "Vehicle information approved successfully.";
            } elseif ($verification_status == 2) {
                $statusMessage = "Your vehicle information has been rejected by the admin. Please review and submit the information again.";
            }

            if (!empty($driver->driver_firebase_token)) {
                $DeviceIdsArr[] = $driver->driver_firebase_token;
                $dataArr = array();
                $dataArr['device_id'] = $DeviceIdsArr;
                $dataArr['message'] = $statusMessage;
                $dataArr['title'] = $title;
                $notification['device_id'] = $DeviceIdsArr;
                $notification['message'] = $statusMessage;
                $notification['title'] = $title;
                $noti = new Notificationlibv_3;
                $result = $noti->sendNotification($dataArr, $notification);
                Log::info("Vehicle details verification notification result", ['result' => $result]);
            }

            LogHelper::logSuccess('Partner vehicle details verified successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            if ($verification_status == 1) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner vehicle details verified successfully.'
                ]);
            }
            if ($verification_status == 2) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner vehicle details rejected successfully.'
                ]);
            }

            //return redirect('driver')->with('success', 'The driver vehicle details verified successfully.');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while verifying the Partner vehicle details', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            return redirect()->back()->with('error', 'An error occurred while verifying the Partner vehicle details.');
        }
    }

    /**
     * @author seemashelar@neosao
     * @author shreyasm@neosao # correction in function
     * previous function has issue cause of redirection instead of response()->json()
     * delete vehicle photo.
     */
    public function delete_vehicle_photo(Request $r, string $id)
    {
        try {
            $driver_vehicle_details = DriverVehicleDetails::find($id);

            if (!$driver_vehicle_details) {
                // Log the error
                LogHelper::logError('An error occurred while deleting the vehicle photo', 'Invalid vehicle photo', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

                // Return JSON error response
                return response()->json(['error' => 'Invalid vehicle photo.'], 404);
            }

            // Delete the photo from storage and update the database
            Storage::disk('public')->delete($driver_vehicle_details->vehicle_photo);
            $driver_vehicle_details->update(['vehicle_photo' => null]);

            // Success log
            LogHelper::logSuccess('The vehicle photo deleted successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            // Return JSON success response
            return response()->json(['success' => 'Vehicle photo deleted successfully.'], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while deleting the vehicle photo', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return JSON error response
            return response()->json(['error' => 'An error occurred while deleting the vehicle photo.'], 500);
        }
    }

    /**
     * @author shreyas@neosao
     * delete driver photo.
     */
    public function delete_driver_photo(Request $r, string $id)
    {
        try {
            $driver = DriverModel::find($id);

            if (!$driver) {
                // Log the error
                LogHelper::logError('An error occurred while deleting the driver photo', 'Invalid driver photo', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return JSON error response
                return response()->json(['error' => 'The driver photo is invalid.'], 404);
            }

            Storage::disk('public')->delete($driver->driver_photo);
            $driver->update(['driver_photo' => null]);

            // Log success
            LogHelper::logSuccess('The driver photo deleted successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            // Return JSON success response
            return response()->json(['success' => 'The driver photo deleted successfully']);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while deleting the driver photo', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return JSON error response
            return response()->json(['error' => 'An error occurred while deleting the driver photo.'], 500);
        }
    }

    /**
     * @author seemashelar@neosao
     * delete vehicle document.
     */
    public function vehicle_document_delete(Request $r, string $id)
    {
        try {
            $driver_vehicle_document_details = DriverVehicleDocumentDetails::find($id);

            if (!$driver_vehicle_document_details) {
                // Log the error
                LogHelper::logError('An error occurred while deleting the vehicle document', 'Invalid vehicle document', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return JSON error response
                return response()->json(['error' => 'The vehicle document is invalid.'], 404);
            }

            Storage::disk('public')->delete($driver_vehicle_document_details->document_file_path);
            $driver_vehicle_document_details->update([
                'document_file_type' => null,
                'document_file_path' => null,
                'document_verification_status' => null
            ]);

            // Log success
            LogHelper::logSuccess('The vehicle document deleted successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
            // Return JSON success response
            return response()->json(['success' => 'Vehicle document deleted successfully']);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while deleting the vehicle document', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return JSON error response
            return response()->json(['error' => 'An error occurred while deleting the vehicle document.'], 500);
        }
    }

    /**
     * @author seemashelar@neosao
     * delete personal document.
     */

    public function personal_document_delete(Request $r, string $id)
    {
        try {
            $driver_personal_document_deatils = DriverDocumentDetails::find($r->id);

            if (!$driver_personal_document_deatils) {
                // Log the error
                LogHelper::logError('An error occurred while deleting the personal document', 'Invalid personal document',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->id);
                // Return error response to the user
                return response()->json(['error' => 'Invalid personal document.'], 404);
            }

            if ($r->document_count == 1) {
                Storage::disk('public')->delete($driver_personal_document_deatils->document_1);
                $driver_personal_document_deatils->update([
                    'document_1_file_type' => null,
                    'document_1' => null,
                    'document_verification_status' => null
                ]);
            } elseif ($r->document_count == 2) {
                Storage::disk('public')->delete($driver_personal_document_deatils->document_2);
                $driver_personal_document_deatils->update([
                    'document_2_file_type' => null,
                    'document_2' => null,
                    'document_verification_status' => null
                ]);
            }

            // Log success
            LogHelper::logSuccess('The personal document deleted successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);

            // Return success response
            return response()->json(['success' => 'Personal document deleted successfully.'], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while deleting the personal document', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return error response to the user
            return response()->json(['error' => 'An error occurred while deleting the personal document.'], 500);
        }
    }

    /**
     * Fetching the training video list.
     * @author seemashelar@neosao
     */
    public function training_video_verify(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'training_video_verification_status' => [
                    'required',
                    'in:approve,reject',
                ],
                'training_video_verification_reason' => [
                    'required_if:training_video_verification_status,reject',
                ]
            ], [
                'training_video_verification_status.required' => 'Training Video verification status is required.',
                'training_video_verification_status.in' => 'Training Video verification status must be either "approve" or "reject".',
                'training_video_verification_reason.required_if' => 'Training Video verification reason is required if the status is "reject".',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $training_video_verification_status = $request->training_video_verification_status == 'approve' ? 1 : 2;

            // Retrieve the driver record
            $driver = DriverModel::find($id);
            if (!$driver) {
                return redirect()->back()->with('error', 'driver not found.');
            }

            $driver->driver_training_video_verification_status = $training_video_verification_status;
            $driver->save();

            $driver_training_video = DriverTrainingVideo::find($request->id);
            $driver_training_video->training_video_verification_reason = $request->training_video_verification_reason;
            $driver_training_video->save();

            $statusMessage = "";
            $title = "Training Video Verification";

            if ($request->driver_training_video_verification_status == "approve") {
                $statusMessage = "Training video  verification approved successfully.";
            } elseif ($request->driver_training_video_verification_status == "reject") {
                $statusMessage = "Your training video verification has been rejected by the admin. Please review and submit the information again.";
            }

            if (!empty($driver->driver_firebase_token)) {
                $DeviceIdsArr[] = $driver->driver_firebase_token;
                $dataArr = array();
                $dataArr['device_id'] = $DeviceIdsArr;
                $dataArr['message'] = $statusMessage;
                $dataArr['title'] = $title;
                $notification['device_id'] = $DeviceIdsArr;
                $notification['message'] = $statusMessage;
                $notification['title'] = $title;
                $noti = new Notificationlibv_3;
                $result = $noti->sendNotification($dataArr, $notification);
                Log::info("Training video verification notification result", ['result' => $result]);
            }
            LogHelper::logSuccess('Partner training video details verified successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $request->id);

            if ($request->training_video_verification_status == "approve") {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner training video details verified successfully.'
                ]);
            }
            if ($request->training_video_verification_status == "reject") {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner training video details rejected successfully.'
                ]);
            }

            //return redirect('driver')->with('success', 'The driver vehicle details verified successfully.');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while verifying the driver training video', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $request->id);
            return redirect()->back()->with('error', 'An error occurred while verifying the driver training video.');
        }
    }

    /*
	 * unique id list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */
    public function get_trips(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');

            $result = Trip::where('trip_unique_id', 'like', '%' . $search . '%')
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->trip_unique_id];
            }

            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while fetching the unique list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([]);
        }
    }


    /*
	 * driver list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */

    public function get_drivers(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');

            $result = Driver::where(function ($query) use ($search) {
                $query->where('driver_first_name', 'like', '%' . $search . '%')
                    ->orWhere('driver_last_name', 'like', '%' . $search . '%')
                    ->orWhere('driver_phone', 'like', '%' . $search . '%');
            })
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name . ' ( ' . $item->driver_phone . ' ) ');
                $html[] = ['id' => $item->id, 'text' => $fullName];
            }

            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while fetching the driver list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([]);
        }
    }


    /*
	 * get customer list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

    public function get_customers(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = Customer::where(function ($query) use ($search) {
                $query->where('customer_first_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_last_name', 'like', '%' . $search . '%');
            })
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $fullName = trim($item->customer_first_name . ' ' . $item->customer_last_name);
                    $html[] = ['id' => $item->id, 'text' => $fullName];
                }
            }

            return response()->json($html);
        } catch (\Exception $ex) {
            //error log
            LogHelper::logError('An error occurred while the customers list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
        }
    }


    /*
	 * rating list index page
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */

    public function rating_index(Request $r)
    {
        try {
            $driverId = $r->id;
            return view('verified-driver.rating', compact('driverId'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while the driver rating page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the driver rating list.');
        }
    }


    /*
	 * rating list
	 * seemashelar@neosao
	 * dt: 4-jan-2025
	 */

    public function rating_list(Request $r)
    {
        try {
            $limit = $r->length;
            $offset = $r->start;
            $search = $r->input('search.value') ?? "";

            $customer = $r->customer ?? "";
            $trip = $r->trip ?? "";
            $driver = $r->driver ?? "";

            $filteredData = Driver::filterRating($search, $limit, $offset, $customer, $trip, $driver);

            $total = $filteredData['totalRecords'];

            $records =  $filteredData['result'];

            $data = [];
            $srno = $offset + 1;
            if ($records->count() > 0) {
                for ($i = 0; $i < $records->count(); $i++) {
                    $row = $records[$i];
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y');

                    $data[] = [
                        $row->customer_first_name . " " . $row->customer_last_name,
                        $row->driver_first_name . " " . $row->driver_last_name,
                        $row->trip_unique_id,
                        $row->rating_value,
                        $row->rating_description,
                        $formattedDate
                    ];
                    $srno++;
                }
            }
            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data
            ], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while the verified-driver rating list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([
                "message" => "An error occurred while fetching the verified-driver rating list",
            ], 500);
        }
    }


    /**
     * the admin verify.
     * @author seemashelar@neosao
     */
    public function admin_verify(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'admin_verification_status' => [
                    'required',
                    'in:approve,reject',
                ],
                'admin_verification_reason' => [
                    'required_if:admin_verification_status,reject',
                ]
            ], [
                'admin_verification_status.required' => 'Admin verification status is required.',
                'admin_verification_status.in' => 'Admin verification status must be either "approve" or "reject".',
                'admin_verification_reason.required_if' => 'Admin verification reason is required.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 200);
            }


            $admin_verification_status = $request->admin_verification_status == 'approve' ? 1 : 2;

            // Retrieve the Driver record
            $driver = Driver::find($id);
            if (!$driver) {
                return redirect()->back()->with('error', 'Partner not found.');
            }
            $driver->admin_verification_status = $admin_verification_status;
            $driver->admin_verification_reason =  $request->admin_verification_reason;
            $driver->save();

            $statusMessage = "";
            $title = "Admin Verification";

            if ($request->admin_verification_status == "approve") {
                $statusMessage = "Partner approved successfully.";
            } elseif ($request->admin_verification_status == "reject") {
                $statusMessage = "Your verification has been rejected by the admin. Please review and submit the information again.";
            }

            if (!empty($driver->driver_firebase_token)) {
                $DeviceIdsArr[] = $driver->driver_firebase_token;
                $dataArr = array();
                $dataArr['device_id'] = $DeviceIdsArr;
                $dataArr['message'] = $statusMessage;
                $dataArr['title'] = $title;
                $notification['device_id'] = $DeviceIdsArr;
                $notification['message'] = $statusMessage;
                $notification['title'] = $title;
                $noti = new Notificationlibv_3;
                $result = $noti->sendNotification($dataArr, $notification);
                Log::info("Admin verification notification result", ['result' => $result]);
            }

            LogHelper::logSuccess('The admin verified successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $request->id);

            if ($request->admin_verification_status == "approve") {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner verified successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'Partner rejected successfully.'
                ]);
            }
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while verifying the Partner by admin', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $request->id);
            return redirect()->back()->with('error', 'An error occurred while verifying the Partner by admin.');
        }
    }
}
