<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
// Helper
use App\Helpers\LogHelper;
//models
use App\Models\CustomerOtp;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\GoodsType;
use App\Models\Coupons;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripStatus;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Couponuses;
use App\Models\Customeraddress;
use App\Models\CustomerWalletTransaction;
use DB;
use PDF;
use App\Models\Rating;
use App\Models\CustomerRejectionReason;
use Illuminate\Support\Str;
use App\Classes\Notificationlibv_3;

use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Models\ServiceableZone;

use App\Classes\Phonepe;


class CustomerController extends Controller
{
    

	/*
	*  seemashelar@neosao
	*  Random Otp Generate
	*/
	public function sendotp($mobileno, $otp)
	{
		if (config('app.sms_mode') !== 'TEST') {
			$authKey = env("AUTH_KEY");
			$senderId = "NXSTOP";
			$templateId = "1707174038990536402"; // updated here
			$smsContentType = "english";
			$message = "Your OTP for Login to Next Stop App is {$otp} valid for 10 minutes. Please do not share this OTP. - NEXT STOP";
			$messageEncoded = urlencode($message);

			$url = "http://msg.icloudsms.com/rest/services/sendSMS/sendGroupSms?" .
				"AUTH_KEY={$authKey}" .
				"&message={$messageEncoded}" .
				"&senderId={$senderId}" .
				"&routeId=1" .
				"&mobileNos={$mobileno}" .
				"&smsContentType={$smsContentType}" .
				"&templateid={$templateId}";

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
			));

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				$errorMsg = curl_error($ch);
				Log::error("SMS not sent to $mobileno. cURL Error: $errorMsg");
				curl_close($ch);
				return [
					'status' => 'failed',
					'msg' => 'cURL Error: ' . $errorMsg
				];
			}

			curl_close($ch);

			Log::info("SMS sent successfully to $mobileno with OTP $otp. Response: $response");
		}
		return [
			'status' => 'success',
			'msg' => "SMS sent successfully to $mobileno with OTP $otp"
		];
	}



	public function generateOTP($contactNumber, $type, $tripId)
	{
		 if ($contactNumber == "7385566988") {
			$otp = "123456";
		} else {
			$otp = config('app.sms_mode') !== "TEST"
				? $this->randomOTP(6)
				: "123456";		}

		try {
			$result = CustomerOtp::create([
				'mobile' => $contactNumber,
				'otp' => $otp,
				'expired_at' => now()->addMinutes(10),
				'type' => $type,
				'trip_id' => $tripId
			]);
			return $result->otp;
		} catch (Exception $e) {
			return false;
		}
	}

	public function randomOTP($n)
	{
		$characters = '0123456789';
		$randomString = '';
		for ($i = 0; $i < $n; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}
		return $randomString;
	}


	/*
	*  seemashelar@neosao
	*  check otp is expired or not
	*/

	public function checkRegisterOTP($otp, $contactNumber, $type = "", $tripId = "")
	{
		$query = CustomerOtp::where('mobile', $contactNumber)
			->where('otp', $otp)
			->when($type != "", function ($q) use ($type) {
				return $q->where('type', $type);
			})
			->when($tripId != "", function ($q) use ($tripId) {
				return $q->where('trip_id', $tripId);
			});

		$result = $query->first();

		if (!empty($result)) {
			if ($result->expired_at < now()) {
				// Delete expired OTP
				CustomerOtp::where('mobile', $contactNumber)
					->when($type != "", function ($q) use ($type) {
						return $q->where('type', $type);
					})
					->when($tripId != "", function ($q) use ($tripId) {
						return $q->where('trip_id', $tripId);
					})
					->delete();
				return 'expired';
			}

			// OTP is valid, delete it
			CustomerOtp::where('mobile', $contactNumber)
				->when($type != "", function ($q) use ($type) {
					return $q->where('type', $type);
				})
				->when($tripId != "", function ($q) use ($tripId) {
					return $q->where('trip_id', $tripId);
				})
				->delete();

			return true;
		}

		return false;
	}


	public function referral_code()
	{
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$digits = '0123456789';
		$characters = $letters . $digits;

		// Ensure at least one letter and one digit
		$code = $letters[random_int(0, strlen($letters) - 1)] .
			$digits[random_int(0, strlen($digits) - 1)];

		// Fill the rest of the code with random characters
		for ($i = 0; $i < 3; $i++) {
			$code .= $characters[random_int(0, strlen($characters) - 1)];
		}

		// Shuffle to randomize the order
		return str_shuffle($code);
	}


	//seemashelar@neosao
	//Resend otp 
	public function resend_otp(Request $r)
	{
		try {
			// Get all input data
			$input = $r->all();

			// Validate mobile number
			$validator = Validator::make($input, [
				'mobileNumber' => ['required', 'digits:10', 'numeric']
			], [
				'mobileNumber.required' => 'The mobile number is required.',
				'mobileNumber.digits' => 'The mobile number must be 10 digits.',
				'mobileNumber.numeric' => 'The mobile number must be a number.'
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return validation error message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Generate OTP for the mobile number
			$otpNumber = $this->generateOTP($r->mobileNumber, 'customer', "");

			// Check if OTP generation is successful
			if ($otpNumber != false) {
				// Optional: Skip sending OTP for specific number
				if ($r->mobileNumber != "7385566988") {
					$otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
					if ($otpResult["status"] == "failed") {
						return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
					}
				}

				//success log
				LogHelper::logSuccess('The customer otp resend successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

				// Return success response
				return response()->json(['status' => 200, 'message' => 'OTP sent successfully'], 200);
			}

			//log error
			LogHelper::logError('An error occurred while the customer resend otp', 'Failed to resend OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

			// Return failure response if OTP could not be generated
			return response()->json(['status' => 300, 'message' => 'Failed to send OTP.'], 200);
		} catch (\Exception $e) {
			//log error
			LogHelper::logError('An error occurred while the customer resend otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

			// Catch and return error in case of exception
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	//seemashelar@neosao
	//customer register 

	public function customer_register(Request $r)
	{
		try {
			// Get all input data from the request
			$input = $r->all();

			// Validate the input data
			$validator = Validator::make($input, [
				'firstName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',  // First name validation
				'lastName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',   // Last name validation
				'mobileNumber' => [
					'required',
					'digits:10',
					'numeric',
					Rule::unique('customers', 'customer_phone')->where(function ($query) {
						return $query->where('is_delete', '=', '0');
					}),  // Ensure mobile number is unique and not marked as deleted
				],
				'email' => [
					'nullable',
					'email',
					Rule::unique('customers', 'customer_email')->where(function ($query) {
						return $query->where('is_delete', '=', '0');
					}),  // Ensure email is unique and not marked as deleted
				],
				'profilePhoto' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
				'referralCode' => 'nullable|exists:customers,customer_referral_code',
			], [
				// Custom error messages for validation
				'firstName.required' => 'The first name is required.',
				'firstName.min' => 'The first name must be at least 2 characters.',
				'firstName.max' => 'The first name must not be greater than 150 characters.',
				'firstName.regex' => 'Enter a valid first name.',
				'lastName.required' => 'The last name is required.',
				'lastName.min' => 'The last name must be at least 2 characters.',
				'lastName.max' => 'The last name must not be greater than 150 characters.',
				'lastName.regex' => 'Enter a valid last name.',
				'email.email' => 'The email address must be a valid email address.',
				'email.unique' => 'The email address is already in use.',
				'mobileNumber.required' => 'The mobile number is required.',
				'mobileNumber.digits' => 'The mobile number must be 10 digits.',
				'mobileNumber.numeric' => 'The mobile number must be a number.',
				'mobileNumber.unique' => 'The mobile number already exists.',
				'profilePhoto.file' => 'The Profile photo must be a valid file.',
				'profilePhoto.mimes' => 'The Profile photo must be a file of type: jpg, png,jpeg',
				'profilePhoto.max' => 'The Profile photo file size must not exceed 2MB.'
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			$referralById = "";
			$referralByCode = "";
			if (!empty($r->referralCode)) {
				$referralCustomer = Customer::where("customer_referral_code", $r->referralCode)
					->first();
				if (!empty($referralCustomer)) {
					$referralById = $referralCustomer->id;
					$referralByCode = $referralCustomer->customer_referral_code;

					//get referral points
					$getReferralPoints = Setting::where("id", 6)->first();
					$points = 0;
					if (!empty($getReferralPoints)) {
						$points = $getReferralPoints->setting_value;
					}

					$referralCustomer->customer_referral_wallet += $points;
					$referralCustomer->save();
				}
			}

			// Prepare data to be inserted into the database
			$data = [
				"customer_first_name" => $r->firstName,
				"customer_last_name" => $r->lastName,
				"customer_email" => $r->email,
				"customer_phone" => $r->mobileNumber,
				"is_active" => 1,
				"customer_referral_code" => $this->referral_code(),
				"is_delete" => 0,
				"customer_referral_by" => $referralByCode,
				"customer_referral_by_id" => $referralById,
			];

			// Handle the porter photo image upload
			if ($r->hasFile('profilePhoto')) {
				$file = $r->file('profilePhoto');
				$imageName = 'customers' . time() . '.' . $file->getClientOriginalExtension();
				$path = Storage::disk('public')->putFileAs('customers', $file, $imageName);
				$data["customer_avatar"] = $path; // Save the image name in the database
			}

			// Create a new customer record in the database
			$result = Customer::create($data);

			// Check if the record was successfully created
			if ($result) {

				// Generate a token for the customer
				$token = $result->createToken('Customer', ['*'])->plainTextToken;

				// Check if the customer exists
				$customer = Customer::where("is_active", 1)
					->where("id", $result->id)
					->where("is_delete", 0)
					->first();
				if (!empty($customer)) {
					// Prepare the data to return
					$dataCustomer['id'] = $customer->id;
					$dataCustomer['firstName'] = $customer->customer_first_name ?? "";
					$dataCustomer['lastName'] = $customer->customer_last_name ?? "";
					$dataCustomer['phoneNumber'] = $customer->customer_phone ?? "";
					$dataCustomer['email'] = $customer->customer_email ?? "";
					$data['profilePhoto'] = $customer->customer_avatar ?  asset('storage/' . $customer->customer_avatar) : asset('/assets/img/user/default-user.png');
					$dataCustomer['referralCode'] = $customer->customer_referral_code ?? "";
				}
				//success log
				LogHelper::logSuccess('The customer create successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $result->id);
				// Return success response
				return response()->json(['status' => 200, 'message' => 'Data added successfully', 'result' => $dataCustomer, 'token' => $token, 'accountExist' => 1], 200);
			}

			//log error
			LogHelper::logError('An error occurred while the customer create.', 'Failed to add data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return failure response if creation failed
			return response()->json(['status' => 300, 'message' => 'Failed to add data'], 200);
		} catch (\Exception $e) {
			//log error
			LogHelper::logError('An error occurred while the customer create.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);
			// Catch any exceptions and return an error response
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}

	//seemashelar@neosao
	//login with otp

	public function login(Request $r)
	{
		try {
			// Get all input data from the request
			$input = $r->all();

			// Validate the mobile number to ensure it is required, a 10-digit number, and numeric
			$validator = Validator::make($input, [
				'mobileNumber' => 'required|digits:10|numeric',
                'device'=>'nullable',
			]);

			// Check if validation fails 
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
          
            
			/*if($r->device=="android"){
				return response()->json(['status' => 300, 'message' => 'Maintenance Notice: This app is currently under maintenance and will be unavailable for the next few days. Thank you for your patience.'], 200);
			}*/
			

			// Check if the mobile number exists in the Customer table and is active (not deleted)
			$result = Customer::where("customer_phone", $r->mobileNumber)
				->where("is_active", 1)
				->where("is_delete", 0)
				->first();

			// If the customer is found and active
			if (!empty($result)) {
				// Generate OTP for the mobile number
				$otpNumber = $this->generateOTP($r->mobileNumber, "customer", "");

				// Check if OTP generation is successful
				if ($otpNumber != false) {
					// Optional: Skip sending OTP for specific number
					if ($r->mobileNumber != "7385566988") {
						$otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
						if ($otpResult["status"] == "failed") {
							return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
						}
					}

					//success log
					LogHelper::logSuccess('The customer otp send successfully for login.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);


					// Return success response with OTP and account existence status
					return response()->json(['status' => 200, 'message' => 'OTP was sent successfully!',  'mobile' => $r->mobileNumber, 'otp' => $otpNumber], 200);
				} else {

					//log error
					LogHelper::logError('An error occurred while the customer send otp', 'Failed to send OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

					// Return failure response if OTP could not be generated
					return response()->json(['status' => 300, 'message' => 'Failed to send OTP!'], 200);
				}
			} else {


				// If the customer is not found, check if the account is inactive
				$checkUserDelete = Customer::where("customer_phone", $r->mobileNumber)
					->where("is_active", 0)
					->first();

				if (!empty($checkUserDelete)) {
					/*if($checkUserDelete->is_customer_delete == 1){
						return response()->json(['status' => 300, 'message' => 'Deleted account. Please contact the administrator or create a new account.'], 200);
					}*/
					if ($checkUserDelete->is_block == 1) {
						return response()->json(['status' => 300, 'message' => 'Your account is blocked by admin. Please contact the administrator for assistance.'], 200);
					}
				}
				// Generate OTP for the mobile number
				$otpNumber = $this->generateOTP($r->mobileNumber, "customer", "");

				// Optional: Skip sending OTP for specific number
				if ($r->mobileNumber != "7385566988") {
					$otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
					if ($otpResult["status"] == "failed") {
						return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
					}
				}
				//log error
				LogHelper::logError('An error occurred while the customer login', 'Customer not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

				// Return response if the customer is not found
				return response()->json(['status' => 200, 'message' => 'Customer not found', 'mobile' => $r->mobileNumber, 'otp' => $otpNumber], 200);
			}
		} catch (\Exception $e) {
			//log error
			LogHelper::logError('An error occurred while the customer login.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

			// Catch any exceptions and return an error response
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	//seemashelar@neosao
	//verify otp with mobile number 

	public function verify_otp(Request $r)
	{
		try {
			// Get all input data from the request
			$input = $r->all();

			// Validate the mobile number and OTP to ensure they are both present and in the correct format
			$validator = Validator::make($input, [
				'mobileNumber' => 'required|digits:10|numeric',
				'otp' => 'required|numeric'
			]);

			// Check if validation fails
			if ($validator->fails()) {

				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Check if the provided OTP is valid by calling a custom method
			$verifyOTP = $this->checkRegisterOTP($r->otp, $r->mobileNumber, "customer", "");

			// If OTP has expired, return an expired message
			if ($verifyOTP === 'expired') {
				//log error
				LogHelper::logError('An error occurred while the customer verify otp', 'OTP has been expired.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

				$response = [
					'status' => 300,
					'message' => 'OTP has been expired.'
				];
				return response()->json($response, 200);
			}
			// If OTP is invalid, return an invalid OTP message
			elseif ($verifyOTP === false) {

				//log error
				LogHelper::logError('An error occurred while the customer verify otp', 'Invalid OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

				$response = [
					'status' => 300,
					'message' => 'Invalid OTP'
				];
				return response()->json($response, 200);
			} else {
				// Check if the customer exists, is active, and not deleted
				$result = Customer::where("is_active", 1)
					->where("customer_phone", $r->mobileNumber)
					->where("is_delete", 0)
					->first();

				// If the customer exists
				if (!empty($result)) {
					// Generate a token for the customer
					$token = $result->createToken('Customer', ['*'])->plainTextToken;

					// Prepare the data to return
					$data['id'] = $result->id;
					$data['firstName'] = $result->customer_first_name ?? "";
					$data['lastName'] = $result->customer_last_name ?? "";
					$data['phoneNumber'] = $result->customer_phone ?? "";
					$data['email'] = $result->customer_email ?? "";
					$data['wallet'] = $result->customer_wallet_balance;
					$data['customerWallet'] = $result->customer_referral_wallet;
					$data['referralCode'] = $result->customer_referral_code ?? "";
					$data['firebaseToken'] = $result->customer_firebase_token ?? "";
					//success log 
					LogHelper::logSuccess('The customer verify otp successfully while login.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

					// Return success response with user data and token 
					return response()->json(['status' => 200, 'message' => 'OTP Verified and Logged in successfully', "result" => $data, "token" => $token, 'accountExist' => 1], 200);
				}

				//log error
				LogHelper::logError('An error occurred while the customer verify otp', 'Customer not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

				// If the customer is not found, return an error message
				return response()->json(['status' => 200, 'message' => 'OTP Verified and Logged in successfully', 'accountExist' => 0, "result" => null, "token" => ""], 200);
			}
		} catch (\Exception $e) {

			//log error
			LogHelper::logError('An error occurred while the customer verify otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

			// Catch any exceptions and return an error response
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	//seemashelar@neosao
	//get basic profile info
	public function basic_profile_info(Request $r)
	{
		try {
			$input = $r->all(); // Get all input data from the request

			$validator = Validator::make($input, [
				'customerId' => 'required|integer' // Validate that customerId is required and an integer
			], [
				'customerId.required' => 'The customer ID is required.', // Custom message for missing customerId
				'customerId.integer' => 'The customer ID must be a valid integer.', // Custom message for invalid customerId
			]);

			if ($validator->fails()) { // Check if validation fails
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first() // Return the first validation error message
				];
				return response()->json($response, 200);
			}

			$result = Customer::where("is_active", 1) // Ensure customer is active
				->where("id", $r->customerId) // Find customer by ID
				->where("is_delete", 0) // Ensure customer is not deleted
				->first(); // Get the first matching result

			if (!empty($result)) { // If the customer is found
				// Prepare the customer data to return
				$data['id'] = $result->id;
				$data['firstName'] = $result->customer_first_name ?? ""; // Default to empty string if not set
				$data['lastName'] = $result->customer_last_name ?? ""; // Default to empty string if not set
				$data['phoneNumber'] = $result->customer_phone ?? ""; // Default to empty string if not set
				$data['email'] = $result->customer_email ?? ""; // Default to empty string if not set
				$data['wallet'] = $result->customer_wallet_balance;
				$data['profilePhoto'] = $result->customer_avatar ?  asset('storage/' . $result->customer_avatar) : asset('/assets/img/user/default-user.png');
				$data['referralWallet'] = $result->customer_referral_wallet ?? 0;
				$data['referralCode'] = $result->customer_referral_code ?? "";
				$data['firebaseToken'] = $result->customer_firebase_token ?? "";
				LogHelper::logSuccess('The customer basic details get successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $result->id); // Log success message

				return response()->json(['status' => 200, 'message' => 'Data found', "result" => $data], 200); // Return success response with customer data
			}

			LogHelper::logError('An error occurred while the customer basic details', 'Customer not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, ''); // Log error if customer not found

			return response()->json(['status' => 300, 'message' => 'Customer not found'], 200); // Return error response if customer not found
		} catch (\Exception $e) { // Catch any exceptions
			LogHelper::logError('An error occurred while the customer basic details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->customerId); // Log error message
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400); // Return error response
		}
	}

	//seemashelar@neosao
	//basic profile update
	public function basic_profile_update(Request $r)
	{
		try {
			// Validate the input data
			$input = $r->all();
			$id = $r->customerId;
			$validator = Validator::make($input, [
				'customerId' => 'required',
				'firstName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',  // First name validation
				'lastName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',   // Last name validation
				'email' => [
					'nullable',
					'email',
					Rule::unique('customers', 'customer_email')->where(function ($query) use ($id) {
						$query->where('is_delete', '0')
							->where('id', '!=', $id);
					}),   // Ensure email is unique and not marked as deleted 
				],
				'profilePhoto' => 'nullable|file|mimes:jpg,png,jpeg|max:2048'
			], [
				// Custom error messages for validation
				'firstName.required' => 'The first name is required.',
				'firstName.min' => 'The first name must be at least 2 characters.',
				'firstName.max' => 'The first name must not be greater than 150 characters.',
				'firstName.regex' => 'Enter a valid first name.',
				'lastName.required' => 'The last name is required.',
				'lastName.min' => 'The last name must be at least 2 characters.',
				'lastName.max' => 'The last name must not be greater than 150 characters.',
				'lastName.regex' => 'Enter a valid last name.',
				'email.email' => 'The email address must be a valid email address.',
				'email.unique' => 'The email address is already in use.',
				'profilePhoto.file' => 'The Profile photo must be a valid file.',
				'profilePhoto.mimes' => 'The Profile photo must be a file of type: jpg, png,jpeg',
				'profilePhoto.max' => 'The Profile photo file size must not exceed 2MB.',

			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			//check whether customer is present in database
			$customer = Customer::where("is_active", 1)
				->where("is_delete", 0)
				->where("id", $id)
				->first();
			if (!empty($customer)) {
				// Prepare data to be updated into the database
				$data = [
					"customer_first_name" => $r->firstName,
					"customer_last_name" => $r->lastName,
					"customer_email" => $r->email
				];

				// Handle the porter photo image upload
				if ($r->hasFile('profilePhoto')) {
					$file = $r->file('profilePhoto');
					$imageName = 'customers' . time() . '.' . $file->getClientOriginalExtension();
					$path = Storage::disk('public')->putFileAs('customers', $file, $imageName);
					$data["customer_avatar"] = $path; // Save the image name in the database
				}

				//customer update 
				$result = $customer->update($data);
				if ($result == true) {
					//success log
					LogHelper::logSuccess('The customer updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
					// Return success response
					return response()->json(['status' => 200, 'message' => 'Data updated successfully'], 200);
				}

				//log error
				LogHelper::logError('An error occurred while the customer update.', 'Failed to update data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
				// Return failure response if update failed
				return response()->json(['status' => 300, 'message' => 'Failed to update data'], 200);
			}

			//log error
			LogHelper::logError('An error occurred while the customer update data.', 'Customer not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return failure response if creation failed
			return response()->json(['status' => 300, 'message' => 'Customer not found.'], 200);
		} catch (\Exception $e) {
			//log error
			LogHelper::logError('An error occurred while the getting customer update data.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			// Catch any exceptions and return an error response 
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}


	//seemashelar@neosao
	//customer logout

	public function customer_logout(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [

				'customerId' => 'required|integer',
			], [
				'customerId.required' => 'The customer ID is required.',
				'customerId.integer' => 'The customer ID must be a valid integer.',
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			//customer checking
			$customer = Customer::where("id", $r->customerId)->first();
			if (!empty($customer)) {
				$customer->tokens()->delete();
				//success log 
				LogHelper::logSuccess('The customer logged out successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->customerId);
				//success response
				return response()->json(["status" => 200, "message" => "Logged out."], 200);
			}
			//log error
			LogHelper::logError('An error occurred while the customer logged out.', 'Customer not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["status" => 300, "message" => "Customer not found."], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while customer logout.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}


	public function vehicle_type_list(Request $request)
	{
		try {
			$input = $request->all();

			// Validate the input
			$validator = Validator::make($input, [
				'search' => 'nullable|string',
				'page' => 'nullable|integer|min:1',

			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Get input values
			$searchTerm = $request->input('search', '');
			$perPage = 10; // Fixed perPage to 10
			$page = $request->input('page', 1); // Default to page 1

			$query = VehicleType::select("vehicle_type", "id")->where("is_delete", 0);
			if (!empty($searchTerm)) {
				$query->where(function ($query) use ($searchTerm) {
					$query->where('vehicle_type', 'LIKE', "%{$searchTerm}%");
				});
			}

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch results
			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			return response()->json([
				'status' => 200,
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"data" => $result,
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while vehicle types.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	public function home_vehicle_list(Request $request)
	{
		try {
			$input = $request->all();

			// Validate the input
			$validator = Validator::make($input, [
				'page' => 'nullable|integer|min:1',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Get input values
			$searchTerm = $request->input('search', '');
			$perPage = 10; // Fixed perPage to 10
			$page = $request->input('page', 1); // Default to page 1

			$query = Vehicle::with("vehicleType")
				->where("vehicles.is_active", 1)
				->where("vehicles.is_delete", 0);

			// Count the total records
			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch results
			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->take($perPage)
				->get();

			// Prepare the result data
			$vehicleArray = [];
			if ($result && count($result) > 0) {
				foreach ($result as $item) {
					$data = [
						"vehicleId" => $item->id,
						"vehicleName" => $item->vehicle_name ?? "",
						"vehicleType" => $item->vehicleType->vehicle_type ?? "",
						"vehicleMaximumLoad" => $item->vehicle_max_load_capacity ?? "",
						"vehicleFixedKm" => $item->vehicle_fixed_km ?? 0,
						"vehiclePerKmCharge" => $item->vehicle_per_km_delivery_charge ?? "",
						"vehiclePerKmExtraDeliveryCharge" => $item->vehicle_per_km_extra_delivery_charge ?? "",
						"vehicleImage" => $item->vehicle_icon ? asset('storage/' . $item->vehicle_icon) : "",
						"vehicleDescription" => $item->vehicle_description ?? "",
					];
					array_push($vehicleArray, $data);
				}
			}

			return response()->json([
				'status' => 200,
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"data" => $vehicleArray,
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while fetching vehicle types.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}



	public function vehicle_list(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'customerId' => 'required|integer',
				'sourceLatitude' => 'required',
				'sourceLongitude' => 'required',
				'destinationLatitude' => 'required',
				'destinationLongitude' => 'required',
				'stopAddressLatitude' => 'nullable|array',
				'stopAddressLongitude' => 'nullable|array',
			], [
				'customerId.required' => 'The customer source ID is required.',
				'customerId.integer' => 'The customer source ID must be a valid integer.',
				'sourceLatitude.required' => 'The source latitude is required.',
				'sourceLongitude.required' => 'The source longitude is required.',
				'destinationLatitude.required' => 'The destination latitude is required.',
				'destinationLongitude.required' => 'The destination longitude is required.',
			]);
			$stopLatitudes = $r->stopAddressLatitude ?? [];
			$stopLongitudes = $r->stopAddressLongitude ?? [];

			$distance = $this->calculate_shortest_route($r->sourceLatitude, $r->sourceLongitude, $r->destinationLatitude, $r->destinationLongitude, $stopLatitudes, $stopLongitudes);
			//print_r($distance);
			//echo $distance;
			//$distance=10;

			$vehicle = Vehicle::with("vehicleType")
				->where("vehicles.is_active", 1)
				->where("vehicles.is_delete", 0)
				->whereHas('vehicleType', function ($query) {
					$query->where('is_active', 1)->where('is_delete', 0);
				})
				->get();
			$vehicleArray = [];
			if ($vehicle && count($vehicle) > 0) {
				foreach ($vehicle as $item) {

					$fixedKm = $item->vehicle_fixed_km ?? 0;
					$fixedKmCharge = $item->vehicle_fixed_km_delivery_charge ?? 0;
					$perKmCharge = $item->vehicle_per_km_delivery_charge ?? 0;
					$extraPerKmCharge = $item->vehicle_per_km_extra_delivery_charge ?? 0;
					$totalDistance = $distance["total_distance_km"] ?? 0;
					//echo $totalDistance."_";
					//echo $fixedKm;
					if ($totalDistance <= $fixedKm) {
						$deliveryCharge = $fixedKmCharge;
						//$deliveryCharge = round($totalDistance) * $fixedKmCharge;
					} else {
						$extraCharge = (round($totalDistance) - $fixedKm) * $perKmCharge;

						$deliveryCharge = $fixedKmCharge + $extraCharge;
					}
					//echo $deliveryCharge;
					$data = [
						"vehicleId" => $item->id,
						"vehicleName" => $item->vehicle_name ?? "",
						"vehicleType" => $item->vehicleType->vehicle_type ?? "",
						"vehicleMaximumLoad" => $item->vehicle_max_load_capacity ?? "",
						"vehicleFixedKm" => $fixedKm,
						"vehicleFixedKmCharge" => $fixedKmCharge,
						"vehiclePerKmCharges" => $perKmCharge,
						"vehiclePerKmCharge" => round($deliveryCharge),
						"vehicleTime" => round($distance["total_duration_minutes"]),
						"vehiclePerKmExtraDeliveryCharge" => $extraPerKmCharge,
						"vehicleImage" => $item->vehicle_icon ? asset('storage/' . $item->vehicle_icon) : "",
						"distance" => round($totalDistance), // You might want to include this for reference
					];
					array_push($vehicleArray, $data);
				}

				LogHelper::logSuccess('The vehicle list get successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, ""); // Log success message

				return response()->json(['status' => 200, 'message' => 'Data found', "result" => $vehicleArray], 200); // Return success response with vehicle data
			}
			//log error
			LogHelper::logError('An error occurred while the vehicle list.', 'Vehicle List.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["status" => 300, "message" => "vehicle list not found."], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while vehicle list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	public function calculate_shortest_distance($originLat, $originLng, $destinationLat, $destinationLng, $stopLatitudes = [], $stopLongitudes = [])
	{
		$apiKey = "AIzaSyA-5HvVHK-Nf2vvLyh9GYk-vRWKWksCV2w";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json";
		$origins = "{$originLat},{$originLng}";
		$destinations = "{$destinationLat},{$destinationLng}";

		if (!empty($stopLatitudes) && !empty($stopLongitudes)) {
			foreach ($stopLatitudes as $index => $stopLat) {
				if (isset($stopLongitudes[$index])) {
					$destinations .= "|{$stopLat},{$stopLongitudes[$index]}";
				}
			}
		}

		$params = [
			'origins' => $origins,
			'destinations' => $destinations,
			'key' => $apiKey,
		];

		$response = Http::get($url, $params);
		if ($response->successful()) {
			$data = $response->json();
			if (isset($data['rows'][0]['elements'][0])) {
				$distance = $data['rows'][0]['elements'][0]['distance']['value'];
				$duration = $data['rows'][0]['elements'][0]['duration']['value'];
				// Convert distance to kilometers and duration to hours/minutes
				return [
					'total_distance_km' => $distance / 1000, // Distance in kilometers
					'total_duration_minutes' => $duration / 60 // Duration in minutes
				];
			}
		}
		return ['total_distance_km' => 0, 'total_duration_minutes' => 0]; // Return zero if error occurs
	}


	public function calculate_shortest_route($sourceLat, $sourceLng, $destinationLat, $destinationLng, $stopLatitudes = [], $stopLongitudes = [])
	{
		$apiKey = "AIzaSyA-5HvVHK-Nf2vvLyh9GYk-vRWKWksCV2w";
		$stops = [];
		$totalDistance = 0;
		$totalDuration = 0;

		// Add stop points to a single array
		foreach ($stopLatitudes as $index => $stopLat) {
			if (!empty($stopLat) && isset($stopLongitudes[$index])) {
				$stops[] = "{$stopLat},{$stopLongitudes[$index]}";
			}
		}
		$origins = "{$sourceLat},{$sourceLng}";
		$destinations = "{$destinationLat},{$destinationLng}";

		// If there are no stops, calculate distance directly
		if (empty($stops)) {
			$result = $this->calculate_shortest_distance($sourceLat, $sourceLng, $destinationLat, $destinationLng);
			return $result; // Return the result containing distance and duration
		}

		// Calculate distances and durations through stops
		$currentLocation = $origins;
		foreach ($stops as $stop) {
			$url = "https://maps.googleapis.com/maps/api/distancematrix/json";
			$params = [
				'origins' => $currentLocation,
				'destinations' => $stop,
				'key' => $apiKey,
			];
			$response = Http::get($url, $params);
			if ($response->successful()) {
				$data = $response->json();
				if (isset($data['rows'][0]['elements'][0])) {
					$totalDistance += $data['rows'][0]['elements'][0]['distance']['value'];
					$totalDuration += $data['rows'][0]['elements'][0]['duration']['value'];
					$currentLocation = $stop; // Update current location to the stop
				}
			}
		}

		// Add the final distance from the last stop to the destination
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json";
		$params = [
			'origins' => $currentLocation,
			'destinations' => $destinations,
			'key' => $apiKey,
		];
		$response = Http::get($url, $params);
		if ($response->successful()) {
			$data = $response->json();
			if (isset($data['rows'][0]['elements'][0])) {
				$totalDistance += $data['rows'][0]['elements'][0]['distance']['value'];
				$totalDuration += $data['rows'][0]['elements'][0]['duration']['value'];
			}
		}

		// Convert totalDistance to kilometers and totalDuration to minutes
		return [
			'total_distance_km' => $totalDistance / 1000, // Distance in kilometers
			'total_duration_minutes' => $totalDuration / 60 // Duration in minutes
		];
	}

	public function calculate_shortest_path($sourceLat, $sourceLng, $destinationLat, $destinationLng, $stopLocations = [])
	{
		$apiKey = "AIzaSyA-5HvVHK-Nf2vvLyh9GYk-vRWKWksCV2w";
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json";

		$allPoints = array_merge([["lat" => $sourceLat, "lng" => $sourceLng]], $stopLocations, [["lat" => $destinationLat, "lng" => $destinationLng]]);
		$origins = [];
		$destinations = [];

		foreach ($allPoints as $point) {
			$origins[] = "{$point['lat']},{$point['lng']}";
			$destinations[] = "{$point['lat']},{$point['lng']}";
		}

		$params = [
			'origins' => implode('|', $origins),
			'destinations' => implode('|', $destinations),
			'key' => $apiKey,
		];

		$response = Http::get($url, $params);
		if ($response->successful()) {
			$data = $response->json();
			if (isset($data['rows'])) {
				$distanceMatrix = [];
				foreach ($data['rows'] as $row) {
					$distances = array_column($row['elements'], 'distance');
					$distanceMatrix[] = array_map(fn($d) => $d['value'], $distances); // Use `value` for numeric distances
				}

				return $this->find_optimal_route($distanceMatrix);
			}
		}
		return ['error' => 'Failed to retrieve distances from Google API'];
	}

	private function find_optimal_route($distanceMatrix)
	{
		$n = count($distanceMatrix);
		$visited = array_fill(0, $n, false);
		$visited[0] = true;
		$route = [0];
		$totalDistance = 0;

		for ($i = 0; $i < $n - 1; $i++) {
			$current = $route[$i];
			$nearest = null;
			$minDistance = PHP_INT_MAX;

			for ($j = 0; $j < $n; $j++) {
				if (!$visited[$j] && $distanceMatrix[$current][$j] < $minDistance) {
					$nearest = $j;
					$minDistance = $distanceMatrix[$current][$j];
				}
			}

			$route[] = $nearest;
			$visited[$nearest] = true;
			$totalDistance += $minDistance;
		}

		$totalDistance += $distanceMatrix[end($route)][0]; // Return to start if needed
		return ['route' => $route, 'totalDistance' => $totalDistance];
	}

	//good types api used for order

	public function good_types_list(Request $request)
	{
		try {
			$input = $request->all();

			// Validate the input
			$validator = Validator::make($input, [
				'search' => 'nullable|string',
				'page' => 'nullable|integer|min:1',

			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Get input values
			$searchTerm = $request->input('search', '');
			$perPage = 10; // Fixed perPage to 10
			$page = $request->input('page', 1); // Default to page 1

			$query = GoodsType::select("goods_name", "id")->where("is_delete", 0);
			if (!empty($searchTerm)) {
				$query->where(function ($query) use ($searchTerm) {
					$query->where('goods_name', 'LIKE', "%{$searchTerm}%");
				});
			}

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch results
			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			return response()->json([
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"data" => $result,
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while good types.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	// coupon list

	public function coupon_list(Request $request)
	{
		try {
			$input = $request->all();
			// Validate the input
			$validator = Validator::make($input, [
				'search' => 'nullable|string',
				'page' => 'nullable|integer|min:1',

			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Get input values
			$searchTerm = $request->input('search', '');
			$perPage = 10; // Fixed perPage to 10
			$page = $request->input('page', 1); // Default to page 1

			$query = Coupons::select("id", "coupon_code", "coupon_type", "coupon_amount_or_percentage", "coupon_cap_limit", "coupon_min_order_amount", "coupon_start_date", "coupon_end_date")
				->where("is_delete", 0)
				->where('coupon_start_date', '<=', Carbon::now())
				->where('coupon_end_date', '>=', Carbon::now());
			if (!empty($searchTerm)) {
				$query->where(function ($query) use ($searchTerm) {
					$query->orWhere('coupons.coupon_code', 'like', "%{$searchTerm}%")
						->orWhere('coupons.coupon_type', 'like', "%{$searchTerm}%")
						->orWhere('coupons.coupon_amount_or_percentage', 'like', "%{$searchTerm}%")
						->orWhere('coupons.coupon_cap_limit', 'like', "%{$searchTerm}%")
						->orWhere('coupons.coupon_min_order_amount', 'like', "%{$searchTerm}%");
				});
			}

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch results
			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			return response()->json([
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"result" => $result,
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while coupon list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

    public function calculate_tripamount(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'customerId' => 'required|integer',
				'sourceLatitude' => 'required',
				'sourceLongitude' => 'required',
				'sourceAddress' => 'required',
				'sourceMobileNumber' => 'required',
				'destinationLatitude' => 'required',
				'destinationLongitude' => 'required',
				'destinationAddress' => 'required',
				'destinationName' => 'required',
				'destinationType' => 'required',
				'destinationMobileNumber' => 'required',
				'vehicleId' => 'required',
				'goodsTypeId' => 'nullable',
				'couponId' => 'nullable|integer',
				'amount' => 'required',
				'stopAddress' => 'nullable|array',
				'stopAddressLatitude' => 'nullable|array',
				'stopAddressLongitude' => 'nullable|array',
				'stopAddressName' => 'nullable|array',
				'stopAddressType' => 'nullable|array',
			], [
				'customerId.required' => 'Customer ID is required.',
				'customerId.integer' => 'Customer ID must be a valid number.',
				'sourceLatitude.required' => 'Please provide the latitude for the source location.',
				'sourceLongitude.required' => 'Please provide the longitude for the source location.',
				'sourceAddress.required' => 'Source address is required.',
				'sourceMobileNumber.required' => 'Source mobile number is required.',
				'destinationLatitude.required' => 'Please provide the latitude for the destination location.',
				'destinationLongitude.required' => 'Please provide the longitude for the destination location.',
				'destinationAddress.required' => 'Destination address is required.',
				'destinationMobileNumber.required' => 'Destination mobile number is required.',
				'vehicleId.required' => 'Vehicle selection is mandatory.',
				//'goodsTypeId.required' => 'Goods type selection is mandatory.',
				'couponId.integer' => 'Coupon ID must be a valid number.',
				'amount.required' => 'Amount field is required.',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$vehicleDetails = [];
			$couponDetails = [];
			$goodsTypeName = "";
			$nextLimit = 1;
			$goodsType = GoodsType::select("goods_name", "id")
				->where("id", $r->goodsTypeId)
				->where("is_delete", 0)
				->first();
			if ($goodsType) {
				$goodsTypeName = $goodsType->goods_name;
			}
			$vehicle = Vehicle::with("vehicleType")
				->where("vehicles.is_active", 1)
				->where("vehicles.is_delete", 0)
				->where("vehicles.id", $r->vehicleId)
				->first();
			if (!empty($vehicle)) {
				$vehicleDetails = [
					"id" => $vehicle->id,
					"vehicleName" => $vehicle->vehicle_name ?? "",
					"vehicleType" => $vehicle->vehicleType->vehicle_type ?? "",
					"vehicleImage" => $vehicle->vehicle_icon ? asset('storage/' . $vehicle->vehicle_icon) : "",
				];
			}
			$coupon = Coupons::select("id", "coupon_code", "coupon_type", "coupon_amount_or_percentage", "coupon_cap_limit", "coupon_min_order_amount", "coupon_start_date", "coupon_end_date")
				->where("is_delete", 0)
				->where("id", $r->couponId)
				->first();
			if (!empty($coupon)) {
				$couponuses = Couponuses::select("couponuses_decided_exisiting_limit")
					->where("couponuses_coupon_id", $r->couponId)
					->where("couponuses_customer_id", $r->customerId)
					->first();
				if (!empty($couponuses)) {
					$userLimit = $couponuses->couponuses_decided_exisiting_limit;
					if ($userLimit < $coupon->coupon_cap_limit) {
						$nextLimit = $userLimit + 1;
					}
					return response()->json(['status' => 300, 'message' => 'Coupon already used'], 200);
				}
				$couponDetails = [
					"id" => $coupon->id,
					"couponCode" => $coupon->coupon_code ?? "",
					"couponType" => $coupon->coupon_type,
					"couponAmount" => $coupon->coupon_amount_or_percentage,
					"nextLimit" => $nextLimit
				];
			}

			if ($r->couponId != "") {
				$calculate_amount = $this->calculate_amount($r->couponId, $r->amount);
				if ($calculate_amount["status"] == 300) {
					return response()->json(['status' => 300, 'message' => $calculate_amount["message"]], 200);
				}
			} else {
				$calculate_amount = $this->calculate_amount("", $r->amount);
				if ($calculate_amount["status"] == 300) {
					return response()->json(['status' => 300, 'message' => $calculate_amount["message"]], 200);
				}
			}

			$stopAddresses = [];
			if (isset($r->stopAddress) && is_array($r->stopAddress)) {
				foreach ($r->stopAddress as $key => $stopAddress) {
					$stopAddresses[] = [
						'name' => $r->stopAddressName[$key] ?? null,
						'type' => $r->stopAddressType[$key] ?? null,
						'address' => $stopAddress,
						'latitude' => $r->stopAddressLatitude[$key] ?? null,
						'longitude' => $r->stopAddressLongitude[$key] ?? null,
					];
				}
			}

			$resultArray = [
				"customerId" => $r->customerId,
				'sourceLatitude' => $r->sourceLatitude,
				'sourceLongitude' => $r->sourceLongitude,
				'sourceAddress' => $r->sourceAddress,
				'sourceMobileNumber' => $r->sourceMobileNumber,
				'destinationLatitude' => $r->destinationLatitude,
				'destinationLongitude' => $r->destinationLongitude,
				'destinationAddress' => $r->destinationAddress,
				'destinationName' => $r->destinationName,
				'destinationType' => $r->destinationType,
				'destinationMobileNumber' => $r->destinationMobileNumber,
				"stopAddresses" => $stopAddresses,
				"goodsTypeId" => $r->goodsTypeId,
				"goodsTypeName" => $goodsTypeName,
				"couponDetails" => $couponDetails,
				"vehicleDetails" => $vehicleDetails,
				"tripFairAmount" => $calculate_amount["tripFairAmount"],
				"netFairAmount" => $calculate_amount["netFairAmount"],
				"discountApplied" => $calculate_amount["discountApplied"],
				"totalAmount" => $calculate_amount["totalAmount"],
				"sgstRate" => $calculate_amount["sgstrate"],
				"cgstRate" => $calculate_amount["cgstrate"],
				"taxAmount" => $calculate_amount["taxAmount"],
			];
			return response()->json(['status' => 200, 'message' => 'Data found', "result" => $resultArray], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while place order.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	// function used for amount calculation
	public function calculate_amount($couponId, $amount)
	{
		$discount = 0;
		$totalTax = 0;
		$totalAmount = 0;
		$netAmount = $amount; // Initialize netAmount with original amount
		$sgstRate = 0; // 9%
		$cgstRate = 0; // 9%

		if ($couponId != "") {
			$coupon = Coupons::select("coupon_code", "coupon_type", "coupon_amount_or_percentage", "coupon_cap_limit", "coupon_min_order_amount", "coupon_start_date", "coupon_end_date")
				->where("is_delete", 0)
				->where("id", $couponId)
				->where('coupon_start_date', '<=', Carbon::now())
				->where('coupon_end_date', '>=', Carbon::now())
				->first();

			if (empty($coupon)) {
				return ["status" => 300, "message" => "Coupon is invalid."];
			}

			if ($amount < $coupon->coupon_min_order_amount) {
				return ["status" => 300, "message" => "Amount does not meet the minimum order requirement for this coupon."];
			}

			if ($coupon->coupon_type === 'flat') {
				$discount = min($coupon->coupon_amount_or_percentage, $amount);
			} else if ($coupon->coupon_type === 'percent') {
				$discount = $amount * ($coupon->coupon_amount_or_percentage / 100);
				// Apply cap limit if exists
				if ($coupon->coupon_cap_limit > 0 && $discount > $coupon->coupon_cap_limit) {
					$discount = $coupon->coupon_cap_limit;
				}
			}

			// Calculate amounts after discount
			$netAmount = $amount - $discount;

			// Calculate taxes (18% total - 9% SGST + 9% CGST)
			$sgstAmount = $netAmount * ($sgstRate / 100);
			$cgstAmount = $netAmount * ($cgstRate / 100);
			$totalTax = $sgstAmount + $cgstAmount;
			$totalAmount = $netAmount + $totalTax;
		} else {
			// Calculate taxes when no coupon is applied
			$sgstAmount = $netAmount * ($sgstRate / 100);
			$cgstAmount = $netAmount * ($cgstRate / 100);
			$totalTax = $sgstAmount + $cgstAmount;
			$totalAmount = $netAmount + $totalTax;
		}

		return [
			"status" => 200,
			"tripFairAmount" => number_format($amount, 2, '.', ''),
			"netFairAmount" => number_format($netAmount, 2, '.', ''),
			"discountApplied" => number_format($discount, 2, '.', ''),
			"sgstrate" => $sgstRate,
			"cgstrate" => $cgstRate,
			"taxAmount" => number_format($totalTax, 2, '.', ''),
			"totalAmount" => number_format($totalAmount, 2, '.', ''),
		];
	}

	//coupon uses details add or edit based on userlimit
	public function use_user_coupon($actionType, $couponId, $customerId, $tripId, $decidedExisitingLimit)
	{
		if ($actionType = "add") {
			$couponuses = new Couponuses;
			$couponuses->couponuses_customer_id = $customerId;
			$couponuses->couponuses_trip_id = $tripId;
			$couponuses->couponuses_coupon_id = $couponId;
			$couponuses->couponuses_used_date = date("Y-m-d h:i:s");
			$couponuses->couponuses_decided_exisiting_limit = $decidedExisitingLimit;
			$couponuses->save();
			return true;

			LogHelper::logSuccess('The coupon added for trip.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $couponuses->id); // Log success message
		} else {
			$couponuses = Couponuses::where("couponuses_trip_id", $tripId)
				->where("couponuses_coupon_id", $couponId)
				->where("couponuses_customer_id", $customerId)
				->first();

			if ($couponuses) {
				$couponuses->couponuses_used_date = date("Y-m-d h:i:s");
				$couponuses->couponuses_decided_exisiting_limit = $decidedExisitingLimit;
				$couponuses->save();
				return true;
				LogHelper::logSuccess('The coupon updated for trip.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $couponuses->id); // Log success message
			} else {
				LogHelper::logError('An error occurred while the update coupon in trip added', 'Coupon not updated.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, ''); // Log error message
				return false;
			}
		}
	}

	// book trip 
	public function book_trip(Request $r)
	{
		try {
			$input = $r->all();
			// Validate the input
			$validator = Validator::make($input, [
				'customerId' => 'required|integer',
				'sourceLatitude' => 'required',
				'sourceLongitude' => 'required',
				'sourceAddress' => 'required',
				'sourceType' => 'required',
				'sourceMobileNumber' => 'required',
				'destinationLatitude' => 'required',
				'destinationLongitude' => 'required',
				'destinationAddress' => 'required',
				'destinationMobileNumber' => 'required',
				'destinationName' => 'required',
				'destinationType' => 'required',
				'vehicleId' => 'required',
				'goodsTypeId' => 'required',
				'couponId' => 'nullable|integer',
				'decidedExisitingLimit' => 'nullable',
				'vehicleTime' => 'required',
				'vehicleDistance' => 'required',
				'tripFairAmount' => 'required',
				'netFairAmount' => 'required',
				'discountApplied' => 'nullable',
				'totalAmount' => 'required',
				'stopAddress' => 'nullable|array',
				'stopAddressLatitude' => 'nullable|array',
				'stopAddressLongitude' => 'nullable|array',
				'stopAddressName' => 'nullable|array',
				'stopAddressType' => 'nullable|array',
				'paymentOption' => 'required|in:online,cod',
				'sgstRate' => 'required',
				'cgstRate' => "required",
				'taxAmount' => "required",
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			//coupon checking if session is out or customer book trip after fews days later  
			if ($r->couponId != "") {
				$calculate_amount = $this->calculate_amount($r->couponId, $r->amount);
			} else {
				$calculate_amount = $this->calculate_amount("", $r->amount);
			}

			$vehicleDetails = Vehicle::where("id", $r->vehicleId)->first();

			//saving trip details
			$trip = new Trip;
			$trip->trip_customer_id = $r->customerId;
			$trip->trip_vehicle_id = $r->vehicleId;
			$trip->trip_goods_type_id = $r->goodsTypeId;
			$trip->trip_coupon_id = $r->couponId;
			$trip->trip_distance = $r->vehicleDistance;
			$trip->trip_vehicle_fix_distance = $vehicleDetails->vehicle_fixed_km;
			$trip->trip_vehicle_fix_amount = $vehicleDetails->vehicle_fixed_km_delivery_charge;
			$trip->trip_vehicle_per_km_amount = $vehicleDetails->vehicle_per_km_delivery_charge;
			$trip->trip_fair_amount = $r->tripFairAmount;
			$trip->trip_netfair_amount = $r->netFairAmount;
			$trip->trip_discount = $r->discountApplied;
			$trip->trip_total_amount = $r->totalAmount;
			$trip->trip_sgst_rate = $r->sgstRate;
			$trip->trip_cgst_rate = $r->cgstRate;
			$trip->trip_tax_amount = $r->taxAmount;
			$trip->trip_status = "pending";
			$trip->trip_payment_status = "pending";
			$trip->trip_payment_mode = $r->paymentOption;
			$trip->trip_unique_id = "TRP" . (int)(microtime(true) * 1000);
			if ($r->paymentOption == "online") {
				$trip->trip_payment_id = "TX" . (int)(microtime(true) * 1000);
			}
			$result = $trip->save();
			if ($result) {

				//phonepe payment gateway 

				if ($r->paymentOption == "online") {

					$phonepe = new Phonepe;
					$phonepeResult = $phonepe->create_phonepe_order($r->totalAmount, $trip->trip_payment_id, "customer-trip-book", $trip->id);
					//print_r($phonepeResult);
					if (isset($phonepeResult["success"])) {
						if ($phonepeResult["success"] == true) {
							$paymentData = [
								"trip_payment_response" => $phonepeResult,
								"trip_payment_order_id" => $phonepeResult["orderId"],
							];
							$tripUpdate = $trip->update($paymentData);
						} else {
							return response()->json(['status' => 300, 'message' => 'The trip booking has failed.'], 200);
						}
					}
				} else {
					$phonepeResult = new \stdClass();
				}

				//save trip status in trip status

				$tripStatus = new TripStatus;
				$tripStatus->trip_id = $trip->id;
				$tripStatus->trip_status_title = "pending";
				$tripStatus->trip_status_description = "Trip is booked by " . $r->customerId . " for vehicle " . $r->vehicleId;
				$tripStatus->trip_status_reason = "Trip is booked.";
				$tripStatus->trip_status_short = "pending";
				$tripStatus->trip_action_type = "customer";
				$tripStatus->save();

				if ($r->paymentOption == "online") {
					//$trip->trip_unique_id="TXN".rand(0000,9999);
				}
				if ($r->couponId != "") {
					//saving coupon details
					if ((isset($r->decidedExisitingLimit)) && $r->decidedExisitingLimit > 1) {
						$this->use_user_coupon("update", $r->couponId, $r->customerId, $trip->id, $r->decidedExisitingLimit);
					} else {
						$this->use_user_coupon("add", $r->couponId, $r->customerId, $trip->id, $r->decidedExisitingLimit);
					}
				}

				$customerSourceAddress = Customeraddress::where("customeraddresses_address", $r->sourceAddress)
					->where("customeraddresses_mobile", $r->sourceMobileNumber)
					->where("customeraddresses_type", "source")
					->where("is_delete", 0)
					->first();
				if (empty($customerSourceAddress)) {

					//saving source address details
					$sourceaddress = new Customeraddress;
					$sourceaddress->customeraddresses_customer_id = $r->customerId;
					$sourceaddress->customeraddresses_address = $r->sourceAddress;
					$sourceaddress->customeraddresses_mobile = $r->sourceMobileNumber;
					$sourceaddress->customeraddresses_type = "source";
					$sourceaddress->customeraddresses_latitude = $r->sourceLatitude;
					$sourceaddress->customeraddresses_longitude = $r->sourceLongitude;
					$sourceaddress->customeraddresses_trip_id = $trip->id;
					$sourceaddress->customeraddresses_location_type = $r->sourceType;
					$sourceaddress->save();

					$trip->trip_source_address_id = $sourceaddress->id;
					$trip->save();
				} else {
					$trip->trip_source_address_id = $customerSourceAddress->id;
					$trip->save();
				}

				$customerDestinationAddress = Customeraddress::where("customeraddresses_address", $r->destinationAddress)
					->where("customeraddresses_mobile", $r->destinationMobileNumber)
					->where("customeraddresses_name", $r->destinationName)
					->where("customeraddresses_type", "destination")
					->where("is_delete", 0)
					->first();
				if (empty($customerDestinationAddress)) {
					//saving destination address details

					$destinationaddress = new Customeraddress;
					$destinationaddress->customeraddresses_customer_id = $r->customerId;
					$destinationaddress->customeraddresses_address = $r->destinationAddress;
					$destinationaddress->customeraddresses_mobile = $r->destinationMobileNumber;
					$destinationaddress->customeraddresses_type = "destination";
					$destinationaddress->customeraddresses_latitude = $r->destinationLatitude;
					$destinationaddress->customeraddresses_longitude = $r->destinationLongitude;
					$destinationaddress->customeraddresses_trip_id = $trip->id;
					$destinationaddress->customeraddresses_name = $r->destinationName;
					$destinationaddress->customeraddresses_location_type = $r->destinationType;
					$destinationaddress->save();

					$trip->trip_destination_address_id = $destinationaddress->id;
					$trip->save();
				} else {
					$trip->trip_destination_address_id = $customerDestinationAddress->id;
					$trip->save();
				}

				//saving stop address details

				if (isset($r->stopAddress) && is_array($r->stopAddress)) {
					foreach ($r->stopAddress as $key => $stopAddress) {
						$stopAddressLatitude = $r->stopAddressLatitude[$key] ?? null;
						$stopAddressLongitude = $r->stopAddressLongitude[$key] ?? null;
						$stopAddressName = $r->stopAddressName[$key] ?? null;
						$stopAddressType = $r->stopAddressType[$key] ?? null;
						if ($stopAddress && $stopAddressLatitude && $stopAddressLongitude) {
							$stopAddressRecord = new Customeraddress;
							$stopAddressRecord->customeraddresses_customer_id = $r->customerId;
							$stopAddressRecord->customeraddresses_address = $stopAddress;
							$stopAddressRecord->customeraddresses_type = "stop";
							$stopAddressRecord->customeraddresses_latitude = $stopAddressLatitude;
							$stopAddressRecord->customeraddresses_longitude = $stopAddressLongitude;
							$stopAddressRecord->customeraddresses_trip_id = $trip->id;
							$stopAddressRecord->customeraddresses_name = $stopAddressName;
							$stopAddressRecord->customeraddresses_location_type = $stopAddressType;
							$stopAddressRecord->save();
						}
					}
				}


				/*$sourceLatitude=$r->sourceLatitude;
				$sourceLongitude=$r->sourceLongitude;
				$drivers = Driver::with('vehicleDetails')
						->select('drivers.*', DB::raw("
							(6371 * acos(cos(radians(?)) * cos(radians(driver_latitude)) * cos(radians(driver_longitude) - radians(?)) + sin(radians(?)) * sin(radians(driver_latitude)))) AS distance
						"))
						->addBinding([$sourceLatitude, $sourceLongitude, $sourceLatitude], 'select')
						->where("driver_document_verification_status", 1)
						->where("driver_vehicle_verification_status", 1)
						->where("driver_vehicle_document_verification_status", 1)
						->where("driver_training_video_verification_status", 1)
						->whereHas('vehicleDetails', function ($query) use ($r) {
							$query->where('vehicle_id', $r->vehicleId);
						})
						->having('distance', '<=', 10)
						->get();

				if($drivers && count($drivers)){
					foreach($drivers as $item){
						
					}
				}	*/

				/*$customer=Customer::select("customer_firebase_token")
				        ->where("id",$r->customerId)
						->first();
                if(!empty($customer)){
					if(!empty($customer->customer_firebase_token)){
						$statusMessage = "Trip booked successfully.";
			            $title = "Trip Booked";
						
						$DeviceIdsArr[] = $customer->customer_firebase_token;
						$dataArr = array();
						$dataArr['device_id'] = $DeviceIdsArr;
						$dataArr['message'] = $statusMessage;
						$dataArr['title'] = $title;					
						$notification['device_id'] = $DeviceIdsArr;
						$notification['message'] = $statusMessage;
						$notification['title'] = $title;		  			
						$noti = new Notificationlibv_3;
						$result = $noti->sendNotification($dataArr, $notification); 
						Log::info("Trip booked notification result", ['result' => $result]); 
					}					
				}*/

				//send otp for customer

				$otpNumber = $this->generateOTP($r->sourceMobileNumber, "pickup", $trip->id);

				// Check if OTP generation is successful
				if ($otpNumber != false) {
					// Optional: Skip sending OTP for specific number
					if ($r->sourceMobileNumber != "8482940592") {
						//$smssending = new SmsSending;
						//$smsresult = $smssending->sendOtp($r->mobilenumber, $otpNumber);  // OTP sending logic (currently commented out)
					}

					//success log
					LogHelper::logSuccess('The pickup otp resend successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);
				}

				LogHelper::logSuccess('The trip booked successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $trip->id); // Log success message

				// Prepare the result response
				$resultData = [
					'customerId' => $r->customerId,
					//'paymentArray'=>$paymentArray,
					'phonepe' => $phonepeResult,
					'tripUniqueId' => $trip->trip_unique_id,
					'merchantOrderId' => $trip->trip_payment_id ?? "",
					'orderId' => $trip->id,
					'sourceGeo' => [
						'lat' => $r->sourceLatitude,
						'lng' => $r->sourceLongitude
					],
					'destinationGeo' => [
						'lat' => $r->destinationLatitude,
						'lng' => $r->destinationLongitude
					]
				];

				return response()->json([
					'status' => 200,
					'message' => 'The trip has been booked successfully.',
					'result' => $resultData
				], 200);
			}

			LogHelper::logError('An error occurred while the trip booked ', 'The trip booking has failed..',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, ''); // Log error if The trip booking has failed.
			return response()->json(['status' => 300, 'message' => 'The trip booking has failed.'], 200); // Return error response if The trip booking has failed.
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while book trip.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}



	//source address list
	public function source_address(Request $request)
	{
		try {
			$input = $request->all();

			$validator = Validator::make($input, [
				'customerId' => 'required',
				'search' => 'nullable|string',
				'page' => 'nullable|integer|min:1',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			$searchTerm = $request->input('search', '');
			$perPage = 10;
			$page = $request->input('page', 1);

			$query = Customeraddress::leftJoin("customers", "customers.id", "=", "customeraddresses.customeraddresses_customer_id")
				->select(
					DB::raw("IFNULL(customeraddresses.id, '') as address_id"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_location_type, '') as customeraddresses_location_type"),
					DB::raw("IFNULL(customers.customer_last_name, '') as customer_last_name"),
					DB::raw("IFNULL(customers.customer_first_name, '') as customer_first_name"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_address, '') as customeraddresses_address"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_mobile, '') as customeraddresses_mobile"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_type, '') as customeraddresses_type"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_latitude, '') as customeraddresses_latitude"),
					DB::raw("IFNULL(customeraddresses.customeraddresses_longitude, '') as customeraddresses_longitude")
				)
				->where("customeraddresses.is_delete", 0)
				->where("customeraddresses.customeraddresses_customer_id", $request->customerId)
				->where("customeraddresses.customeraddresses_type", "source");

			if (!empty($searchTerm)) {
				$query->where(function ($query) use ($searchTerm) {
					$query->orWhere('customeraddresses.customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customers.customer_first_name', 'like', "%{$searchTerm}%")
						->orWhere('customers.customer_last_name', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses.customeraddresses_location_type', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses.customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses.customeraddresses_latitude', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses.customeraddresses_longitude', 'like', "%{$searchTerm}%");
				});
			}

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			$result = $query->orderBy('customeraddresses.id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			return response()->json([
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"result" => $result,
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while getting source address.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}



	//destination address list
	public function destination_address(Request $request)
	{
		try {
			$input = $request->all();
			// Validate the input
			$validator = Validator::make($input, [
				'customerId' => 'required',
				'search' => 'nullable|string',
				'page' => 'nullable|integer|min:1',

			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Get input values
			$searchTerm = $request->input('search', '');
			$perPage = 10; // Fixed perPage to 10
			$page = $request->input('page', 1); // Default to page 1

			$query = Customeraddress::select("customeraddresses_location_type", "customeraddresses_name", "customeraddresses_address", "customeraddresses_mobile", "customeraddresses_type", "customeraddresses_latitude", "customeraddresses_longitude")
				->where("customeraddresses.is_delete", 0)
				->where("customeraddresses.customeraddresses_customer_id", $request->customerId)
				->where("customeraddresses.customeraddresses_type", "destination");
			if (!empty($searchTerm)) {
				$query->where(function ($query) use ($searchTerm) {
					$query->orWhere('customeraddresses_address', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_name', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_location_type', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_mobile', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_latitude', 'like', "%{$searchTerm}%")
						->orWhere('customeraddresses_longitude', 'like', "%{$searchTerm}%");
				});
			}

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch results
			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			return response()->json([
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"result" => $result,
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while getting destination address.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}


	public function update_customer_address(Request $r)
	{
		try {
			$input = $r->all();

			// Validate the input
			$validator = Validator::make($input, [
				'addressId' => 'required',
				'address' => 'required|string',
				'name' => 'nullable|string',
				'location_type' => 'required|string',
				'mobile' => 'required|string',
				'latitude' => 'required|numeric',
				'longitude' => 'required|numeric',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Fetch the customer address to ensure it exists
			$address = Customeraddress::find($r->addressId);

			if (!$address) {
				return response()->json(['status' => 300, 'message' => 'Address not found.'], 200);
			}

			// Update the customer address fields
			$address->customeraddresses_address = $r->address;
			$address->customeraddresses_name = $r->name;
			$address->customeraddresses_location_type = $r->location_type;
			$address->customeraddresses_mobile = $r->mobile;
			$address->customeraddresses_latitude = $r->latitude;
			$address->customeraddresses_longitude = $r->longitude;

			// Save the updated address
			$address->save();

			// Return success response
			return response()->json([
				"status" => 200,
				"message" => "Customer address updated successfully",
				"result" => $address
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while updating customer address.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}



	public function delete_customer_address(Request $r)
	{
		try {
			$input = $r->all();

			// Validate the input
			$validator = Validator::make($input, [
				'addressId' => 'required',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Fetch the customer address to ensure it exists
			$address = Customeraddress::find($r->addressId);

			if (!$address) {
				return response()->json(['status' => 300, 'message' => 'Address not found.'], 200);
			}

			// Update the customer address fields
			$address->is_active = 0;
			$address->is_delete = 1;

			// Save the updated address
			$address->save();

			// Return success response
			return response()->json([
				"message" => "Customer address deleted successfully"
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while deleting customer address.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	public function edit_customer_address(Request $r)
	{
		try {
			$input = $r->all();

			// Validate the input
			$validator = Validator::make($input, [
				'addressId' => 'required',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			// Fetch the customer address to ensure it exists
			$address = Customeraddress::find($r->addressId);

			if (!$address) {
				return response()->json(['status' => 300, 'message' => 'Address not found.'], 200);
			}

			// Return success response
			return response()->json([
				"status" => 200,
				"message" => "Customer address found successfully",
				"result" => $address
			], 200);
		} catch (\Exception $e) {
			// Log the error and return a generic error response
			LogHelper::logError('An error occurred while editing customer address.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

	//seemashelar@neosao
	//get all services with icon path
	public function get_all_services(Request $request)
	{
		try {
			$services = Service::select(
				'id',
				'service_name',
				DB::raw("CONCAT('" . asset('storage/') . "/', service_icon) as service_icon"),
				'service_description'
			)
				->where('is_delete', 0)
				->where('is_active', 1)
				->orderBy('service_name', 'ASC')
				->get();

			return response()->json([
				"status" => 200,
				"message" => $services->isEmpty() ? 'No Data Found' : 'Data Found',
				"result" => $services,
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while fetching services.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");

			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}

	public function get_trip_history(Request $r)
	{
		try {
			$input = $r->all();

			$validator = Validator::make($input, [
				'customerId' => 'required',
				'page' => 'nullable|integer|min:1',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$perPage = 10;
			$page = $r->input('page', 1);
			$query = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'sourceAddress', 'destinationAddress')
				->where('trip_customer_id', '=', $r->customerId);

			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			$result = $query->orderBy('id', 'DESC')
				->skip($offset)
				->limit($perPage)
				->get();

			$tripArray = [];

			if ($result && count($result) > 0) {
				foreach ($result as $item) {

					$sourceAddress = [
						"address" => $item->sourceAddress->customeraddresses_address ?? "",
						"mobile" => $item->sourceAddress->customeraddresses_mobile ?? "",
						"type" => $item->sourceAddress->customeraddresses_type ?? "",
						"name" => $item->customer?->customer_first_name . ' ' . $item->customer?->customer_last_name ?? "",
						"locationType" => $item->sourceAddress->customeraddresses_location_type ?? "",
						"latitude" => $item->sourceAddress->customeraddresses_latitude ?? "",
						"longitude" => $item->sourceAddress->customeraddresses_longitude ?? "",

					];

					$destinationAddress = [
						"address" => $item->destinationAddress->customeraddresses_address ?? "",
						"mobile" => $item->destinationAddress->customeraddresses_mobile ?? "",
						"type" => $item->destinationAddress->customeraddresses_type ?? "",
						"name" => $item->destinationAddress->customeraddresses_name ?? "",
						"locationType" => $item->destinationAddress->customeraddresses_location_type ?? "",
						"latitude" => $item->destinationAddress->customeraddresses_latitude ?? "",
						"longitude" => $item->destinationAddress->customeraddresses_longitude ?? "",
					];


					$customer = [
						"customerId" => $item->customer?->id ?? "",
						"customerFirstName" => $item->customer?->customer_first_name ?? "",
						"customerLastName" => $item->customer?->customer_last_name ?? "",
					];

					$vehicle = [
						"vehicleId" => $item->vehicle->id ?? "",
						"vehicleName" => $item->vehicle->vehicle_name ?? "",
					];

					$finalResult = [
						"tripId" => $item->id,
						"tripUniqueId" => $item->trip_unique_id,
						"tripTotalAmount" => $item->trip_total_amount,
						"sgstRate" => $item->trip_sgst_rate,
						"cgstRate" => $item->trip_cgst_rate,
						"taxAmount" => $item->trip_tax_amount,
						"tripStatus" => $item->trip_status,
						"vehicleType" => $item->vehicle?->vehicleType?->vehicle_type ?? '',
						"tripDate" => $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y h:i:s') : "",
						"customer" => $customer,
						"driverId" => $item->trip_driver_id ?? "",
						"vehicle" => $vehicle,
						"customerSourceAddress" => $sourceAddress,
						"customerDestinationAddress" => $destinationAddress
					];

					$tripArray[] = $finalResult;
				}
			}

			return response()->json([
				"message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
				"currentPage" => intval($page),
				"totalPages" => $totalPages,
				"totalCount" => $totalCount,
				"result" => $tripArray
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while fetching trips.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	public function trip_details(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'tripId' => 'required',
				'customerId' => 'required',
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$tripDetails = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'sourceAddress', 'destinationAddress')
				->where('id', '=', $r->tripId)
				->first();

			if (!empty($tripDetails)) {
				$customerRatingDetails = new \stdClass(); // Default to an empty object
				$driverRatingDetails = new \stdClass();
				$vehicle = new \stdClass();

				$averageRating = Rating::where("rating_driver_id", $tripDetails->trip_driver_id)
					->where("rating_given_by", "customer")
					->avg("rating_value");

				$rating = Rating::where("rating_customer_id", $r->customerId)
					->where("rating_trip_id", $r->tripId)
					->where("rating_given_by", "customer")
					->first();

				if (!empty($rating)) {
					$customerRatingDetails->ratingId = $rating->id;
					$customerRatingDetails->ratingValue = $rating->rating_value;
				}


				$driverrating = Rating::where("rating_driver_id", $tripDetails->trip_driver_id)
					->where("rating_trip_id", $r->tripId)
					->where("rating_given_by", "driver")
					->first();

				if (!empty($driverrating)) {
					$driverRatingDetails->ratingId = $driverrating->id;
					$driverRatingDetails->ratingValue = $driverrating->rating_value;
				}

				if (!empty($tripDetails->trip_coupon_id)) {
					$couponDetails = [
						"tripCouponId" => $tripDetails->coupon->id,
						"tripCouponType" => $tripDetails->coupon->coupon_type,
						"tripCouponAmountOrPercentage" => $tripDetails->coupon->coupon_amount_or_percentage,
					];
				}

				$couponDetails = new \stdClass();
				if (!empty($tripDetails->trip_coupon_id)) {
					$couponDetails = [
						"tripCouponId" => $tripDetails->coupon->id,
						"tripCouponType" => $tripDetails->coupon->coupon_type,
						"tripCouponAmountOrPercentage" => $tripDetails->coupon->coupon_amount_or_percentage,
					];
				}

				$sourceAddress = [
					"address" => $tripDetails->sourceAddress->customeraddresses_address ?? "",
					"mobile" => $tripDetails->sourceAddress->customeraddresses_mobile ?? "",
					"type" => $tripDetails->sourceAddress->customeraddresses_type ?? "",
					"name" => $tripDetails->customer->customer_first_name . ' ' . $tripDetails->customer->customer_last_name ?? "",
					"locationType" => $tripDetails->sourceAddress->customeraddresses_location_type ?? "",
					"latitude" => $tripDetails->sourceAddress->customeraddresses_latitude ?? "",
					"longitude" => $tripDetails->sourceAddress->customeraddresses_longitude ?? "",

				];

				$destinationAddress = [
					"address" => $tripDetails->destinationAddress->customeraddresses_address ?? "",
					"mobile" => $tripDetails->destinationAddress->customeraddresses_mobile ?? "",
					"type" => $tripDetails->destinationAddress->customeraddresses_type ?? "",
					"name" => $tripDetails->destinationAddress->customeraddresses_name ?? "",
					"locationType" => $tripDetails->destinationAddress->customeraddresses_location_type ?? "",
					"latitude" => $tripDetails->destinationAddress->customeraddresses_latitude ?? "",
					"longitude" => $tripDetails->destinationAddress->customeraddresses_longitude ?? "",
				];

				$customer = [
					"customerId" => $tripDetails->customer->id ?? "",
					"customerFirstName" => $tripDetails->customer->customer_first_name ?? "",
					"customerLastName" => $tripDetails->customer->customer_last_name ?? "",
				];


				$vehicleDetails = Driver::with('vehicleDetails.vehicle')
					->where("id", $tripDetails->trip_driver_id)
					->first();
				if (!empty($vehicleDetails)) {
					$vehicle = [
						"vehicleDriverId" => $tripDetails->trip_driver_id,
						"vehicleDriverName" => $vehicleDetails->driver_first_name . " " . $vehicleDetails->driver_last_name,
						"vehicleDriverMobileNumber" => $vehicleDetails->driver_phone ?? "",
						"vehicleNumber" => $vehicleDetails->vehicleDetails->vehicle_number ?? '',

						"vehicleName" => $vehicleDetails->vehicleDetails->vehicle->vehicle_name ?? '',
						"vehicleImage" => optional(optional($vehicleDetails)->vehicleDetails)->vehicle_icon
							? asset('storage/' . optional(optional($vehicleDetails)->vehicleDetails)->vehicle_icon)
							: "",
					];
				}

				$data = [
					"tripId" => $tripDetails->id,
					"driverId" => $tripDetails->trip_driver_id ?? "",
					"tripStatus" => $tripDetails->trip_status,
					"tripFairAmount" => $tripDetails->trip_fair_amount,
					"tripNetfairAmount" => $tripDetails->trip_netfair_amount,
					"tripDiscount" => $tripDetails->trip_discount,
					"tripTotalAmount" => $tripDetails->trip_total_amount,
					"sgstRate" => $tripDetails->trip_sgst_rate,
					"cgstRate" => $tripDetails->trip_cgst_rate,
					"taxAmount" => $tripDetails->trip_tax_amount,
					"tripDate" => $tripDetails->created_at ? Carbon::parse($tripDetails->created_at)->format('d-m-Y h:i:s') : "",
					"tripUniqueId" => $tripDetails->trip_unique_id,
					"customerSourceAddress" => $sourceAddress,
					"customerDestinationAddress" => $destinationAddress,
					"customer" => $customer,
					"vehicleType" => $tripDetails->vehicle?->vehicleType?->vehicle_type ?? '',
					"vehicle" => $vehicle,
					"paymentOption" => $tripDetails->trip_payment_mode,
					"paymentStatus" => $tripDetails->trip_payment_status,
					"couponDetails" => $couponDetails,
					"customerRatingDetails" => $customerRatingDetails,
					"driverratingDetails" => $driverRatingDetails,
					"averageRating" => number_format($averageRating ?? 0, 1, '.', '') ?? "",
				];

				return response()->json(['status' => 200, 'message' => 'Data found.', 'result' => $data], 200);
			}

			return response()->json(['status' => 300, 'message' => 'Data not found.'], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while fetching trips details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	public function trip_driver_rating(Request $r)
	{
		try {
			$input = $r->all();
			// Validate the input
			$validator = Validator::make($input, [
				'ratingValue' => 'required',
				'customerId' => 'required',
				'tripId' => 'required',
				'driverId' => 'required',
				'description' => 'nullable',
			]);

			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$rating = new Rating;
			$rating->rating_customer_id = $r->customerId;
			$rating->rating_trip_id = $r->tripId;
			$rating->rating_value = $r->ratingValue;
			$rating->rating_description = $r->description;
			$rating->rating_driver_id = $r->driverId;
			$rating->rating_given_by = "customer";
			$rating->save();

			return response()->json(['status' => 200, 'message' => 'Rating has been given successfully.'], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while star rating.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	public function customer_wallet_add(Request $r)
	{
		try {
			$input = $r->all();

			$validator = Validator::make($input, [
				'customerId' => 'required',
				'amount' => 'required|numeric|min:0',
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$customer = Customer::find($input['customerId']);

			if (!$customer) {
				return response()->json([
					'status' => 300,
					'message' => 'Customer not found'
				], 200);
			}

			//$customer->customer_wallet_balance += $input['amount'];
			//$customer->save();

			/*LogHelper::logSuccess(
				"Customer wallet updated successfully. Amount added: {$input['amount']}",
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$customer->id
			);*/
			$walletTransaction = new CustomerWalletTransaction;
			$walletTransaction->customer_id = $r->customerId;
			$walletTransaction->type = "deposit";
			$walletTransaction->message = "Rs. " . $input['amount'] . " amount is created in your wallet.";
			$walletTransaction->amount = $input['amount'];
			$walletTransaction->status = "pending";
			$walletTransaction->payment_mode = "online";
			$walletTransaction->payment_status = "pending";
			$walletTransaction->payment_id = "TX" . (int)(microtime(true) * 1000);
			$walletTransaction->save();

			LogHelper::logSuccess(
				"Customer wallet transaction. Amount added: {$input['amount']}",
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$customer->id
			);

			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->create_phonepe_order($r->amount, $walletTransaction->payment_id, 'customer-wallet-add', $input['customerId']);

			if (isset($phonepeResult["success"])) {
				if ($phonepeResult["success"] == true) {

					$walletTransactions = CustomerWalletTransaction::find($walletTransaction->id);
					$paymentData = [
						"payment_response" => $phonepeResult,
						"payment_order_id" => $phonepeResult["orderId"],
					];
					$result = $walletTransactions->update($paymentData);


					$resultData = [
						'phonepe' => $phonepeResult,
						'merchantOrderId' => $walletTransactions->payment_id
					];

					return response()->json([
						'status' => 200,
						'message' => 'Money has been added to the wallet successfully.',
						'result' => $resultData
					], 200);
				} else {
					return response()->json([
						'status' => 300,
						'message' => 'Failed to add money to the wallet. Please try again later.',
					], 200);
				}
			} else {
				return response()->json([
					'status' => 300,
					'message' => 'Failed to add money to the wallet. Please try again later.',
				], 200);
			}
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while updating customer wallet.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}


	public function check_order_status(Request $r)
	{

		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'customerId' => 'required|integer',
				'merchantOrderId' => 'required'
			], [
				'customerId.required' => 'The customer ID is required.',
				'customerId.integer' => 'The customer ID must be a valid integer.',
				'merchantOrderId.required' => 'The merchant order id is required.'
			]);

			// Check if validation fails
			if ($validator->fails()) {
				return response()->json([
					"status" => 500,
					"message" => $validator->errors()->first()
				], 200);
			}

			// Check if customer exists
			$customer = Customer::find($r->customerId);

			if (!$customer) {
				return response()->json([
					"status" => 300,
					"message" => "Customer not found."
				], 200);
			}

			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->check_phonepe_order_status($r->merchantOrderId);

			if ($phonepeResult['success']) {
				$status = strtoupper($phonepeResult['status']);
				$message = match (strtoupper($status)) {
					'COMPLETED' => 'Payment completed successfully.',
					'PENDING' => 'Payment is still pending.',
					'FAILED' => 'Payment failed.',
					'EXPIRED' => 'Payment has expired.',
					'REFUNDED' => 'Payment has been refunded.',
					default => 'Unknown payment status.',
				};

				/*$transaction = CustomerWalletTransaction::where("payment_id",$r->merchantOrderId)->first();					
				$paymentData=["payment_status"=>strtolower($status),"status"=>strtolower($status)];
				$transaction->update($paymentData);
				
				if($status=="COMPLETED"){
					$customer->customer_wallet_balance += $transaction->amount;
					$customer->save();
				}*/

				LogHelper::logSuccess('Customer wallet updated successfully', [
					'customer_id' => $r->customerId,
					'updated_wallet_amount' => $customer->customer_wallet_balance,
				], __FUNCTION__, basename(__FILE__), __LINE__);

				return response()->json([
					'status' => 200,
					'message' => $message,
					'result' => $phonepeResult['data'],
				]);
			} else {
				return response()->json([
					'status' => 300,
					'message' => $phonepeResult['message'] ?? 'Failed to fetch order status',
					'result' => $phonepeResult['error'] ?? [],
				], 200);
			}
		} catch (\Exception $e) {
			// Log error
			LogHelper::logError('An error occurred while check add money order status.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId ?? 'N/A');

			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong.'
			], 400);
		}
	}

	public function customer_wallet(Request $r)
	{
		try {
			$input = $r->all();

			$validator = Validator::make($input, [
				'customerId' => 'required|exists:customers,id'
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$customer = Customer::find($input['customerId']);

			if (!$customer) {
				return response()->json([
					'status' => 300,
					'message' => 'Customer not found'
				], 200);
			}

			return response()->json([
				'status' => 200,
				'message' => 'Customer Wallet Amount',
				'wallet' => $customer->customer_wallet_balance
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while updating customer wallet.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function customer_transaction_list(Request $r)
	{
		try {
			$input = $r->all();

			// Validate the input
			$validator = Validator::make($input, [
				'customerId' => 'required|exists:customers,id',
				'date' => 'nullable',
				'page' => 'nullable|integer|min:1', // Page validation
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			$customer = Customer::find($input['customerId']);

			if (!$customer) {
				return response()->json([
					'status' => 300,
					'message' => 'Customer not found'
				], 200);
			}

			// Get the input values
			$page = $r->input('page', 1); // Default to page 1 if no page is provided
			$perPage = 10; // Set perPage to 10 for pagination

			// Start building the query for transactions
			$query = CustomerWalletTransaction::where('customer_id', $input['customerId'])
				->where('is_delete', 0) // Assuming is_delete is a flag for soft delete
				->whereIn('status', ['success', 'completed']);
			// If a date is provided, filter by that date
			if (!empty($input['date'])) {
				$date = date('Y-m-d', strtotime(str_replace("/", "-", $input['date'])));
				$query->whereDate('created_at', '=', $date);
			}

			// Count total number of transactions for pagination
			$totalCount = $query->count();
			$totalPages = ceil($totalCount / $perPage);
			$page = $page > $totalPages ? max(1, $totalPages) : $page;
			$offset = ($page - 1) * $perPage;

			// Fetch paginated transaction results
			$transactionList = $query->orderBy("id", "DESC")->skip($offset)
				->take($perPage)
				->get();

			$transactionArray = [];
			if ($transactionList && count($transactionList) > 0) {
				foreach ($transactionList as $transaction) {
					$data = [
						"tripId" => strval($transaction->trip_id) ?? "",
						"customerId" => $transaction->customer_id,
						"type" => $transaction->type,
						"message" => $transaction->message,
						"amount" => $transaction->amount,
						"status" => $transaction->status,
						"transactionDate" => $transaction->created_at ? Carbon::parse($transaction->created_at)->format('d-m-Y h:i:s') : "",
					];
					array_push($transactionArray, $data);
				}

				return response()->json([
					"status" => 200,
					"message" => "Data found",
					"currentPage" => intval($page),
					"totalPages" => $totalPages,
					"totalCount" => $totalCount,
					"result" => $transactionArray,
				], 200);
			}

			return response()->json([
				"status" => 300,
				"message" => "Data not found"
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while fetching transaction list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function cancel_reason(Request $r)
	{
		try {
			$reasonArray = CustomerRejectionReason::where("is_delete", 0)
				->pluck('reason')
				->toArray();

			if ($reasonArray) {
				return response()->json([
					"status" => 200,
					"message" => "Data found",
					"result" => $reasonArray
				], 200);
			}
			return response()->json([
				"status" => 300,
				"message" => "Data not found"
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while fetching cancel reason list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function cancel_trip(Request $r)
	{
		try {
			$input = $r->all();

			$validator = Validator::make($input, [
				'customerId' => 'required|exists:customers,id',
				'tripId' => 'required',
				'cancelReason' => "required",
			]);


			// Check if validation fails
			if ($validator->fails()) {
				// Return error response with validation message
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$customer = Customer::find($input['customerId']);

			if (!$customer) {
				return response()->json([
					'status' => 300,
					'message' => 'Customer not found'
				], 200);
			}

			$trip = Trip::find($input['tripId']);
          
            if($trip->trip_status=="cancelled"){
               return response()->json(["status" => 300, "message" => "Trip is already cancelled."], 200);
            }
			$trip->trip_status = "cancelled";
			$trip->save();

			//save trip status in trip status				
			$tripStatus = new TripStatus;
			$tripStatus->trip_id = $trip->id;
			$tripStatus->trip_status_title = "cancelled";
			$tripStatus->trip_status_description = "Trip is cancelled by " . $r->customerId;
			$tripStatus->trip_status_reason = $r->cancelReason;
			$tripStatus->trip_status_short = "cancelled";
			$tripStatus->trip_action_type = "customer";
			$tripStatus->save();

			//get customer penalty
			$getCustomerPenalty = Setting::where("id", 5)->first();
			$commission = 0;
			if (!empty($getCustomerPenalty)) {
				$commission = $getCustomerPenalty->setting_value;
			}
			$customerPenalty = ($trip->trip_total_amount * $commission) / 100;

			//wallet transaction deduction
			$walletTransaction = new CustomerWalletTransaction;
			$walletTransaction->trip_id = $trip->id;
			$walletTransaction->customer_id = $r->customerId;
			$walletTransaction->type = "deduction";
			$walletTransaction->message = $customerPenalty . " is deducted against Trip unique ID " . $trip->trip_unique_id . " due to trip cancel.";
			$walletTransaction->amount = $customerPenalty;
			$walletTransaction->status = "success";
			$walletTransaction->save();

			LogHelper::logSuccess(
				"Customer wallet transaction. Amount deducted: {$commission}",
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$customer->id
			);

			$customer->customer_wallet_balance -= $commission;
			$customer->save();

			LogHelper::logSuccess(
				"Customer wallet updated successfully. Amount deducted: {$commission}",
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				$customer->id
			);

			return response()->json([
				'status' => 200,
				'message' => 'Trip cancel successfully'
			], 200);
		} catch (\Exception $e) {
			LogHelper::logError('An error occurred while customer cancel trip .', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function customer_delete(Request $r)
	{
		try {
			$input = $r->all();
			$validator = Validator::make($input, [
				'customerId' => 'required'
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			$data = [
				'is_active' => 0,
				'is_delete' => 1,
				'is_customer_delete' => 1,
			];
			$customer = Customer::where("id", $r->customerId)->first();
			$result = $customer->update($data);
			if ($result == true) {

				LogHelper::logSuccess(
					"Customer delete successfully.",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$customer->id
				);
				return response()->json(["status" => 200, "message" => "Deleted Successfully."], 200);
			} else {
				return response()->json(["status" => 300, "message" => " Failed to delete customer."], 200);
			}
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while customer delete .', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function update_firebase_token(Request $r)
	{
		try {
			$data = [];
			$input = $r->all();
			$validator = Validator::make($input, [
				'customerId' => 'required',
				'firebaseId' => 'required',
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$customerdata = [
				'customer_firebase_token' => $r->firebaseId,
			];
			$result = Customer::where('id', $r->customerId)->update($customerdata);

			if ($result == true) {

				LogHelper::logSuccess(
					"Customer firebase update successfully.",
					__FUNCTION__,
					basename(__FILE__),
					__LINE__,
					__FILE__,
					$r->customerId
				);
				return response()->json(["status" => 200, "message" => "customer firebase update Successfully."], 200);
			} else {
				return response()->json(["status" => 300, "message" => " Failed to update customer firebase."], 200);
			}
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while customer update firebase token .', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}


	public function serviceable_area(Request $r)
	{
		try {
			$data = [];
			$input = $r->all();
			$validator = Validator::make($input, [
				'latitude' => 'required',
				'longitude' => 'required',
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$latitude = $r->latitude;
			$longitude = $r->longitude;

			$point = new Point($latitude, $longitude);

			// Find which serviceable area contains this point
			$serviceableArea = ServiceableZone::whereContains('serviceable_area', $point)
				->where("is_delete", 0)
				->where("is_active", 1)
				->count();

			if ($serviceableArea > 0) {
				return response()->json([
					"status" => 200,
					"message" => "Serviceable area found.",
					"serviceable" => true
				], 200);
			}
			return response()->json([
				"status" => 300,
				"message" => "Serviceable area not found.",
				"serviceable" => false
			], 200);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while testing serviceable area.', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function create_order(Request $r)
	{
		try {
			$data = [];
			$input = $r->all();
			$validator = Validator::make($input, [
				'tripId' => 'required',
				'txnId' => 'required',
				'totalAmount' => 'required',
				'paymentOption' => 'required'
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$trip = Trip::where("id", $r->tripId)->first();

			if (empty($trip)) {
				return response()->json([
					'status' => 300,
					'message' => 'Trip not found'
				], 200);
			}

			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->create_phonepe_order($r->totalAmount, $trip->trip_payment_id, 'customer-trip-book', $trip->id);
			//print_r($phonepeResult);
			if (isset($phonepeResult["success"])) {
				if ($phonepeResult["success"] == true) {
					$paymentData = [
						"trip_payment_mode" => $r->paymentOption,
						"trip_payment_response" => $phonepeResult,
						"trip_payment_order_id" => $phonepeResult["orderId"],
					];
					$tripUpdate = $trip->update($paymentData);


					// Prepare the result response
					$resultData = [
						'customerId' => $trip->trip_customer_id,
						'phonepe' => $phonepeResult,
						'merchantOrderId' => $trip->trip_payment_id ?? "",
						'orderId' => $trip->id
					];

					return response()->json([
						'status' => 200,
						'message' => 'The trip has been booked successfully.',
						'result' => $resultData
					], 200);
				} else {
					return response()->json(['status' => 300, 'message' => 'The trip booking has failed.'], 200);
				}
			}
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while create order.', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong'
			], 400);
		}
	}

	public function check_phonepe_order_status(Request $r)
	{
		try {
			$merchantOrderId = $r->merchantOrderId;
			$data = [];
			$input = $r->all();
			$validator = Validator::make($input, [
				'tripId' => 'required',
				'merchantOrderId' => 'required'
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}

			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->check_phonepe_order_status($merchantOrderId);

			if ($phonepeResult['success']) {
				$status = strtoupper($phonepeResult['status']);
				$message = match (strtoupper($status)) {
					'COMPLETED' => 'Payment completed successfully.',
					'PENDING' => 'Payment is still pending.',
					'FAILED' => 'Payment failed.',
					'EXPIRED' => 'Payment has expired.',
					'REFUNDED' => 'Payment has been refunded.',
					default => 'Unknown payment status.',
				};

				/*$trip = Trip::find($r->tripId);					
				$paymentData=["trip_payment_status"=>strtolower($status)];
				$trip->update($paymentData);*/

				return response()->json([
					'status' => 200,
					'message' => $message,
					'result' => $phonepeResult['data'],
				]);
			} else {
				return response()->json([
					'status' => 300,
					'message' => $phonepeResult['message'] ?? 'Failed to fetch order status',
					'result' => $phonepeResult['error'] ?? [],
				], 200);
			}
		} catch (\Exception $ex) {
			LogHelper::logError(
				'An error occurred while checking order status.',
				$ex->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				__FILE__,
				""
			);

			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong',
			], 400);
		}
	}


	public function check_user_status(Request $r)
	{
		try {
           // return response()->json(["status" => 300, "message" => "Maintenance Notice: This app is currently under maintenance and will be unavailable for the next few days. Thank you for your patience."], 200);
			$input = $r->all();
			$validator = Validator::make($input, [
				'mobileNumber' => 'required|digits:10|numeric',
			]);
			if ($validator->fails()) {
				$response = [
					"status" => 500,
					"message" => $validator->errors()->first()
				];
				return response()->json($response, 200);
			}
			$customer = Customer::where("is_block", 1)
				->where("is_delete", 0)
				->where("customer_phone", $r->mobileNumber)
				->first();
			if (!empty($customer)) {
				return response()->json(["status" => 300, "message" => "Your account is blocked by admin.Please contact with administrator for further process."], 200);
			}
			return response()->json(["status" => 200, "message" => "No Found"], 200);
		} catch (\Exception $e) {
			\Log::error('Error: ' . $e->getMessage());
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}


	public function invoice(Request $r)
	{
		try {

			$id = $r->id;
			$data["tripDetails"] = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'sourceAddress', 'destinationAddress')
				->where('id', '=', $id)
				->first();

			// Generate a unique filename
			$filename = 'invoice_' . time() . '.pdf';

			// Directory path (storage/app/public/invoices)
			$directory = 'invoices';

			// Generate PDF
			$pdf = PDF::loadView('invoice', $data);

			// Save to storage (similar to avatar handling)
			$path = Storage::disk('public')->put($directory . '/' . $filename, $pdf->output());

			// Get the full public URL
			$publicUrl = Storage::disk('public')->url($directory . '/' . $filename);

			// Return the path and URL (similar to your avatar handling)
			return response()->json([
				'status' => 200,
				'message' => 'Invoice generated successfully',
				'path' => $path, // Relative storage path (invoices/filename.pdf)
				'url' => $publicUrl, // Full public URL
				'filename' => $filename
			]);
		} catch (\Exception $e) {
			\Log::error('Error: ' . $e->getMessage());
			return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
		}
	}
}
