<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\DriverDocumentDetails as DriverDocumentDetailsModel;

class DriverDocumentDetails extends Controller
{

    /**
     * Display a index page of the resource.
     * @author shreyasm@neosao
     */
    public function index()
    {
        try {
            return view('driver-document-details.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the driver document details index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the driver document details list.');
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
            $srno = $offset + 1;
            $data = array();

            // Fetch driver with filtering
            $filteredData = DriverDocumentDetailsModel::filterDriverDocumentDetails($search, $limit, $offset); // Assuming you have a method for filtering
            $total = $filteredData['totalRecords'];
            $result = $filteredData['result'];

            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y h:i:s A');
                    $action = '';
                    // Check permissions for actions
                    // if (Auth::guard('admin')->user()->canany(['driver.Edit', 'driver.Delete', 'driver.View'])) {
                    $action = '
                        <span class="text-end">
                            <div class="dropdown font-sans-serif position-static">
                                <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="vehicle-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
                                    aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="vehicle-dropdown-' . $row->id . '">
                                    <div class="bg-white py-2">';
                    // if (Auth::guard('admin')->user()->can('driver.View')) {
                    $action .= '<a class="dropdown-item btn-view" href="' . url('driver-document-details/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
                    // }
                    $action .= '</div></div></div></span>';
                    // }

                    // Set status
                    $status = $row->is_active == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">' . __('index.active')  . '</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.in_active')  . '</span></div>';

                    $document_verification_status = $row->document_verification_status == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">' . __('index.verified')  . '</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.not_verified')  . '</span></div>';

                    // Append data to the array
                    $data[] = array(
                        $row->Driver->porter_first_name,
                        $row->Driver->porter_last_name,
                        $row->document_type,
                        $row->document_number,
                        $document_verification_status,
                        $status,
                        $formattedDate,
                        $action
                    );
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
            LogHelper::logError('An error occurred while the driver document details list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the driver document details list",
            ], 500);
        }
    }


    /**
     * @author seemashelar@neosao
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $driver_document_details = DriverDocumentDetailsModel::with("Driver")->where('id', $id)->where('is_delete', 0)->first();

            if (!$driver_document_details) {
                // Log the error
                LogHelper::logError('An error occurred while view the driver document details', 'The invalid driver document details',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid driver document details.');
            }
            return view('driver-document-details.show', compact('driver_document_details'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the driver document details', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the driver document details.');
        }
    }


    /**
     * @author seemashelar@neosao
     * Display the specified resource.
     */

    public function change_status(Request $r, string $id)
    {
        try {

            // Find the driver document details by ID
            $driver_document_details = DriverDocumentDetailsModel::find($id);


            // change status of document flag based on status like pending,approved,reject
            $driver_document_details->document_verification_status = $r->status;
            $driver_document_details->save();

            //success log
            LogHelper::logSuccess('The status of driver document details updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $driver_document_details->id);

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while change status the driver document details', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while change status the driver document details.'
            ]);
        }
    }
}
