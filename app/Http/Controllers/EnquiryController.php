<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;
// Helper
use App\Helpers\LogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
class EnquiryController extends Controller
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
	
	
	public function index()
	{
		try{
           return view('enquiry.index');
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the enquiry index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the enquiry list.');
        }
	}
	 
	public function list(Request $r)
	{
	  try{
			$limit = $r->length;
			$offset = $r->start;
			$search = $r->input('search.value') ?? "";
			$filteredData =  Enquiry::filterEnquiry($search, $limit, $offset);

			$total = $filteredData['totalRecords'];

			$records =  $filteredData['result'];

			$data = [];
			$srno = $offset + 1;
			if ($records->count() > 0) {
				for ($i = 0; $i < $records->count(); $i++) {
					$row = $records[$i];
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y h:i A');
					$data[] = [
					  
						$row->name, 
						$row->email,
						$row->contactno,
						$row->subject,
						$row->subject,
						$formattedDate,
						
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
	   }catch (\Exception $ex) {
			// Log the error
            LogHelper::logError('An error occurred while the enquiry list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the enquiry
            return response()->json([
                "message" => "An error occurred while fetching the enquiry list",
            ], 500);
        }
	}
}