<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Notification;
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

class NotificationController extends Controller
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
	
	
	/*
	 * notification list index page
	 * seemashelar@neosao
	 * dt: 30-jan-2025
	 */
	 
	public function index()
	{
		try{
           return view('notification.index');
		}catch (\Exception $ex) {
			 // Log the error
			LogHelper::logError('An error occurred while the notification index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while the notification list.');
        }
	}
	
	
	/*
	 * get customer list
	 * seemashelar@neosao
	 * dt: 30-jan-2025
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
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the customers list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
  }
  
  
  /*
	 * get driver list
	 * seemashelar@neosao
	 * dt: 30-jan-2025
	 */
	
	public function get_drivers(Request $r)
	{
	  try {
			$html = [];
			$search = $r->input('search');
			
			$result = Driver::where(function ($query) use ($search) {
					$query->where('driver_first_name', 'like', '%' . $search . '%')
						->orWhere('driver_last_name', 'like', '%' . $search . '%');
				})
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			if ($result) {
				foreach ($result as $item) {
					$fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name);
					$html[] = ['id' => $item->id, 'text' => $fullName];
				}
			}

			return response()->json($html);
	  }catch (\Exception $ex) {
            //error log
			LogHelper::logError('An error occurred while the driver list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return response()->json([]);
      }
  }
  
	public function store(Request $request)
	{
		try {
			$rules = [
				'notification_title' => 'required|string|max:255',
				'message' => 'required|string',
				'customer_name' => 'nullable|array',
				'driver_name' => 'nullable|array',
				'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
			];

			$messages = [
				'notification_title.required' => 'The notification title field is required.',
				'message.required' => 'The message field is required.',                
				'image.image' => 'The image must be an image.',
				'image.mimes' => 'The image must be a jpeg, png, jpg, or gif.',
				'image.max' => 'The image size must not exceed 2MB.',
			];

			$validator = Validator::make($request->all(), $rules, $messages);

			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'errors' => $validator->errors()
				], 200);
			}

			$imagePath = null;
			if ($request->hasFile('image')) {
				$file = $request->file('image');                
				$filename = 'notification-image' . time() . '.' . $file->getClientOriginalExtension();          
				$file->move(public_path("uploads/notificationimg"), $filename);
				$imagePath = url('uploads/notificationimg/' . $filename);
			}

			$notifications = [];

			if ($request->type == "customer") {
				if (!empty($request->customer_name)) {
					$customers = Customer::where("is_active", 1)
						->whereIn('id', $request->customer_name)
						->whereNotNull('customer_firebase_token')
						->get();
				} else {
					$customers = Customer::where("is_active", 1)
						->whereNotNull('customer_firebase_token')
						->cursor(); // Using cursor() to prevent memory overflow
				}

				foreach ($customers->chunk(100) as $chunk) {
					$notifications = [];
					foreach ($chunk as $customer) {
						$notifications[] = [
							'title' => $request->notification_title,
							'message' => $request->message,
							'firebase_id' => $customer->customer_firebase_token,
							'type' => "customer",
							'image' => $imagePath,
							'created_at' => now(),
							'updated_at' => now(),
						];
					}
					Notification::insert($notifications);
				}
			}

			if ($request->type == "driver") {
				if (!empty($request->driver_name)) {
					$drivers = Driver::where("is_active", 1)
						->whereIn('id', $request->driver_name)
						->whereNotNull('driver_firebase_token')
						->get();
				} else {
					$drivers = Driver::where("is_active", 1)
						->whereNotNull('driver_firebase_token')
						->cursor();
				}

				foreach ($drivers->chunk(100) as $chunk) {
					$notifications = [];
					foreach ($chunk as $driver) {
						$notifications[] = [
							'title' => $request->notification_title,
							'message' => $request->message,
							'firebase_id' => $driver->driver_firebase_token,
							'type' => "driver",
							'image' => $imagePath,
							'created_at' => now(),
							'updated_at' => now(),
						];
					}
					Notification::insert($notifications);
				}
			}

			LogHelper::logSuccess('The notification added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__);

			return response()->json([
				'status' => 200,
				'message' => 'Notification added successfully.'
			], 200);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while saving the notification', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

			return response()->json([
				'status' => 'error',
				'message' => 'An error occurred while saving the notification.'
			], 500);
		}
	}



	
}
