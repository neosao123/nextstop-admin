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
use App\Models\DriverRejectionReason;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class DriverRejectionReasonController extends Controller
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
            return view('driver-rejection-reason.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the driver rejection reason index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the driver rejection reason list.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Display a listing of the resource.
     */

    public function list(Request $request)
    {
        try {
            $search = $request->input('search.value') ?? "";
            $limit = $request->length;
            $offset = $request->start;
            $srno = $offset + 1;
            $data = array();
            $filteredData =  DriverRejectionReason::filterDriverRejectionReason($search, $limit, $offset);
            $total = $filteredData['totalRecords'];
            $result =  $filteredData['result'];
			
			$canEdit = Auth::guard('admin')->user()->can('Driver-Reason.Edit');
			$canView = Auth::guard('admin')->user()->can('Driver-Reason.Delete');
			$canDelete = Auth::guard('admin')->user()->can('Driver-Reason.View');
			$showActions = $canEdit || $canView || $canDelete;
			
            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y h:i:s A');
                   $dataRow = [];
                    if ($showActions) {
                        $action = '
                    <span class="text-start">
                        <div class="dropdown font-sans-serif position-static">
                            <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
                                aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
                                <div class="bg-white py-2">';
                        if (Auth::guard('admin')->user()->can('Driver-Reason.Edit')) {
                            $action .= '<a class="dropdown-item btn-edit" href="' . url('driver-rejection-reason/' . $row['id'] . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit')  . ' </a>';
                        }
                        if (Auth::guard('admin')->user()->can('Driver-Reason.View')) {
                            $action .= '<a class="dropdown-item btn-edit" href="' . url('driver-rejection-reason/' . $row['id']) . '"> <i class="far fa-folder-open"></i> ' . __('index.view')  . '</a>';
                        }
                        if (Auth::guard('admin')->user()->can('Driver-Reason.Delete')) {
                            $action .= '<a class="dropdown-item btn-delete" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete')  . '</a>';
                        }
                        $action .= '</div></div></div></span>';
						$dataRow[] = $action;
                    }

                    if ($row->is_active == 1) {
                        $status =   '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>';
                    } else {
                        $status =   '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
                    }
                    $dataRow[] = $row->reason;
					
					$dataRow[] = $formattedDate;
					$dataRow[] =$status;
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
            LogHelper::logError('An error occurred while the driver rejection reason list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the driver rejection reason list",
            ], 500);
        }
    }


    /**
     * @author seemashelar@neosao
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('driver-rejection-reason.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while create the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the driver rejection reason.');
        }
    }


    /**
     * @author seemashelar@neosao
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => [
                    'required',
                    Rule::unique('driver_rejection_reasons', 'reason')->where(function ($query) {
                        return $query->where('is_delete', 0);
                    }),
                ],
                'is_active' => 'nullable'
            ], [
                'reason.required' => 'The reason field is required.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the driver rejection reason
            $driver_rejection_reason = new DriverRejectionReason;
            $driver_rejection_reason->reason = $request->reason;
            $driver_rejection_reason->is_active = $request->is_active ? 1 : 0;
            $driver_rejection_reason->save();
            //success log
            LogHelper::logSuccess('The driver rejection reason added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__,  $driver_rejection_reason->id);
            // Return success response
            return redirect('driver-rejection-reason')->with('success', 'The driver rejection reason added successfully.');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the driver reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the driver rejection reason.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $driver_reason = DriverRejectionReason::where('id', $id)->where('is_delete', 0)->first();
            if (!$driver_reason) {

                // Log the error
                LogHelper::logError('An error occurred while view the driver rejection reason', 'The invalid driver rejection reason',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver rejection reason.');
            }
            return view('driver-rejection-reason.show', compact('driver_reason'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the driver rejection reason.');
        }
    }


    /**
     * @author seemashelar@neosao
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $driver_reason = DriverRejectionReason::where('id', $id)->where('is_delete', 0)->first();
            if (!$driver_reason) {
                // Log the error
                LogHelper::logError('An error occurred while edit the driver rejection reason', 'The invalid driver rejection reason',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver rejection reason.');
            }
            return view('driver-rejection-reason.edit', compact('driver_reason'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the driver rejection reason.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => [
                    'required',
                    Rule::unique('driver_rejection_reasons', 'reason')
                        ->ignore($id)
                        ->where(function ($query) {
                            return $query->where('is_delete', 0);
                        }),
                ],
                'is_active' => 'nullable'
            ], [
                'reason.required' => 'The reason field is required.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the driver rejection reason
            $driver_reason = DriverRejectionReason::find($id);
            if (!$driver_reason) {
                // Log the error
                LogHelper::logError('An error occurred while update the driver rejection reason', 'The invalid driver rejection reason',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver rejection reason.');
            }
            $driver_reason->reason = $request->reason;
            $driver_reason->is_active = $request->is_active ? 1 : 0;
            $driver_reason->update();

            LogHelper::logSuccess('The driver rejection reason updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $driver_reason->id);
            // Return success response
            return redirect('driver-rejection-reason')->with('success', 'The driver rejection reason updated successfully.');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the driver rejection reason.');
        }
    }


    /**
     * Remove the specified resource from storage.
     * @author seemashelar@neosao
     */
    public function destroy(string $id)
    {
        try {
            // Find the reason by ID
            $driver_reason = DriverRejectionReason::find($id);

            // Check if the driver reason exists
            if (! $driver_reason) {
                LogHelper::logError('An error occurred while deleting the driver rejection reason', 'driver rejection reason not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'driver rejection reason not found.']);
            }

            // Soft delete the driver rejection reason by setting is_delete flag
            $driver_reason->is_delete = 1;
            $driver_reason->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the driver rejection reason.']);
        }
    }


    /**
     * import driver rejection reason.
     * @author seemashelar@neosao
     */

    public function import_excel(Request $r)
    {
        try {
            return view("driver-rejection-reason.import");
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while importing the driver rejection reason', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while importing the driver rejection reason .']);
        }
    }

    /**
     * validate driver rejection reason.
     * @author seemashelar@neosao
     */

    public function validate_excel(Request $r)
    {
        try {
            $rowArray = $r->convertedIntoArray;
            $statusArr = ['active', 'inactive'];
            $DriverReasonStatusStr = '';
            $duplicateReasons = '';
            $reasonCount = [];
            $returnRowArray = [];
            $msg = '';

            if (!empty($rowArray)) {
                for ($i = 0; $i < count($rowArray); $i++) {
                    $reason = trim($rowArray[$i][1] ?? '');
                    $status = trim($rowArray[$i][2] ?? '');

                    // Reason validation
                    if (empty($reason)) {
                        $duplicateReasons .= "Row " . ($i + 1) . " - driver rejection reason is required.<br>";
                        $returnRowArray[] = $i;
                    } else {
                        // Check if reason already exists in database
                        $reasonExists = DriverRejectionReason::where('reason', $reason)
                            ->where("is_delete", 0)
                            ->exists();

                        if ($reasonExists) {
                            $duplicateReasons .= "Row " . ($i + 1) . " - driver rejection reason '$reason' already exists in the database.<br>";
                            $returnRowArray[] = $i;
                        } else {
                            // Detect duplicates in the current upload
                            if (!isset($reasonCount[$reason])) {
                                $reasonCount[$reason] = [$i + 1]; // Store row numbers
                            } else {
                                $reasonCount[$reason][] = $i + 1; // Add row number
                            }
                        }
                    }

                    // Status validation
                    if (empty($status)) {
                        $DriverReasonStatusStr .= "Row " . ($i + 1) . " - Status is required.<br>";
                        $returnRowArray[] = $i;
                    } elseif (!in_array($status, $statusArr)) {
                        $DriverReasonStatusStr .= "Row " . ($i + 1) . " - Status is not valid. Please select from active/inactive instead of '$status'.<br>";
                        $returnRowArray[] = $i;
                    }
                }
            }

            // Prepare duplicate reasons error message
            foreach ($reasonCount as $reason => $rows) {
                if (count($rows) > 1) {
                    $duplicateReasons .= "driver rejection reason '$reason' is duplicated in rows: " . implode(', ', $rows) . ".<br>";
                }
            }

            // Prepare the response message with colors
            $msg = '';
            if (!empty($duplicateReasons)) {
                $msg .= '<li><p class="mb-1"><span class="text-warning">' . rtrim($duplicateReasons, '<br>') . '</span></p></li>';
            }

            if (!empty($DriverReasonStatusStr)) {
                $msg .= '<li><p class="mb-1"><span class="text-warning">' . rtrim($DriverReasonStatusStr, '<br>') . '</span></p></li>';
            }

            if (!empty($msg)) {
                $msg = '<ul>' . $msg . '</ul>';
            }

            $arr = [
                "msg" => $msg,
                "rowArr" => json_encode($returnRowArray)
            ];

            return response()->json($arr);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'An error occurred while validating the driver rejection reason',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while validating the driver rejection reason.'
            ]);
        }
    }



    /**
     * upload  driver rejection reason excel.
     * @author seemashelar@neosao
     */

    public function upload_excel(Request $r)
    {
        $path = 'excel/';
        if (file_exists($path . $r->file('uploadFile'))) {
            unlink($path . $r->file('uploadFile'));
        }
        $fileItem =  $r->file('uploadFile');
        $rowExcepts = $r->rowExcepts;
        $filenameItemImage = $fileItem->getClientOriginalName();
        $fileItem->move($path,  $filenameItemImage);
        $inputFileName = $path . $filenameItemImage;
        $itemArr = array();
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($inputFileName);
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $allData = $objPHPExcel->getActiveSheet();
            $total_line = 0;
            $i = 0;
            $cntr = 0;
            unset($allDataInSheet[1]);
            $rowExcepts = json_decode($rowExcepts, true);
            if (!empty($rowExcepts)) {
                if (count($rowExcepts) > 0) {
                    $total_line = $total_line + count($rowExcepts);
                    for ($j = 0; $j < count($rowExcepts); $j++) {
                        unset($allDataInSheet[$rowExcepts[$j] + 2]);
                    }
                }
            }
            foreach ($allDataInSheet as $value) {
                if (count(array_filter($value)) == 0) {
                } else {
                    $total_line++;
                    $reason = $value['A'];

                    $inserdata[$i]['reason'] = $reason;

                    if (strtolower($value['B']) == 'active') $active = 1;
                    $inserdata[$i]['is_active'] = $active;
                    $inserdata[$i]['is_delete'] = 0;

                    $result = DriverRejectionReason::create($inserdata[$i]);

                    if ($result == false) {
                        $result = 0;
                    } else {
                        $cntr++;
                    }
                    array_push($itemArr, array("id" => $result));
                    $i++;
                }
            }

            if ($total_line == $cntr) {
                $response['status'] = true;
                if ($total_line == 1) {
                    $response['text'] = 'Total : ' . $total_line . ' record is saved';
                } else {
                    $response['text'] = 'Total : ' . $total_line . ' records. All records are saved';
                }
            } else {
                $response['status'] = false;

                $response['text'] = 'Total Records: ' . $total_line . ' Successful: ' . $cntr . ' Unsuccessful: ' . ($total_line - $cntr);
            }
            echo json_encode($response);
        } catch (Exception $e) {
            $response['status'] = false;
            $response['text'] = 'Something went wrong';

            LogHelper::logError(
                'An error occurred while importing the driver rejection reason',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );
        }
    }
}
