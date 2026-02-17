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
use App\Models\Otps;
use App\Models\CustomerOtp;
use App\Models\Driver;
use App\Models\DriverBankDetails;
use App\Models\DriverDocumentDetails;
use App\Models\DriverVehicleDocumentDetails;
use App\Models\DriverVehicleDetails;
use App\Models\DriverTrainingVideo;
use App\Models\DriverTrainingVideoDetails;
use App\Models\TrainingVideo;
use App\Models\Trip;
use App\Models\Customer;
use App\Models\Customeraddress;
use App\Models\Vehicle;
use App\Models\TripStatus;
use App\Models\DriverEarning;
use App\Models\DriverWallet;
use App\Models\AdminCommission;
use App\Models\Rating;
use App\Models\Setting;
use App\Models\DriverOnlineOffline;
use App\Models\DriverRejectionReason;
use DB;
use PDF;
use App\Models\ServiceableZone;
use App\Classes\Notificationlibv_3;
use App\Classes\DriverPhonePe;

class DriverController extends Controller
{

    private $testingMobileNumbers = [
        "+918521007927",
        "8521007927",
        "+919373939082",
        "9373939082",
        "7385566988",
    ];

    //otp for driver
    public function sendotp($mobileno, $otp)
    {
        if (config('app.sms_mode') !== 'TEST' && (!in_array($mobileno, $this->testingMobileNumbers))) {
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
                Log::error("Partner otp not sent to $mobileno. cURL Error: $errorMsg");
                curl_close($ch);
                return [
                    'status' => 'failed',
                    'msg' => 'cURL Error: ' . $errorMsg
                ];
            }

            curl_close($ch);

            Log::info("Partner otp sent successfully to $mobileno with OTP $otp. Response: $response");
        }
        return [
            'status' => 'success',
            'msg' => "Partner otp sent successfully to $mobileno with OTP $otp"
        ];
    }

    //otp for pickup
    public function pickupotp($mobileno, $otp)
    {
        if (config('app.sms_mode') !== 'TEST' && (!in_array($mobileno, $this->testingMobileNumbers))) {
            $authKey = env("AUTH_KEY");
            $senderId = "NXSTOP";
            $templateId = "1707174038994417261"; // updated here
            $smsContentType = "english";
            $message = "Your verification code for the Next Stop App Pickup is {$otp}. Please provide this code to the driver. - NEXT STOP";
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
                Log::error("Pickup otp not sent to $mobileno. cURL Error: $errorMsg");
                curl_close($ch);
                return [
                    'status' => 'failed',
                    'msg' => 'cURL Error: ' . $errorMsg
                ];
            }

            curl_close($ch);

            Log::info("Pickup otp sent successfully to $mobileno with OTP $otp. Response: $response");
        }
        return [
            'status' => 'success',
            'msg' => "Pickup otp sent successfully to $mobileno with OTP $otp"
        ];
    }

    //otp for deliver
    public function deliverotp($mobileno, $otp)
    {
        if (config('app.sms_mode') !== 'TEST' && (!in_array($mobileno, $this->testingMobileNumbers))) {
            $authKey = env("AUTH_KEY");
            $senderId = "NXSTOP";
            $templateId = "1707174616706820430"; // updated here
            $smsContentType = "english";
            $message = "Your Parcel delivery confirmation code for the Next Stop is $otp. Please provide this code to confirm that you have received your parcel. - NEXT STOP";
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
                Log::error("Deliver otp not sent to $mobileno. cURL Error: $errorMsg");
                curl_close($ch);
                return [
                    'status' => 'failed',
                    'msg' => 'cURL Error: ' . $errorMsg
                ];
            }

            curl_close($ch);

            Log::info("Deliver otp sent successfully to $mobileno with OTP $otp. Response: $response");
        }
        return [
            'status' => 'success',
            'msg' => "Deliver otp sent successfully to $mobileno with OTP $otp"
        ];
    }


    /*
	*  seemashelar@neosao
	*  Random Otp Generate
	*/
    public function generateOTP($mobileno)
    {
        $otp = (config('app.sms_mode') !== "TEST" && (!in_array($mobileno, $this->testingMobileNumbers))) ? $this->randomOTP(6) : "123456";
        try {
            $result = Otps::create([
                'mobile' => $mobileno,
                'otp' => $otp,
                'type' => 'driver',
                'expired_at' => now()->addMinutes(10)
            ]);
            return $result->otp;
        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*  seemashelar@neosao
	*  check otp is expired or not
	*/

    public function checkRegisterOTP($otp, $mobileno)
    {
        $result = Otps::where('mobile', $mobileno)
            ->where("type", "driver")
            ->where('otp', $otp)
            ->first();

        if (!empty($result)) {
            if ($result->expired_at < now()) {
                Otps::where('mobile', $mobileno)->where('type', 'driver')->delete();
                return 'expired'; // Return 'expired' when the OTP is expired
            }
            Otps::where('mobile', $mobileno)->where('type', 'driver')->delete();
            return true; // OTP is valid
        }
        return false; // OTP is invalid
    }


    public function generateCustomerOTP($mobileno, $type, $tripId)
    {
        //$otp = "123456";
        $otp =  config('app.sms_mode') !== "TEST" && (!in_array($mobileno, $this->testingMobileNumbers)) ? $this->randomOTP(6) : "123456";
        try {
            $result = CustomerOtp::create([
                'mobile' => $mobileno,
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

    /*
	*  seemashelar@neosao
	*  check otp is expired or not
	*/

    public function checkCustomerRegisterOTP($otp, $contactNumber, $type = "", $tripId = "")
    {
        $query = CustomerOtp::where('mobile', $contactNumber)->where('otp', $otp);
        if ($type != "") {
            $query->where('type', $type);
        }
        if ($tripId != "") {
            $query->where('trip_id', $tripId);
        }
        $result = $query->orderBy('id', 'desc')->first();
        if (empty($result)) {
            return 'not-found';
        } else {
            if (now() > $result->expired_at) {
                // Delete expired OTP
                $result->delete();
                return 'expired';
            }
            // OTP is valid, delete it
            $result->delete();
            return 'valid';
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
            $otpNumber = $this->generateOTP($r->mobileNumber);

            // Check if OTP generation is successful
            if ($otpNumber != false) {
                // Optional: Skip sending OTP for specific number
                if (!in_array($r->mobileNumber, $this->testingMobileNumbers)) {
                    $otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
                    if ($otpResult["status"] == "failed") {
                        return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
                    }
                }

                //success log
                LogHelper::logSuccess('The Partner otp resend successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

                // Return success response
                return response()->json(['status' => 200, 'message' => 'OTP sent successfully'], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the Partner resend otp', 'Failed to resend OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return failure response if OTP could not be generated
            return response()->json(['status' => 300, 'message' => 'Failed to send OTP.'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the Partner resend otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch and return error in case of exception
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
          
           /* if($r->device=="android"){
				return response()->json(['status' => 300, 'message' => 'Maintenance Notice: This app is currently under maintenance and will be unavailable for the next few days. Thank you for your patience.'], 200);
			}*/

            // Check if the mobile number exists in the Driver table and is active (not deleted)
            $result = Driver::where("driver_phone", $r->mobileNumber)
                ->where("is_active", 1)
                ->where("is_delete", 0)
                ->first();

            // If the driver is found and active
            if (!empty($result)) {
              
                 if ($result->is_driver_block == 1) {
                        return response()->json(['status' => 500, 'message' => 'Your account is blocked by admin. Please contact the administrator for assistance.'], 200);
                 }
                
                // Generate OTP for the mobile number
                $otpNumber = $this->generateOTP($r->mobileNumber);

                // Check if OTP generation is successful
                if ($otpNumber != false) {
                    // Optional: Skip sending OTP for specific number
                    if (!in_array($r->mobileNumber, $this->testingMobileNumbers)) {
                        $otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
                        if ($otpResult["status"] == "failed") {
                            return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
                        }
                    }

                    //success log
                    LogHelper::logSuccess('The Partner otp send successfully for login.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);


                    // Return success response with OTP and account existence status
                    return response()->json(['status' => 200, 'message' => 'OTP was sent successfully!',  'mobile' => $r->mobileNumber, 'otp' => $otpNumber], 200);
                } else {

                    //log error
                    LogHelper::logError('An error occurred while the Partner send otp', 'Failed to send OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                    // Return failure response if OTP could not be generated
                    return response()->json(['status' => 300, 'message' => 'Failed to send OTP!'], 200);
                }
            } else {
                // If the driver is not found, check if the account is inactive
                $checkUserDelete = Driver::where("driver_phone", $r->mobileNumber)
                    ->where("is_active", 0)
                    ->first();

                if (!empty($checkUserDelete)) {
                    /*if($checkUserDelete->is_driver_delete == 1){
						return response()->json(['status' => 300, 'message' => 'Deleted account. Please contact the administrator or create a new account.'], 200);
					}*/
                    if ($checkUserDelete->is_driver_block == 1) {
                        return response()->json(['status' => 300, 'message' => 'Your account is blocked by admin. Please contact the administrator for assistance.'], 200);
                    }
                }
                // Generate OTP for the mobile number
                $otpNumber = $this->generateOTP($r->mobileNumber);

                // Optional: Skip sending OTP for specific number
                if (!in_array($r->mobileNumber, $this->testingMobileNumbers)) {
                    $otpResult = $this->sendotp($r->mobileNumber, $otpNumber);
                    if ($otpResult["status"] == "failed") {
                        return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
                    }
                }
                //log error
                LogHelper::logError('An error occurred while the Partner login', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                // Return response if the driver is not found
                return response()->json(['status' => 200, 'message' => 'Partner not found', 'mobile' => $r->mobileNumber, 'otp' => $otpNumber], 200);
            }
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the partner login.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

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
            $verifyOTP = $this->checkRegisterOTP($r->otp, $r->mobileNumber);

            // If OTP has expired, return an expired message
            if ($verifyOTP === 'expired') {
                //log error
                LogHelper::logError('An error occurred while the Partner verify otp', 'OTP has been expired.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'OTP has been expired.'
                ];
                return response()->json($response, 200);
            }
            // If OTP is invalid, return an invalid OTP message
            elseif ($verifyOTP === false) {

                //log error
                LogHelper::logError('An error occurred while the Partner verify otp', 'Invalid OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'Invalid OTP'
                ];
                return response()->json($response, 200);
            } else {
                // Check if the driver exists, is active, and not deleted
                $result = Driver::with(["serviceableZones", "vehicleDetails", "rating"])
                    ->where("is_active", 1)
                    ->where("driver_phone", $r->mobileNumber)
                    ->where("is_delete", 0)
                    ->first();

                // If the driver exists
                if (!empty($result)) {
                    // Generate a token for the driver
                    $token = $result->createToken('Driver', ['*'])->plainTextToken;

                    // Prepare the data to return
                    $data['id'] = $result->id;
                    $data['firstName'] = $result->driver_first_name ?? "";
                    $data['lastName'] = $result->driver_last_name ?? "";
                    $data['phoneNumber'] = $result->driver_phone ?? "";
                    $data['email'] = $result->driver_email ?? "";
                    $data['gender'] = $result->driver_gender ?? "";
                    $data['profilePhoto'] = $result->driver_photo ? asset('storage/' . $result->driver_photo) : ""; // Get profile photo URL or empty string
                    $data['serviceableLocationId'] = $result->serviceableZones->id ?? ""; // Default to empty string if serviceable zone not found
                    $data['serviceableLocationName'] = $result->serviceableZones->serviceable_zone_name ?? ""; // Default to empty string if serviceable zone name not found
                    $data['driverVehicleNumber'] = $result->vehicleDetails->vehicle_number ?? "";
                    $data['vehicleType'] = $result->vehicleDetails?->vehicle?->vehicleType?->vehicle_type ?? '';
                    $data['verificationStatus'] = ($result->admin_verification_status == 1 && $result->driver_document_verification_status == 1 && $result->driver_vehicle_verification_status == 1 && $result->driver_vehicle_document_verification_status == 1 && $result->driver_training_video_verification_status == 1) ? 1 : 0; // Check if all verification statuses are 1

                    $driverStatus = json_decode(stripslashes($result->driver_status)) ?? new stdClass(); // Decode driver status or use empty object
                    $data['documentRegisterStatus'] = $driverStatus->document_details ?? ""; // Default to empty string if document details not found
                    $data['vehicleRegisterStatus'] = $driverStatus->vehicle_details ?? ""; // Default to empty string if vehicle details not found
                    $data['trainingRegisterStatus'] = $driverStatus->training_video_details ?? ""; // Default to empty string if training video details not found
                    $averageRating = $result->rating->avg('rating_value') ?? 0;
                    $data['averageRating'] = round($averageRating, 2);
                    $verificationStatus = ($result->admin_verification_status == 1 && $result->driver_document_verification_status == 1 && $result->driver_vehicle_verification_status == 1 && $result->driver_vehicle_document_verification_status == 1 && $result->driver_training_video_verification_status == 1) ? 1 : 0;
                    //$data['status'] = json_decode(stripcslashes($result->porter_status)) ?? "";

                    //success log 
                    LogHelper::logSuccess('The Partner verify otp successfully while login.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

                    // Return success response with user data and token 
                    return response()->json(['status' => 200, 'message' => 'OTP Verified and Logged in successfully', "result" => $data, "token" => $token, 'verificationStatus' => $verificationStatus, 'accountExist' => 1], 200);
                }

                //log error
                LogHelper::logError('An error occurred while the Partner verify otp', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                // If the driver is not found, return an error message
                return response()->json(['status' => 200, 'message' => 'OTP Verified and Logged in successfully', 'accountExist' => 0, "result" => null, "verificationStatus" => 0, "token" => ""], 200);
            }
        } catch (\Exception $e) {

            //log error
            LogHelper::logError('An error occurred while the Partner verify otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch any exceptions and return an error response
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //driver register

    public function driver_register(Request $r)
    {
        try {
            // Get all input data from the request
            $input = $r->all();

            // Validate the input data
            $validator = Validator::make($input, [
                'firstName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',  // First name validation
                'lastName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',   // Last name validation
                'gender' => 'required|in:male,female,other',  // Gender validation
                'mobileNumber' => [
                    'required',
                    'digits:10',
                    'numeric',
                    Rule::unique('drivers', 'driver_phone')->where(function ($query) {
                        return $query->where('is_delete', '=', '0');
                    }),  // Ensure mobile number is unique and not marked as deleted
                ],
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('drivers', 'driver_email')->where(function ($query) {
                        return $query->where('is_delete', '=', '0');
                    }),  // Ensure email is unique and not marked as deleted
                ],
                'profilePhoto' => 'nullable|file|mimes:jpg,png,jpeg|max:10240',
                'serviceableLocation' => 'required'
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
                'profilePhoto.max' => 'The Profile photo file size must not exceed 10MB.',
                'serviceableLocation.required' => 'Serviceable Location is required.',
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

            // Prepare data to be inserted into the database
            $data = [
                "driver_first_name" => $r->firstName,
                "driver_last_name" => $r->lastName,
                "driver_email" => $r->email,
                "driver_phone" => $r->mobileNumber,
                "driver_gender" => $r->gender,
                "is_active" => 1,
                "admin_verification_status" => 0,
                "driver_serviceable_location" => $r->serviceableLocation,
                "driver_status" => json_encode(["document_details" => 0, "vehicle_details" => 0, "training_video_details" => 0]), // Default status
                "is_delete" => 0
            ];

            // Handle the porter photo image upload
            if ($r->hasFile('profilePhoto')) {
                $file = $r->file('profilePhoto');
                $imageName = 'driver-photo' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('driver-photo', $file, $imageName);
                $data["driver_photo"] = $path; // Save the image name in the database
            }

            // Create a new driver record in the database
            $result = Driver::create($data);

            // Check if the record was successfully created
            if ($result) {

                // Generate a token for the driver
                $token = $result->createToken('Driver', ['*'])->plainTextToken;

                // Check if the driver exists
                $driver = Driver::where("is_active", 1)
                    ->where("id", $result->id)
                    ->where("is_delete", 0)
                    ->first();
                if (!empty($driver)) {
                    // Prepare the data to return
                    $dataDriver['id'] = $driver->id;
                    $dataDriver['firstName'] = $driver->driver_first_name ?? "";
                    $dataDriver['lastName'] = $driver->driver_last_name ?? "";
                    $dataDriver['phoneNumber'] = $driver->driver_phone ?? "";
                    $dataDriver['email'] = $driver->driver_email ?? "";
                    $dataDriver['gender'] = $driver->driver_gender ?? "";
                    $dataDriver['profilePhoto'] = $driver->driver_photo ? asset('storage/' . $driver->driver_photo) : "";
                    $verificationStatus = ($driver->driver_document_verification_status == 1 && $driver->driver_vehicle_verification_status == 1 && $driver->driver_vehicle_document_verification_status == 1 && $driver->driver_training_video_verification_status == 1) ? 1 : 0;
                }
                //success log
                LogHelper::logSuccess('The Partner create successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $result->id);
                // Return success response
                return response()->json(['status' => 200, 'message' => 'Data added successfully', 'result' => $dataDriver, 'token' => $token, 'verificationStatus' => $verificationStatus], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the Partner create.', 'Failed to add data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return failure response if creation failed
            return response()->json(['status' => 300, 'message' => 'Failed to add data'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the Partner create.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);
            // Catch any exceptions and return an error response
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //serviceable location list
    public function serviceable_location(Request $request)
    {
        try {
            $input = $request->all();

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

            $searchTerm = $request->input('search', '');
            $perPage = 10;
            $page = 1;
            if ($request->page != "") {
                $page = $request->page; // Defaults to 1 if `page` is not provided
            }
            $query = ServiceableZone::select('id', 'serviceable_zone_name', 'serviceable_area')
                ->where('is_delete', 0)
                ->where('is_active', 1);

            if (!empty($searchTerm)) {
                $query->where('serviceable_zone_name', 'LIKE', "%{$searchTerm}%");
            }

            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page; // Adjust page within range
            $offset = ($page - 1) * $perPage;

            /*
			$result = $query->orderBy('serviceable_zone_name', 'ASC')
                ->skip($offset)
                ->limit($perPage)
                ->get();
                */

            $result = $query->orderBy('serviceable_zone_name', 'ASC')->get();

            return response()->json([
                "status" => 200,
                "message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
                "currentPage" => intval($page),
                "perPage" => $perPage,
                "totalPages" => $totalPages,
                "totalCount" => $totalCount,
                "result" => $result,
            ], 200);
        } catch (\Exception $e) {
            // Log the exception and return error response

            LogHelper::logError('An error occurred while the fetching serviceable location.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");

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
                'driverId' => 'required|integer' // Validate that driverId is required and an integer
            ], [
                'driverId.required' => 'The Partner ID is required.', // Custom message for missing driverId
                'driverId.integer' => 'The Partner ID must be a valid integer.', // Custom message for invalid driverId
            ]);

            if ($validator->fails()) { // Check if validation fails
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first() // Return the first validation error message
                ];
                return response()->json($response, 200);
            }

            $result = Driver::with(["serviceableZones", "vehicleDetails", "rating"]) // Get driver data along with serviceable zones
                ->where("is_active", 1) // Ensure driver is active
                ->where("id", $r->driverId) // Find driver by ID
                ->where("is_delete", 0) // Ensure driver is not deleted
                ->first(); // Get the first matching result

            if (!empty($result)) { // If the driver is found
                // Prepare the driver data to return
                $data['id'] = $result->id;
                $data['firstName'] = $result->driver_first_name ?? ""; // Default to empty string if not set
                $data['lastName'] = $result->driver_last_name ?? ""; // Default to empty string if not set
                $data['phoneNumber'] = $result->driver_phone ?? ""; // Default to empty string if not set
                $data['email'] = $result->driver_email ?? ""; // Default to empty string if not set
                $data['gender'] = $result->driver_gender ?? ""; // Default to empty string if not set
                $data['profilePhoto'] = $result->driver_photo ? asset('storage/' . $result->driver_photo) : ""; // Get profile photo URL or empty string
                $data['serviceableLocationId'] = $result->serviceableZones->id ?? ""; // Default to empty string if serviceable zone not found
                $data['serviceableLocationName'] = $result->serviceableZones->serviceable_zone_name ?? ""; // Default to empty string if serviceable zone name not found
                $data['driverVehicleNumber'] = $result->vehicleDetails->vehicle_number ?? "";
                $data['vehiclePhoto'] = $result->vehicleDetails->vehicle_photo ? asset('storage/' . $result->vehicleDetails->vehicle_photo) : "";

                $data['vehicleType'] = $result->vehicleDetails?->vehicle?->vehicleType?->vehicle_type ?? '';
                $data['verificationStatus'] = ($result->admin_verification_status == 1 && $result->driver_document_verification_status == 1 && $result->driver_vehicle_verification_status == 1 && $result->driver_vehicle_document_verification_status == 1 && $result->driver_training_video_verification_status == 1) ? 1 : 0; // Check if all verification statuses are 1

                $driverStatus = json_decode(stripslashes($result->driver_status)) ?? new stdClass(); // Decode partner status or use empty object
                $data['documentRegisterStatus'] = $driverStatus->document_details ?? ""; // Default to empty string if document details not found
                $data['vehicleRegisterStatus'] = $driverStatus->vehicle_details ?? ""; // Default to empty string if vehicle details not found
                $data['trainingRegisterStatus'] = $driverStatus->training_video_details ?? ""; // Default to empty string if training video details not found
                $averageRating = $result->rating->avg('rating_value') ?? 0;
                $data['averageRating'] = round($averageRating, 2);
                LogHelper::logSuccess('The partner basic details get successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $result->id); // Log success message

                return response()->json(['status' => 200, 'message' => 'Data found', "result" => $data], 200); // Return success response with partner data
            }

            LogHelper::logError('An error occurred while the partner basic details', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, ''); // Log error if driver not found

            return response()->json(['status' => 300, 'message' => 'Partner not found'], 200); // Return error response if partner not found
        } catch (\Exception $e) { // Catch any exceptions
            LogHelper::logError('An error occurred while the partner basic details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId); // Log error message
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400); // Return error response
        }
    }


    /* 
	*  seemashelar@neosao
	*  Three step register for driver
	*  1) document register
	*
	*/

    public function document_register(Request $r)
    {
        try {
            $input = $r->all();

            // Validation rules 
            $validator = Validator::make($input, [
                // Bank Details
                'driverId' => 'required|integer',
                'bankName' => 'required|string|regex:/^[A-Za-z\s]+$/',
                'bankAccountNumber' => 'required|numeric|regex:/^\d{9,18}$/',
                'bankIfscCode' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                'branchName' => 'required|max:255',

                // PAN card validation 
                'document.pan_card.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.pan_card.number' => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/',

                // Aadhar card validation
                'document.aadhar_card.file' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.aadhar_card.fileback' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.aadhar_card.number' => 'required|regex:/^\d{12}$/',

                // Driving License validation
                'document.driving_license.file' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.driving_license.number' => 'required|regex:/^[A-Z]{2}\d{2} \d{4}\d{7}$/',

                // Either Bank Passbook or Cancel Check validation
                'document.bank_passbook_or_cancel_cheque.file' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',

                // Vehicle documents
                // RC Book validation
                'document.rc_book.file' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.rc_book.number' => 'required|regex:/^[A-Z]{2} \d{2} [A-Z]{2} \d{4}$/',

                // Insurance document validation
                'document.insurance.file' => 'required|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.insurance.number' => 'required',
            ], [
                // Custom error messages for required fields and formats
                'driverId.required' => 'The driver ID is required.',
                'bankName.required' => 'The bank name is required.',
                'bankName.string' => 'The bank name must be a string.',
                'bankName.regex' => 'The bank name can contain only letters and spaces.',
                'bankAccountNumber.required' => 'The bank account number is required.',
                'bankAccountNumber.numeric' => 'The bank account number must be numeric.',
                'bankAccountNumber.regex' => 'The bank account number must be between 9 to 18 digits.',
                'bankIfscCode.required' => 'The IFSC code is required.',
                'bankIfscCode.regex' => 'The IFSC code must be in a valid format (e.g., ABCD0EFGHIJ).',
                'branchName.required' => 'The branch name is required.',
                'branchName.max' => 'The branch name cannot exceed 255 characters.',

                // PAN Card error messages
                //'document.pan_card.file.required' => 'The PAN card file is required.',
                'document.pan_card.file.file' => 'The PAN card must be a valid file.',
                'document.pan_card.file.mimes' => 'The PAN card must be a file of type: jpg, png, pdf, doc, docx.',
                'document.pan_card.file.max' => 'The PAN card file size must not exceed 10MB.',
                //'document.pan_card.number.required' => 'The PAN card number is required.',
                'document.pan_card.number.regex' => 'The PAN card number format is invalid. It must be in the format ABCDE1234F.',

                // Aadhar card error messages
                'document.aadhar_card.file.required' => 'The Aadhar card front file is required.',
                'document.aadhar_card.file.file' => 'The Aadhar card front file must be a valid file.',
                'document.aadhar_card.file.mimes' => 'The Aadhar card front file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.aadhar_card.file.max' => 'The Aadhar card front file size must not exceed 10MB.',
                'document.aadhar_card.fileback.required' => 'The Aadhar card back file is required.',
                'document.aadhar_card.fileback.file' => 'The Aadhar card back file must be a valid file.',
                'document.aadhar_card.fileback.mimes' => 'The Aadhar card back file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.aadhar_card.fileback.max' => 'The Aadhar card back file size must not exceed 10MB.',
                'document.aadhar_card.number.required' => 'The Aadhar card number is required.',
                'document.aadhar_card.number.regex' => 'The Aadhar card number format is invalid. It must be in the format 123456789101.',

                // Driving License error messages
                'document.driving_license.file.required' => 'The Driving License file is required.',
                'document.driving_license.file.file' => 'The Driving License must be a valid file.',
                'document.driving_license.file.mimes' => 'The Driving License must be a file of type: jpg, png, pdf, doc, docx.',
                'document.driving_license.file.max' => 'The Driving License file size must not exceed 10MB.',
                'document.driving_license.number.required' => 'The Driving License number is required.',
                'document.driving_license.number.regex' => 'The Driving License number format is invalid. It must be in the format: MH12 34567890123.',

                // Bank Passbook and Cancel Check error messages
                'document.bank_passbook_or_cancel_cheque.file.required' => 'Either Bank Passbook file or Cancel Check file is required.',
                'document.bank_passbook_or_cancel_cheque.file.file' => 'Either Bank Passbook file or Cancel Check must be a valid file.',
                'document.bank_passbook_or_cancel_cheque.file.mimes' => 'Either Bank Passbook file or Cancel Check must be a file of type: jpg, png, pdf, doc, docx.',
                'document.bank_passbook_or_cancel_cheque.file.max' => 'Either Bank Passbook file or Cancel Check size must not exceed 10MB.',
                //'document.bank_passbook_or_cancel_cheque.number.required' => 'Either Bank Passbook file or Cancel Check number is required.',
                //'document.bank_passbook_or_cancel_cheque.number.regex' => 'Either Bank Passbook file or Cancel Check number format is invalid. It must be a valid number format.',

                // RC Book error messages
                'document.rc_book.file.required' => 'The RC Book file is required.',
                'document.rc_book.file.file' => 'The RC Book must be a valid file.',
                'document.rc_book.file.mimes' => 'The RC Book must be a file of type: jpg, png, pdf, doc, docx.',
                'document.rc_book.file.max' => 'The RC Book file size must not exceed 10MB.',
                'document.rc_book.number.required' => 'The RC Book number is required.',
                'document.rc_book.number.regex' => 'The RC Book number format is invalid. It must be in the format MH 12 AB 1234.',

                // Insurance document error messages
                'document.insurance.file.required' => 'The Insurance file is required.',
                'document.insurance.file.file' => 'The Insurance file must be a valid file.',
                'document.insurance.file.mimes' => 'The Insurance file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.insurance.file.max' => 'The Insurance file size must not exceed 10MB.',
                'document.insurance.number.required' => 'The Insurance policy number is required.',
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


            // Bank details data
            $data = [
                "driver_id" => $r->driverId,
                "driver_bank_name" => $r->bankName,
                "driver_bank_account_number" => $r->bankAccountNumber,
                "driver_bank_ifsc_code" => $r->bankIfscCode,
                "driver_bank_branch_name" => $r->branchName,
                "is_active" => 1,
                "is_delete" => 0
            ];

            // Create a new driver bank details record in the database
            $result = DriverBankDetails::create($data);

            // Document handling (combined for both PAN, Aadhar ,Driving License,Bank Password)
            $personal_documents = ['pan_card', 'aadhar_card', 'driving_license', 'bank_passbook_or_cancel_cheque'];
            foreach ($personal_documents as $documentType) {
                if (isset($r->document[$documentType])) {
                    $documentData = $r->document[$documentType];
                    $documentPath = $documentData['file'] ?? null;
                    $documentNumber = $documentData['number'] ?? null;

                    $driverDocument = new DriverDocumentDetails;
                    $driverDocument->driver_id = $r->driverId;
                    $driverDocument->document_number = $documentNumber;

                    if ($documentPath) {
                        $fileExtension = $documentPath->getClientOriginalExtension();
                        $fileName = 'personal-document-' . time() . '-' . $documentPath->getClientOriginalName();

                        // Store file
                        $path = Storage::disk('public')->putFileAs('personal-document', $documentPath, $fileName);

                        // Save document details
                        $driverDocument->document_type = $documentType;
                        $driverDocument->document_1_file_type = $fileExtension;
                        $driverDocument->document_1 = $path;
                        $driverDocument->document_uploaded_at = now();
                        $driverDocument->is_active = 1;
                        $driverDocument->is_delete = 0;

                        // If it's an Aadhar card, handle the back side file
                        if ($documentType == 'aadhar_card' && isset($documentData['fileback'])) {
                            $fileBack = $documentData['fileback'];
                            $fileBackExtension = $fileBack->getClientOriginalExtension();
                            $fileBackName = 'personal-document-' . time() . '-' . $fileBack->getClientOriginalName();

                            // Store the back file
                            $backPath = Storage::disk('public')->putFileAs('personal-document', $fileBack, $fileBackName);

                            // Update document_2 with the back file path
                            $driverDocument->document_2 = $backPath;
                            $driverDocument->document_2_file_type = $fileBackExtension;
                        }
                        $driverDocument->save();
                    }
                }
            }

            $vehicle_documents = ["rc_book", "insurance"];
            foreach ($vehicle_documents as $documentType) {
                if (isset($r->document[$documentType])) {
                    $documentData = $r->document[$documentType];
                    $documentPath = $documentData['file'];
                    $documentNumber = $documentData['number'];

                    $driverVehicleDocument = new DriverVehicleDocumentDetails;
                    $driverVehicleDocument->driver_id = $r->driverId;
                    $driverVehicleDocument->document_number = $documentNumber;

                    if ($documentPath) {
                        $fileExtension = $documentPath->getClientOriginalExtension();
                        $fileName = 'vehicle-document-' . time() . '-' . $documentPath->getClientOriginalName();

                        // Store file
                        $path = Storage::disk('public')->putFileAs('vehicle-document', $documentPath, $fileName);

                        // Save document details
                        $driverVehicleDocument->document_type = $documentType;
                        $driverVehicleDocument->document_file_type = $fileExtension;
                        $driverVehicleDocument->document_file_path = $path;
                        $driverVehicleDocument->document_uploaded_at = now();
                        $driverVehicleDocument->is_active = 1;
                        $driverVehicleDocument->is_delete = 0;
                        $driverVehicleDocument->save();
                    }
                }
            }
            if ($result) {
                $getDriver = Driver::where("id", $r->driverId)->first();
                //update status 1 for document details in first step 
                if (!empty($getDriver)) {
                    $status = json_decode($getDriver->driver_status, true);
                    if ($status["document_details"] == 0) {
                        $getDriver->driver_status = json_encode(["document_details" => 1, "vehicle_details" => 0, "training_video_details" => 0]);
                        $getDriver->driver_document_verification_status = 1;
                        $getDriver->driver_vehicle_document_verification_status = 1;
                        $getDriver->save();
                    }
                }

                //success log
                LogHelper::logSuccess('The partner register personal documents successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                //Return Success response
                return response()->json(['status' => 200, 'message' => 'Data added successfully'], 200);
            }
            //log error
            LogHelper::logError('An error occurred while the partner register personal documents', 'Failed to add data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return failure response
            return response()->json(['status' => 300, 'message' => 'Failed to add data'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the partner register personal documents.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //This api will get vehicle which is used for vehicle register api 

    public function vehicles(Request $request)
    {
        try {
            $input = $request->all();

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

            $searchTerm = $request->input('search', '');
            $perPage = 10;
            $page = 1;
            if ($request->page != "") {
                $page = $request->page; // Defaults to 1 if `page` is not provided
            }
            $vehicleQuery = Vehicle::select('id', 'vehicle_name')
                ->where('is_delete', 0)
                ->where('is_active', 1);

            if (!empty($searchTerm)) {
                $vehicleQuery->where('vehicle_name', 'LIKE', "%{$searchTerm}%");
            }

            $totalCount = $vehicleQuery->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page; // Adjust page within valid range
            $offset = ($page - 1) * $perPage;

            $vehicles = $vehicleQuery->orderBy('vehicle_name', 'ASC')
                ->skip($offset)
                ->limit($perPage)
                ->get();

            return response()->json([
                'status' => 200,
                'message' => $totalCount > 0 ? 'Data Found' : 'No Data Found',
                'currentPage' => intval($page),
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalCount' => $totalCount,
                'result' => $vehicles,
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching vehicle types.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__);

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong'
            ], 400);
        }
    }


    //seemashelar@neosao
    //This Api is used for second step registration for driver i.e vehicle registration
    public function vehicle_register(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer',
                'vehicle' => 'required|integer',
                'vehicleNumber' => 'required|min:2|max:15|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/',

                // Vehicle Photo validation
                'vehiclePhoto' => 'required|file|mimes:jpg,png,jpeg|max:10240',
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',

                'vehicle.required' => 'The vehicle  is required.',
                'vehicle.string' => 'The vehicle type must be a valid integer.',

                'vehicleNumber.required' => 'The vehicle number is required.',
                'vehicleNumber.min' => 'The vehicle number must be at least 2 characters.',
                'vehicleNumber.max' => 'The vehicle number cannot exceed 15 characters.',
                'vehicleNumber.regex' => 'The vehicle number must be in the format: XX00XX1234.',

                'vehiclePhoto.required' => 'The vehicle photo is required.',
                'vehiclePhoto.file' => 'The vehicle photo must be a valid file.',
                'vehiclePhoto.mimes' => 'The vehicle photo must be a file of type: jpg, png,jpeg.',
                'vehiclePhoto.max' => 'The vehicle photo file size must not exceed 10MB.',
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

            // Vehicle details data
            $driverVehicleDetails = new DriverVehicleDetails;
            $driverVehicleDetails->driver_id = $r->driverId;
            $driverVehicleDetails->vehicle_number = $r->vehicleNumber;
            $driverVehicleDetails->vehicle_id = $r->vehicle;
            $driverVehicleDetails->is_active = 1;
            $driverVehicleDetails->is_delete = 0;
            if ($r->hasFile('vehiclePhoto')) {
                $file = $r->file('vehiclePhoto');
                $imageName = 'vehicle-photo-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('vehicle-photo', $file, $imageName);
                $driverVehicleDetails->vehicle_photo = $path;
            }

            // Create a new vehicle details record in the database 
            $result = $driverVehicleDetails->save();

            if ($result) {

                $getDriver = Driver::where("id", $r->driverId)->first();
                //update status 1 for vehicle details in first step 
                if (!empty($getDriver)) {
                    $status = json_decode($getDriver->driver_status, true);
                    if ($status["vehicle_details"] == 0) {
                        $getDriver->driver_status = json_encode(["document_details" => 1, "vehicle_details" => 1, "training_video_details" => 0]);
                        $getDriver->driver_vehicle_verification_status = 1;
                        $getDriver->driver_vehicle_document_verification_status = 1;
                        $getDriver->save();
                    }
                }

                //success log
                LogHelper::logSuccess('The partner register vehicle details successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

                //Return Success response 
                return response()->json(['status' => 200, 'message' => 'Data added successfully'], 200);
            }
            //log error
            LogHelper::logError('An error occurred while the partner register vehicle details', 'Failed to add data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return failure response
            return response()->json(['status' => 300, 'message' => 'Failed to add data'], 200);
        } catch (\Exception $e) {

            //log error
            LogHelper::logError('An error occurred while the partner register vehicle details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");

            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //Training Video list which is used for training details api

    public function training_video_list(Request $r)
    {
        try {
            $get_video_list = TrainingVideo::where("is_delete", 0)
                ->where("is_active", 1)
                ->get();
            if (!empty($get_video_list)) {
                //create active status video array
                $videoArray = [];
                foreach ($get_video_list as $item) {
                    $driverVideoStatus = DriverTrainingVideoDetails::where("training_video_id", $item->id)
                        ->where("driver_id", $r->driverId)
                        ->first();
                    $status = 0;
                    if (!empty($driverVideoStatus)) {
                        $status = $driverVideoStatus->checked_status;
                    }

                    $data = [
                        "id" => $item->id,
                        "videoTitle" => $item->video_title,
                        "videoPath" => $item->video_path ? asset('storage/videos/' . $item->video_path) : "",
                        "thumbnail" => $item->thumbnail ? asset('storage/thumbnails/' . $item->thumbnail) : "",
                        "totalVideoTimeLength" => $item->total_video_time_length ?? "",
                        "status" => $status

                    ];
                    array_push($videoArray, $data);
                }

                //return success response 
                return response()->json([
                    'status' => 200,
                    'message' => 'Data Found',
                    'result' => $videoArray
                ], 200);
            }

            //return no data found response
            return response()->json([
                'status' => 300,
                'message' => 'Data Not Found',
            ], 200);
        } catch (\Exception $e) {
            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //This Api is used for third step registration for partner i.e training video seen 
    public function training_video_details(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer',
                'videoId' => 'required|integer'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',

                'videoId.required' => 'The video is required.',
                'videoId.integer' => 'The video ID must be a valid integer.'
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

            $checkTrainingVideoMaster = DriverTrainingVideo::where("driver_id", $r->driverId)->first();
            if (empty($checkTrainingVideoMaster)) {
                //add master training video entry
                $trainingVideoMaster = new DriverTrainingVideo;
                $trainingVideoMaster->driver_id = $r->driverId;
                $trainingVideoMaster->is_active = 1;
                $trainingVideoMaster->is_delete = 0;
                $trainingVideoMaster->save();
            }
            $checkTrainingVideoDetails = DriverTrainingVideoDetails::where("driver_id", $r->driverId)->where("training_video_id", $r->videoId)->first();
            if (empty($checkTrainingVideoDetails)) {
                //add training video seen entry of each driver
                $trainingVideoDetails = new DriverTrainingVideoDetails;
                $trainingVideoDetails->driver_id = $r->driverId;
                $trainingVideoDetails->training_video_id = $r->videoId;
                $trainingVideoDetails->checked_status = 1;
                $trainingVideoDetails->is_active = 1;
                $trainingVideoDetails->is_delete = 0;
                $result = $trainingVideoDetails->save();

                if ($result) {
                    //success log
                    LogHelper::logSuccess('The partner seen training video.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

                    $getDriver = Driver::where("id", $r->driverId)->first();
                    //update status 1 for training video in first step 
                    if (!empty($getDriver)) {
                        $status = json_decode($getDriver->driver_status, true);

                        $getTrainingVideoCount = TrainingVideo::where("is_active", 1)
                            ->where("is_delete", 0)
                            ->count();
                        $getTotalSeenVideoCount = DriverTrainingVideoDetails::where("driver_id", $r->driverId)
                            ->where("checked_status", 1)
                            ->count();


                        //if ($status["training_video_details"] == 0) {
                        if ($getTrainingVideoCount == $getTotalSeenVideoCount) {
                            $getDriver->driver_status = json_encode(["document_details" => 1, "vehicle_details" => 1, "training_video_details" => 1]);
                            $getDriver->driver_training_video_verification_status = 1;
                            $getDriver->save();
                        }
                        //}
                    }

                    //Return Success response 
                    return response()->json(['status' => 200, 'message' => 'Training video seen successfully'], 200);
                }
                //log error
                LogHelper::logError('An error occurred while the partner seen training video.', 'Failed to seen training video.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                //Return Failure response 
                return response()->json(['status' => 300, 'message' => 'Failed to seen training video.'], 200);
            }
            //Return Success response 
            return response()->json(['status' => 200, 'message' => 'You already seen this training video'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the partner seen training video.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //This Api is used to get all register data of partner  

    public function driver_register_info(Request $r)
    {
        try {
            // Get all input data from the request
            $input = $r->all();

            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
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

            // Check if the driver exists, is active, and not deleted
            $result = Driver::where("is_active", 1)
                ->where("id", $r->driverId)
                ->where("is_delete", 0)
                ->first();
            if (!empty($result)) {
                $driverStatus = json_decode(stripslashes($result->driver_status)) ?? "";
                $data['verificationStatus'] = ($result->driver_document_verification_status == 1 && $result->driver_vehicle_verification_status == 1 && $result->driver_vehicle_document_verification_status == 1 && $result->driver_training_video_verification_status == 1) ? 1 : 0; // Check if all verification statuses are 1

                $data['documentVerificationStatus'] = $result->driver_document_verification_status ?? 0;
                $data['vehicleVerificationStatus'] = $result->driver_vehicle_verification_status ?? 0;
                $data['trainingVideoVerificationStatus'] = $result->driver_training_video_verification_status ?? 0;
                $data['adminVerificationStatus'] = $result->admin_verification_status ?? 0;
                $data['adminVerificationReason'] = $result->admin_verification_reason ?? "";
                $data['documentRegisterStatus'] = $driverStatus->document_details ?? 0;
                $data['vehicleRegisterStatus'] = $driverStatus->vehicle_details ?? 0;
                $data['trainingRegisterStatus'] = $driverStatus->training_video_details ?? 0;

                $data['documentDetails'] = new \stdClass();
                $data['vehicleDetails'] = new \stdClass();
                $data['trainingVideoDetails'] = []; // Default to empty array


                //get Bank Details
                $getBankDetails = DriverBankDetails::where("driver_id", $r->driverId)->first();
                //get vehicle details 

                $driver_vehicle_details = DriverVehicleDetails::with('vehicle')->where('driver_id', $r->driverId)->where('is_delete', 0)->first();
                //get training video details 

                $training_video_details = DriverTrainingVideo::where("driver_id", $r->driverId)->first();

                $driver_training_video_details = DriverTrainingVideoDetails::with('trainingVideo')
                    ->where('driver_id', $r->driverId)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->get();
                if (!empty($getBankDetails)) {
                    $getPersonalDocumentsDetails = DriverDocumentDetails::where("driver_id", $r->driverId)->get();
                    $getVehicleDocumentsDetails = DriverVehicleDocumentDetails::where("driver_id", $r->driverId)->get();

                    $data['documentDetails'] = [
                        'documentReason' => $getPersonalDocumentsDetails[0]->document_verification_reason ?? "",
                        'bankDetails' => [
                            'bankName' => $getBankDetails->driver_bank_name ?? "",
                            'bankAccountNumber' => $getBankDetails->driver_bank_account_number ?? "",
                            'bankIfscCode' => $getBankDetails->driver_bank_ifsc_code ?? "",
                            'bankBranchName' => $getBankDetails->driver_bank_branch_name ?? "",
                        ],
                        'personalDocuments' => [],
                        'vehicleDocuments' => [],
                    ];

                    // Personal Documents
                    foreach ($getPersonalDocumentsDetails as $doc) {
                        $data['documentDetails']['personalDocuments'][] = [
                            'id' => $doc->id ?? "",
                            'documentType' => $doc->document_type ?? "",
                            'documentFileType1' => $doc->document_1_file_type ?? "",
                            'documentFileType2' => $doc->document_2_file_type ?? "",
                            'documentNumber' => $doc->document_number ?? "",
                            'documentFile1' => $doc->document_1 ? asset('storage/' . $doc->document_1) : "",
                            'documentFile2' => $doc->document_2 ? asset('storage/' . $doc->document_2) : "",
                        ];
                    }

                    // Vehicle Documents
                    foreach ($getVehicleDocumentsDetails as $doc) {
                        $data['documentDetails']['vehicleDocuments'][] = [
                            'id' => $doc->id ?? "",
                            'documentType' => $doc->document_type ?? "",
                            'documentFileType' => $doc->document_file_type ?? "",
                            'documentNumber' => $doc->document_number ?? "",
                            'documentFile' => $doc->document_file_path ? asset('storage/' . $doc->document_file_path) : "",
                        ];
                    }
                }
                //get vehicle details
                if (!empty($driver_vehicle_details)) {
                    $data['vehicleDetails'] = [
                        'id' => $driver_vehicle_details->id ?? "",
                        'vehicleId' => $driver_vehicle_details->vehicle->id ?? "",
                        'vehicleName' => $driver_vehicle_details->vehicle->vehicle_name ?? "",
                        'vehicleNumber' => $driver_vehicle_details->vehicle_number ?? "",
                        'vehiclePhoto' => $driver_vehicle_details->vehicle_photo ? asset('storage/' . $driver_vehicle_details->vehicle_photo) : "",
                        "vehicleReason" => $driver_vehicle_details->vehicle_verification_reason ?? "",
                    ];
                }
                //get video details 
                if (!empty($driver_training_video_details)) {
                    $trainingVideo = [];
                    foreach ($driver_training_video_details as $item) {
                        // Check if TrainingVideo exists and has the necessary fields
                        $trainingVideoData = [
                            'id' => $item->TrainingVideo->id,
                            'videoTitle' => $item->TrainingVideo->video_title ?? "", // Default if missing
                            'videoFile' => $item->TrainingVideo->video_path ? asset('storage/video/' . $item->TrainingVideo->video_path) : "",
                            'videoStatus' => $item->check_status ?? "", // Default if missing
                        ];
                        // Push the formatted data into the array
                        array_push($trainingVideo, $trainingVideoData);
                    }

                    // Add the training video details to the response data
                    $data['trainingVideoDetails'] = $trainingVideo;

                    $data['trainingVideoDetails'] = [
                        'videos' => $trainingVideo,
                        'videoReason' => $training_video_details->training_video_verification_reason ?? "",
                    ];
                }
                //success log 
                LogHelper::logSuccess('The partner register data get successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $result->id);

                // Return success response with user data 
                return response()->json(['status' => 200, 'message' => 'Data found', "result" => $data], 200);
            }
            //log error
            LogHelper::logError('An error occurred while the partner register data', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // If the driver is not found, return an error message
            return response()->json(['status' => 300, 'message' => 'Partner not found'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the getting partner register data.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //driver self delete
    public function driver_delete(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required'
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
                'is_driver_delete' => 1
            ];

            $driver = Driver::where("id", $r->driverId)->first();
            $result = $driver->update($data);
            if ($result == true) {
                Log::info("Driver delete" . $r->driverId);
                //success log
                LogHelper::logSuccess('The partner delete successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

                return response()->json(["status" => 200, "message" => "Deleted Successfully."], 200);
            } else {
                //log error
                LogHelper::logError('An error occurred while the partner delete', 'Failed to delete partner.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                return response()->json(["status" => 300, "message" => "Failed to delete partner."], 200);
            }
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the getting partner delete.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    //seemashelar@neosao
    //basic profile update
    public function basic_profile_update(Request $r)
    {
        try {
            // Validate the input data
            $input = $r->all();
            $id = $r->driverId;
            $validator = Validator::make($input, [
                'driverId' => 'required',
                'firstName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',  // First name validation
                'lastName' => 'required|min:2|max:150|regex:/^[a-zA-Z\s]+$/',   // Last name validation
                'gender' => 'required|in:male,female,other',  // Gender validation
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('drivers', 'driver_email')->where(function ($query) use ($id) {
                        $query->where('is_delete', '0')
                            ->where('is_driver_delete', '0')
                            ->where('id', '!=', $id);
                    }),   // Ensure email is unique and not marked as deleted
                ],
                'profilePhoto' => 'nullable|file|mimes:jpg,png,jpeg|max:10240',
                'serviceableLocation' => 'required'
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
                'profilePhoto.max' => 'The Profile photo file size must not exceed 10MB.',
                'serviceableLocation.required' => 'Serviceable Location is required.',
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

            //check whether driver is present in database
            $driver = Driver::where("is_active", 1)
                ->where("is_delete", 0)
                ->where("id", $id)
                ->first();
            if (!empty($driver)) {
                // Prepare data to be updated into the database
                $data = [
                    "driver_first_name" => $r->firstName,
                    "driver_last_name" => $r->lastName,
                    "driver_email" => $r->email,
                    "driver_gender" => $r->gender,
                    "driver_serviceable_location" => $r->serviceableLocation
                ];

                // Handle the porter photo image upload
                if ($r->hasFile('profilePhoto')) {
                    $file = $r->file('profilePhoto');
                    $imageName = 'driver-photo' . time() . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs('driver-photo', $file, $imageName);
                    $data["driver_photo"] = $path; // Save the image name in the database
                }

                //driver update 
                $result = $driver->update($data);
                if ($result == true) {
                    //success log
                    LogHelper::logSuccess('The partner updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                    // Return success response
                    return response()->json(['status' => 200, 'message' => 'Data updated successfully'], 200);
                }

                //log error
                LogHelper::logError('An error occurred while the partner update.', 'Failed to update data.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return failure response if update failed
                return response()->json(['status' => 300, 'message' => 'Failed to update data'], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the partner update data.', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return failure response if creation failed
            return response()->json(['status' => 300, 'message' => 'Partner not found.'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the getting partner update data.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    //seemashelar@neosao
    //update first step of driver as document register /bank info 
    public function document_update(Request $r)
    {
        try {
            $input = $r->all();

            // Validation rules 
            $validator = Validator::make($input, [
                // Bank Details
                'driverId' => 'required|integer',
                'bankName' => 'required|string|regex:/^[A-Za-z\s]+$/',
                'bankAccountNumber' => 'required|numeric|regex:/^\d{9,18}$/',
                'bankIfscCode' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                'branchName' => 'required|max:255',

                // PAN card validation
                'document.pan_card.id' => 'nullable',
                'document.pan_card.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.pan_card.number' => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/',

                // Aadhar card validation
                'document.aadhar_card.id' => 'required',
                'document.aadhar_card.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.aadhar_card.fileback' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.aadhar_card.number' => 'nullable|regex:/^\d{12}$/',

                // Driving License validation
                'document.driving_license.id' => 'required',
                'document.driving_license.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.driving_license.number' => 'nullable|regex:/^[A-Z]{2}\d{2} \d{4}\d{7}$/',

                // Either Bank Passbook or Cancel Check validation
                'document.bank_passbook_or_cancel_cheque.id' => 'required',
                'document.bank_passbook_or_cancel_cheque.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',

                // Vehicle documents
                // RC Book validation
                'document.rc_book.id' => 'required',
                'document.rc_book.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.rc_book.number' => 'nullable|regex:/^[A-Z]{2} \d{2} [A-Z]{2} \d{4}$/',

                // Insurance document validation
                'document.insurance.id' => 'required',
                'document.insurance.file' => 'nullable|file|mimes:jpg,png,pdf,doc,docx|max:10240',
                'document.insurance.number' => 'nullable',

            ], [
                // Custom error messages for required fields and formats
                'driverId.required' => 'The driver ID is required.',
                'bankName.required' => 'The bank name is required.',
                'bankName.string' => 'The bank name must be a string.',
                'bankName.regex' => 'The bank name can contain only letters and spaces.',
                'bankAccountNumber.required' => 'The bank account number is required.',
                'bankAccountNumber.numeric' => 'The bank account number must be numeric.',
                'bankAccountNumber.regex' => 'The bank account number must be between 9 to 18 digits.',
                'bankIfscCode.required' => 'The IFSC code is required.',
                'bankIfscCode.regex' => 'The IFSC code must be in a valid format (e.g., ABCD0EFGHIJ).',
                'branchName.required' => 'The branch name is required.',
                'branchName.max' => 'The branch name cannot exceed 255 characters.',

                // PAN Card error messages
                //'document.pan_card.id.required' => 'The PAN card id is required.',
                'document.pan_card.file.file' => 'The PAN card must be a valid file.',
                'document.pan_card.file.mimes' => 'The PAN card must be a file of type: jpg, png, pdf, doc, docx.',
                'document.pan_card.file.max' => 'The PAN card file size must not exceed 10MB.',
                'document.pan_card.number.regex' => 'The PAN card number format is invalid. It must be in the format ABCDE1234F.',

                // Aadhar card error messages
                'document.aadhar_card.id.required' => 'The Aadhar card id is required.',
                'document.aadhar_card.file.file' => 'The Aadhar card front file must be a valid file.',
                'document.aadhar_card.file.mimes' => 'The Aadhar card front file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.aadhar_card.file.max' => 'The Aadhar card front file size must not exceed 10MB.',
                'document.aadhar_card.fileback.required' => 'The Aadhar card back file is required.',
                'document.aadhar_card.fileback.file' => 'The Aadhar card back file must be a valid file.',
                'document.aadhar_card.fileback.mimes' => 'The Aadhar card back file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.aadhar_card.fileback.max' => 'The Aadhar card back file size must not exceed 10MB.',
                'document.aadhar_card.number.required' => 'The Aadhar card number is required.',
                'document.aadhar_card.number.regex' => 'The Aadhar card number format is invalid. It must be in the format 123456789101.',

                // Driving License error messages
                'document.driving_license.id.required' => 'The Driving License id is required.',
                'document.driving_license.file.file' => 'The Driving License must be a valid file.',
                'document.driving_license.file.mimes' => 'The Driving License must be a file of type: jpg, png, pdf, doc, docx.',
                'document.driving_license.file.max' => 'The Driving License file size must not exceed 10MB.',
                'document.driving_license.number.required' => 'The Driving License number is required.',
                'document.driving_license.number.regex' => 'The Driving License number format is invalid. It must be in the format: MH12 34567890123.',

                // Bank Passbook and Cancel Check error messages
                'document.bank_passbook_or_cancel_cheque.file.required' => 'Either Bank Passbook file or Cancel Check file is required.',
                'document.bank_passbook_or_cancel_cheque.file.file' => 'Either Bank Passbook file or Cancel Check must be a valid file.',
                'document.bank_passbook_or_cancel_cheque.file.mimes' => 'Either Bank Passbook file or Cancel Check must be a file of type: jpg, png, pdf, doc, docx.',
                'document.bank_passbook_or_cancel_cheque.file.max' => 'Either Bank Passbook file or Cancel Check size must not exceed 10MB.',

                // RC Book error messages
                'document.rc_book.id.required' => 'The RC Book file id is required.',
                'document.rc_book.file.file' => 'The RC Book must be a valid file.',
                'document.rc_book.file.mimes' => 'The RC Book must be a file of type: jpg, png, pdf, doc, docx.',
                'document.rc_book.file.max' => 'The RC Book file size must not exceed 10MB.',
                'document.rc_book.number.required' => 'The RC Book number is required.',
                'document.rc_book.number.regex' => 'The RC Book number format is invalid. It must be in the format MH 12 AB 1234.',

                // Insurance document error messages
                'document.insurance.id.required' => 'The Insurance file id is required.',
                'document.insurance.file.file' => 'The Insurance file must be a valid file.',
                'document.insurance.file.mimes' => 'The Insurance file must be a file of type: jpg, png, pdf, doc, docx.',
                'document.insurance.file.max' => 'The Insurance file size must not exceed 10MB.',
                'document.insurance.number.required' => 'The Insurance policy number is required.',
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

            $driver = Driver::where("is_active", 1)
                ->where("is_delete", 0)
                ->where("id", $r->driverId)
                ->first();
            if (!empty($driver)) {

                $driver->driver_document_verification_status = 1;
                $driver->driver_vehicle_document_verification_status = 1;
                $driver->save();

                // Bank details data			
                $bank_details = DriverBankDetails::where('driver_id', $r->driverId)->first();
                $bank_details->driver_bank_name = $r->bankName;
                $bank_details->driver_bank_account_number = $r->bankAccountNumber;
                $bank_details->driver_bank_ifsc_code = $r->bankIfscCode;
                $bank_details->driver_bank_branch_name = $r->branchName;
                $bank_details->save();


                // List of personal document types
                $personal_documents = ['pan_card', 'aadhar_card', 'driving_license', 'bank_passbook_or_cancel_cheque'];

                // Process each personal document type
                foreach ($personal_documents as $documentType) {
                    if (isset($r->document[$documentType])) {
                        $documentData = $r->document[$documentType];
                        $documentId = $documentData['id'] ?? null;
                        $documentPath = $documentData['file'] ?? null;
                        $documentNumber = $documentData['number'] ?? null;

                        // If no document ID is provided, create a new document if a file is given
                        if (!$documentId) {
                            if (empty($documentPath)) {
                                continue; // Skip creating the document if no file is provided
                            }

                            // Create a new document entry
                            $driverDocument = new DriverDocumentDetails;
                            $driverDocument->driver_id = $r->driverId;  // Assuming 'driver_id' is needed
                            $driverDocument->document_type = $documentType;
                            // Set document number if applicable (only for required document types)
                            if ($documentNumber && in_array($documentType, ['driving_license', 'aadhar_card'])) {
                                $driverDocument->document_number = $documentNumber;
                            }

                            // Handle document file upload
                            if ($documentPath) {
                                $fileExtension = $documentPath->getClientOriginalExtension();
                                $fileName = 'personal-document-' . time() . '-' . $documentPath->getClientOriginalName();

                                // Store the file and update the document path in the database
                                $path = Storage::disk('public')->putFileAs('personal-document', $documentPath, $fileName);
                                $driverDocument->document_1 = $path;
                                $driverDocument->document_1_file_type = $fileExtension;
                            }
                            $driverDocument->document_verification_status = 0;
                            // Save the new document entry
                            $driverDocument->save();
                        } else {
                            // If document ID is provided, find the existing document or create a new one if it doesn't exist
                            $driverDocument = DriverDocumentDetails::find($documentId);

                            if (!$driverDocument) {
                                // If document doesn't exist, create a new instance
                                $driverDocument = new DriverDocumentDetails;
                                $driverDocument->driver_id = $r->driverId;  // Assuming 'driver_id' is needed
                            }

                            // If there's no file and no number, skip updating
                            if (empty($documentPath) && empty($documentNumber)) {
                                continue; // Skip if both file and number are empty
                            }

                            // Update document number if applicable (only for required document types)
                            if ($documentNumber && in_array($documentType, ['driving_license', 'aadhar_card'])) {
                                $driverDocument->document_number = $documentNumber;
                            }

                            // Handle document file upload if provided
                            if ($documentPath) {
                                $fileExtension = $documentPath->getClientOriginalExtension();
                                $fileName = 'personal-document-' . time() . '-' . $documentPath->getClientOriginalName();

                                // Store the file and update the document path in the database
                                $path = Storage::disk('public')->putFileAs('personal-document', $documentPath, $fileName);
                                $driverDocument->document_1 = $path;
                                $driverDocument->document_1_file_type = $fileExtension;
                            }
                            $driverDocument->document_verification_status = 0;
                            // Save the updated document entry
                            $driverDocument->save();
                        }
                    }
                }

                // Process vehicle documents (RC Book, Insurance)
                $vehicle_documents = ['rc_book', 'insurance'];

                foreach ($vehicle_documents as $documentType) {
                    if (isset($r->document[$documentType])) {
                        $documentData = $r->document[$documentType];
                        $documentId = $documentData['id'] ?? null;
                        $documentPath = $documentData['file'] ?? null;
                        $documentNumber = $documentData['number'] ?? null;

                        // If no document ID is provided, create a new document if a file is given
                        if (!$documentId) {
                            if (empty($documentPath)) {
                                continue; // Skip creating the document if no file is provided
                            }

                            // Create a new document entry
                            $driverVehicleDocument = new DriverVehicleDocumentDetails;
                            $driverVehicleDocument->driver_id = $r->driverId;  // Assuming 'porter_id' is needed

                            // Set document number if provided
                            if ($documentNumber) {
                                $driverVehicleDocument->document_number = $documentNumber;
                            }

                            // Handle document file upload
                            if ($documentPath) {
                                $fileExtension = $documentPath->getClientOriginalExtension();
                                $fileName = 'vehicle-document-' . time() . '-' . $documentPath->getClientOriginalName();

                                // Store the file and update the document path in the database
                                $path = Storage::disk('public')->putFileAs('vehicle-document', $documentPath, $fileName);
                                $driverVehicleDocument->document_file_path = $path;
                                $driverVehicleDocument->document_file_type = $fileExtension;
                            }
                            $driverVehicleDocument->document_verification_status = 0;
                            // Save the new document entry
                            $driverVehicleDocument->save();
                        } else {
                            // If document ID is provided, find the existing document or create a new one if it doesn't exist
                            $driverVehicleDocument = DriverVehicleDocumentDetails::find($documentId);

                            if (!$driverVehicleDocument) {
                                // If document doesn't exist, create a new instance
                                $driverVehicleDocument = new DriverVehicleDocumentDetails;
                                $driverVehicleDocument->driver_id = $r->driverId;  // Assuming 'driver_id' is needed
                            }

                            // If there's no file and no number, skip updating
                            if (empty($documentPath) && empty($documentNumber)) {
                                continue; // Skip if both file and number are empty
                            }

                            // Update document number if provided
                            if ($documentNumber) {
                                $driverVehicleDocument->document_number = $documentNumber;
                            }

                            // Handle document file upload if provided
                            if ($documentPath) {
                                $fileExtension = $documentPath->getClientOriginalExtension();
                                $fileName = 'vehicle-document-' . time() . '-' . $documentPath->getClientOriginalName();

                                // Store the file and update the document path in the database
                                $path = Storage::disk('public')->putFileAs('vehicle-document', $documentPath, $fileName);
                                $driverVehicleDocument->document_file_path = $path;
                                $driverVehicleDocument->document_file_type = $fileExtension;
                            }
                            $driverVehicleDocument->document_verification_status = 0;
                            // Save the updated document entry
                            $driverVehicleDocument->save();
                        }
                    }
                }


                //success log
                LogHelper::logSuccess('The partner personal documents updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                //Return Success response
                return response()->json(['status' => 200, 'message' => 'Data updated successfully'], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the partner update data.', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return failure response if creation failed
            return response()->json(['status' => 300, 'message' => 'Partner not found.'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the partner update personal documents.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

            // Catch any exceptions and return an error response 
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }
    //seemashelar@neosao
    //update vehicle data 
    public function vehicle_update(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer',
                'vehicle' => 'required|integer',
                'vehicleNumber' => 'required|min:2|max:15|regex:/^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/',

                // Vehicle Photo validation
                'vehiclePhoto' => 'nullable|file|mimes:jpg,png,jpeg|max:10240', // Photo is now optional for update
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',

                'vehicle.required' => 'The vehicle is required.',
                'vehicle.integer' => 'The vehicle type must be a valid integer.',

                'vehicleNumber.required' => 'The vehicle number is required.',
                'vehicleNumber.min' => 'The vehicle number must be at least 2 characters.',
                'vehicleNumber.max' => 'The vehicle number cannot exceed 15 characters.',
                'vehicleNumber.regex' => 'The vehicle number must be in the format: XX00XX1234.',

                'vehiclePhoto.file' => 'The vehicle photo must be a valid file.',
                'vehiclePhoto.mimes' => 'The vehicle photo must be a file of type: jpg, png, jpeg.',
                'vehiclePhoto.max' => 'The vehicle photo file size must not exceed 10MB.',
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

            if ($r->hasFile('vehiclePhoto')) {
                $file = $r->file('vehiclePhoto');

                // Get file size in bytes
                $fileSize = $file->getSize();

                // Optional: convert to KB or MB
                $sizeInKB = round($fileSize / 1024, 2);
                $sizeInMB = round($fileSize / 1048576, 2);

                // Log it
                Log::info("Vehicle photo uploaded. Size: {$fileSize} bytes ({$sizeInKB} KB / {$sizeInMB} MB)");
            }

            // Check if vehicle details already exist
            $driverDetails = Driver::where('id', $r->driverId)
                ->first();

            if ($driverDetails) {

                $driverDetails->driver_vehicle_verification_status = 1;
                $driverDetails->save();

                $vehicleDetails = DriverVehicleDetails::where('driver_id', $r->driverId)
                    ->first();


                // Update the existing vehicle details record
                $vehicleDetails->vehicle_id = $r->vehicle;
                $vehicleDetails->vehicle_number = $r->vehicleNumber;

                $vehicleDetails->is_active = 1;
                $vehicleDetails->is_delete = 0;

                if ($r->hasFile('vehiclePhoto')) {
                    $file = $r->file('vehiclePhoto');
                    $imageName = 'vehicle-photo-' . time() . '.' . $file->getClientOriginalExtension();
                    $path = Storage::disk('public')->putFileAs('vehicle-photo', $file, $imageName);
                    $vehicleDetails->vehicle_photo = $path;
                }

                $result = $vehicleDetails->save();  // Update existing record

                // Success response
                if ($result) {
                    LogHelper::logSuccess('The partner vehicle details updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                    return response()->json(['status' => 200, 'message' => 'Vehicle details updated successfully.'], 200);
                }

                // If  update fail, return failure response
                LogHelper::logError('An error occurred while update partner vehicle details.', 'Failed to update vehicle details.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                return response()->json(['status' => 300, 'message' => 'Failed to update vehicle details.'], 200);
            }
            //log error
            LogHelper::logError('An error occurred while the partner vehicle details update data.', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return failure response if creation failed
            return response()->json(['status' => 300, 'message' => 'Partner not found.'], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while processing vehicle details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    //seemashelar@neosao
    //driver logout

    public function driver_logout(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer',
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
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
            //driver checking
            $driver = Driver::where("id", $r->driverId)->first();
            if (!empty($driver)) {
                $driver->tokens()->delete();
                //success log 
                LogHelper::logSuccess('The partner logged out successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                //success response
                return response()->json(["status" => 200, "message" => "Logged out."], 200);
            }
            //log error
            LogHelper::logError('An error occurred while the partner logged out.', 'Partner not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(["status" => 300, "message" => "Partner not found."], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while partner logout.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    // seemashelar@neosao
    // Check partner status
    public function check_driver_status(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Vehicle Details
                'driverId' => 'required|integer',
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
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

            // Driver checking
            $driver = Driver::where("id", $r->driverId)
                ->where("is_driver_block", 1)
                ->first();

            if (empty($driver)) {
                // Success log
                LogHelper::logSuccess('The partner status check successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);
                // Success response
                return response()->json(["status" => 200, "message" => "Your account is active."], 200);
            }

            return response()->json(["status" => 300, "message" => "Your account is blocked by admin. Please contact the administrator for further assistance."], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while checking partner status.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    // seemashelar@neosao
    // Check driver online/offline

    public function driver_change_status(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'status' => 'required|in:0,1'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
                'status.required' => 'The status is required.',
                'status.in' => 'The status must be 0 (offline) or 1 (online).'
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

            $driver = Driver::find($input['driverId']);

            if (!$driver) {
                return response()->json([
                    "status" => 404,
                    "message" => "Partner not found."
                ], 200);
            }

            $driver->driver_online_offline_status = $input['status'];
            $driver->save();

            $driverOfflineOnline = new DriverOnlineOffline;
            $driverOfflineOnline->event_at = now();
            $driverOfflineOnline->status = $input['status'];
            $driverOfflineOnline->driver_id = $input['driverId'];
            $driverOfflineOnline->save();

            $message = $input['status'] == 1
                ? "You are now online and ready to accept bookings!"
                : "You are now offline and unavailable for booking requests.";

            return response()->json([
                "status" => 200,
                "message" => $message,
                "data" => [
                    "driverId" => $driver->id,
                    "status" => $driver->driver_online_offline_status
                ]
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while changing partner status.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    // seemashelar@neosao
    // order list

    public function trip_list(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'page' => 'nullable|integer|min:1',
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            $perPage = 10;
            $page = $r->input('page', 1);
            $offset = ($page - 1) * $perPage;

            // Main query
            $query = Trip::with(['vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'sourceAddress', 'destinationAddress'])
                //->where("trip_status", "pending")
                ->where("is_active", 1)
                ->where("is_delete", 0)
                ->where("trip_driver_id", $r->driverId)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('trip_payment_mode', 'online')
                            ->where('trip_payment_status', 'completed');
                    })
                        ->orWhere('trip_payment_status', 'pending');
                });

            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page;
            $offset = ($page - 1) * $perPage;

            $tripDetails = $query->orderBy('id', 'DESC')
                ->skip($offset)
                ->limit($perPage)
                ->get();

            $tripArray = [];
            foreach ($tripDetails as $item) {
                $sourceAddress = [
                    "address" => $item->sourceAddress->customeraddresses_address ?? "",
                    "mobile" => $item->sourceAddress->customeraddresses_mobile ?? "",
                    "type" => $item->sourceAddress->customeraddresses_type ?? "",
                    "name" => $item->customer->customer_first_name . ' ' . $item->customer->customer_last_name ?? "",
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

                $tripArray[] = [
                    "tripId" => $item->id,
                    "TripStatus" => $item->trip_status,
                    "tripFairAmount" => $item->trip_fair_amount,
                    "tripNetFairAmount" => $item->trip_netfair_amount,
                    "tripDiscount" => $item->trip_discount,
                    "tripTotalAmount" => $item->trip_total_amount,
                    "sgstRate" => $item->trip_sgst_rate,
                    "cgstRate" => $item->trip_cgst_rate,
                    "taxAmount" => $item->trip_tax_amount,
                    "tripDate" => $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y h:i:s') : "",
                    "tripPaymentMode" => $item->trip_payment_mode,
                    "vehicleType" => $item->vehicle?->vehicleType?->vehicle_type ?? '',
                    "tripUniqueId" => $item->trip_unique_id,
                    "customerSourceAddress" => $sourceAddress,
                    "customerDestinationAddress" => $destinationAddress,
                ];
            }

            $message = $totalCount > 0 ? "Data found" : "Data not found";

            return response()->json([
                "status" => 200,
                "message" => $message,
                "currentPage" => intval($page),
                "totalPages" => $totalPages,
                "totalCount" => $totalCount,
                "result" => $tripArray
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching trip list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

 public function trip_status_change(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'status' => 'required',
                'tripId' => 'required'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
                'tripId.required' => 'The Trip ID is required.',
                'status.required' => 'Status is required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            $trip = Trip::find($r->tripId);
            // Check if the trip exists
            if (!$trip) {
                //log error
                LogHelper::logError('An error occurred while change status of trip', 'Trip not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return error response
                return response()->json(["status" => 300, "message" => "Trip not found"], 200);
            }
			
			if ($trip->trip_status == "cancelled") {
                return response()->json(["status" => 300, "message" => "Trip is already cancelled."], 200);
            }

            if ($trip->trip_status == "accepted" && $r->status != 'start') {
                return response()->json(["status" => 300, "message" => "Trip is already accepted."], 200);
            }

            if ($trip->trip_status == "start" && in_array($r->status, ['accepted'])) {
                return response()->json(["status" => 300, "message" => "Trip has already started."], 200);
            }

            $trip->trip_status = $r->status;
            $trip->trip_driver_id = $r->driverId;
            $trip->save();

            $tripStatus = new TripStatus;
            $tripStatus->trip_id = $r->tripId;
            $tripStatus->trip_status_short = $r->status;
            $tripStatus->trip_action_by = $r->driverId;
            $tripStatus->trip_action_type = "driver";

            if ($r->status == "accepted") {
                $tripStatus->trip_status_reason = "Trip is accepted by partner";
                $tripStatus->trip_status_title = "accepted";
            }

            if ($r->status == "start") {
                $tripStatus->trip_status_reason = "Trip is started";
                $tripStatus->trip_status_title = "start";
            }

            $tripStatus->save();

            $customer = Customer::select("customer_firebase_token")
                ->where("id", $trip->trip_customer_id)
                ->first();

            if (!empty($customer)) {
                if (!empty($customer->customer_firebase_token)) {
                    if ($r->status == "accepted") {
                        $statusMessage = "Trip is accepted by partner";
                        $title = "Trip Accepted";
                    }
                    if ($r->status == "start") {
                        $statusMessage = "Trip is started";
                        $title = "Trip Start";
                    }

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
                    Log::info("Trip status change notification result", ['result' => $result]);
                }
            }
            if ($r->status == "accepted") {
                return response()->json(["status" => 200, "message" => "Trip has been accepted successfully"], 200);
            } else {
                return response()->json(["status" => 200, "message" => "Trip is now started"], 200);
            }
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while changing change status.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }
  
    public function trip_details(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'tripId' => 'required',
                'driverId' => 'required',
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
                $couponDetails = new \stdClass();
                $vehicle = new \stdClass();
                $customerRatingDetails = new \stdClass(); // Default to an empty object
                $driverRatingDetails = new \stdClass();

                $customerrating = Rating::where("rating_customer_id", $tripDetails->customer->id)
                    ->where("rating_trip_id", $r->tripId)
                    ->where("rating_given_by", "customer")
                    ->first();

                if (!empty($customerrating)) {
                    $customerRatingDetails->ratingId = $customerrating->id;
                    $customerRatingDetails->ratingValue = $customerrating->rating_value;
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
                        "vehicleNumber" => $vehicleDetails->vehicleDetails->vehicle_number ?? "",
                        "vehicleType" => $vehicleDetails->vehicleDetails?->vehicle?->vehicleType?->vehicle_type ?? '',
                        "vehicleName" => $vehicleDetails->vehicleDetails->vehicle->vehicle_name ?? "",
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
                    "tripNetFairAmount" => $tripDetails->trip_netfair_amount,
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
                ];

                return response()->json(['status' => 200, 'message' => 'Data found.', 'result' => $data], 200);
            }

            return response()->json(['status' => 300, 'message' => 'Data not found.'], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching trip details.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    public function trip_end(Request $r)
    {
        try {
            $input = $r->all();
            // Validate the input
            $validator = Validator::make($input, [
                'tripId' => 'required',
                'driverId' => 'required',
                'status' => 'required',
                'paymentType' => 'nullable',
                'paymentStatus' => 'nullable',
                'tripAmount' => 'required',
                'destinationMobileNumber' => 'required',
                'otp' => 'required|numeric|digits:6',
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

            $trip = Trip::find($r->tripId);
            // Check if the trip exists
            if (!$trip) {

                //log error
                LogHelper::logError('An error occurred while change status of trip', 'Trip not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return error response
                return response()->json(["status" => 300, "message" => "Trip not found"], 200);
            }

            // Check if the provided OTP is valid by calling a custom method
            $otpResult = $this->checkCustomerRegisterOTP($r->otp, $r->destinationMobileNumber, "deliver", $r->tripId);

            // If OTP has expired, return an expired message
            if ($otpResult === 'expired') {
                //log error
                LogHelper::logError('An error occurred while the partner verify otp while deliver', 'OTP has been expired.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'OTP has been expired.'
                ];
                return response()->json($response, 200);
            }
            // If OTP is invalid, return an invalid OTP message
            elseif ($otpResult === 'not-found') {

                //log error
                LogHelper::logError('An error occurred while the partner verify otp while deliver', 'Invalid OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'Invalid OTP'
                ];
                return response()->json($response, 200);
            } else {


                // Check if paymentType is "cod" and paymentStatus is not 1
                if ($input['paymentType'] === 'cod' && $input['paymentStatus'] != 1) {
                    return response()->json([
                        "message" => "Please collect cash first as payment type is COD."
                    ], 300);
                }


                $trip->trip_status = $r->status;
                $trip->trip_driver_id = $r->driverId;
                $trip->save();

                $tripStatus = new TripStatus;
                $tripStatus->trip_id = $r->tripId;
                $tripStatus->trip_status_short = $r->status;
                $tripStatus->trip_action_by = $r->driverId;
                $tripStatus->trip_action_type = "driver";
                if ($r->status == "completed") {
                    $tripStatus->trip_status_reason = "Trip is completed by partner";
                    $tripStatus->trip_status_title = "completed";
                }
                $tripStatus->save();

                //if ($input['paymentType'] === 'cod')
                //{
                //driver earning				
                $getAdminCommission = Setting::where("id", 4)->first();
                $commission = 0;

                if (!empty($getAdminCommission)) {
                    $commission = $getAdminCommission->setting_value;
                }

                $adminShare = ($r->tripAmount * $commission) / 100; // Calculate admin's share
                $driverShare = $r->tripAmount - $adminShare;


                $driverEarning = new DriverEarning;
                $driverEarning->trip_id = $r->tripId;
                $driverEarning->driver_id = $r->driverId;
                $driverEarning->type = "deposit";
                $driverEarning->message = "For Trip {$trip->trip_unique_id}: Driver receives ₹{$driverShare} while Admin gets ₹{$adminShare} from total ₹{$r->tripAmount}";
                $driverEarning->amount = $driverShare;
                $driverEarning->status = "success";
                $driverEarning->paymentMode = $r->paymentType;
                $driverEarning->save();

                LogHelper::logSuccess('partner transaction recorded successfully', [
                    'trip_id' => $r->tripId,
                    'driver_id' => $r->driverId,
                    'amount' => $driverShare,
                ], __FUNCTION__, basename(__FILE__), __LINE__);

                // Update driver's wallet
                $driver = Driver::find($r->driverId);
                if ($driver) {

                    if ($input['paymentType'] === 'online') {
                        $driver->driver_wallet += $driverShare;
                        $driver->save();
                    }

                    if ($input['paymentType'] === 'cod') {
                        $driver->driver_wallet -= $adminShare;
                        $driver->save();
                    }

                    //driver wallet
                    /*$driverEarning=new DriverEarning;
						$driverEarning->trip_id=$r->tripId;
						$driverEarning->driver_id=$r->driverId;
						$driverEarning->type="debit";
						$driverEarning->message=$adminShare.' amount is deduct against trip - ' . $trip->trip_unique_id;
						$driverEarning->amount=$adminShare;
						$driverEarning->status="success";
						$driverEarning->paymentMode=$input['paymentType'];
						$driverEarning->save(); */

                    LogHelper::logSuccess('partner wallet updated successfully', [
                        'driver_id' => $r->driverId,
                        'updated_wallet_amount' => $driver->driver_wallet,
                    ], __FUNCTION__, basename(__FILE__), __LINE__);
                }

                //admin commission

                $adminTransaction = new AdminCommission;
                $adminTransaction->trip_id = $r->tripId;
                $adminTransaction->driver_id = $r->driverId;
                $adminTransaction->commission_percentage = $commission;
                $adminTransaction->type = "deposit";
                $adminTransaction->subtotal = $trip->trip_fair_amount;
                $adminTransaction->commission_amount = $adminShare;
                $adminTransaction->grand_total = $trip->trip_total_amount;
                $adminTransaction->save();


                LogHelper::logSuccess('Admin commission recorded successfully', [
                    'trip_id' => $r->tripId,
                    'commission_percentage' => $commission,
                    'commission_amount' => $adminShare,
                ], __FUNCTION__, basename(__FILE__), __LINE__);


                //send notification to customer 

                $customer = Customer::find($trip->trip_customer_id);
                if (!empty($customer)) {
                    if (!empty($customer->customer_firebase_token)) {
                        $statusMessage = "Trip completed successfully.";
                        $title = "Trip completed";

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
                        Log::info("Trip completed notification result", ['result' => $result]);
                    }
                }

                //}
                return response()->json(["status" => 200, "message" => "Trip is successfully completed."], 200);
            }
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching trips end.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }


    public function  driver_home_screen(Request $r)
    {
        try {
            $input = $r->all();
            // Validate the input
            $validator = Validator::make($input, [
                'driverId' => 'required'
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

            $driver = Driver::find($r->driverId);
            // Check if the trip exists
            if (!$driver) {

                //log error
                LogHelper::logError('An error occurred while getting wallet amount of partner', 'Partner not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return error response
                return response()->json(["status" => 300, "message" => "Partner not found"], 200);
            }
            $todaysEarning = DriverEarning::whereDate('created_at', now())
                ->where("type", "deposit")
                ->sum('amount');
            $completedTrip = Trip::where("trip_driver_id", $r->driverId)
                ->where("is_active", 1)
                ->where("is_delete", 0)
                ->where("trip_status", "completed")
                ->count();

            $missedTrip = Trip::where("trip_driver_id", $r->driverId)
                ->where("trip_status", "cancelled")
                ->count();
            $data = [
                "walletBalance" => $driver->driver_wallet ?? "0",
                "todaysEarning" => $todaysEarning,
                "completedTrip" => $completedTrip,
                "missedTrip" => $missedTrip
            ];
            return response()->json(["status" => 200, "message" => "Data found", "result" => $data], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching wallet amount.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }


    public function customer_rating(Request $r)
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
            $rating->rating_given_by = "driver";
            $rating->save();

            return response()->json(['status' => 200, 'message' => 'Rating has been given successfully.'], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while star rating to customer.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }



    public function earning_list(Request $r)
    {
        try {
            $input = $r->all();

            // Validate the input
            $validator = Validator::make($input, [
                'startDate' => 'required',
                'endDate' => 'required',
                'driverId' => 'required|exists:drivers,id',
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


            $driver = Driver::find($r->driverId);
            // Check if the trip exists
            if (!$driver) {

                //log error
                LogHelper::logError('An error occurred while getting wallet amount of partner', 'Partner not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return error response
                return response()->json(["status" => 300, "message" => "Partner not found"], 200);
            }

            $startDate = date('Y-m-d', strtotime(str_replace("/", "-", $r->startDate)));
            $endDate = date('Y-m-d', strtotime(str_replace("/", "-", $r->endDate)));
            $driverId = $input['driverId'];

            // Fetch both online and offline events for the driver within the date range
            $events = DriverOnlineOffline::where('driver_id', $driverId)
                ->whereDate('event_at', '>=', $startDate)
                ->whereDate('event_at', '<=', $endDate)
                ->whereIn('status', ['1', '0']) // Fetch both online (1) and offline (0) events
                ->orderBy('event_at')
                ->get();

            $totalTime = 0; // Initialize totalTime to 0
            $lastOnlineTime = null;

            foreach ($events as $event) {
                // Log the event and its timestamp conversion
                Log::info('Event:', ['event_at' => $event->event_at, 'status' => $event->status]);
                $eventTimestamp = strtotime($event->event_at);
                Log::info('Converted Event Time:', ['timestamp' => $eventTimestamp, 'event_at' => $event->event_at]);

                if ($event->status === 1) { // Online event
                    $lastOnlineTime = $event->event_at;
                    Log::info('Last Online Time:', ['event_at' => $lastOnlineTime]);
                } elseif ($event->status === 0 && $lastOnlineTime) { // Offline event and online event exists
                    // Convert both times to timestamps
                    $offlineEventTime = strtotime($event->event_at);
                    $onlineEventTime = strtotime($lastOnlineTime);

                    // Log the comparison of offline vs online event times
                    Log::info('Checking Offline vs Online:', [
                        'offline_time' => $offlineEventTime,
                        'online_time' => $onlineEventTime,
                        'event_diff' => $offlineEventTime - $onlineEventTime // Log the time difference directly
                    ]);

                    // Ensure the offline time is after the online time
                    if ($offlineEventTime > $onlineEventTime) {
                        $timeDiff = $offlineEventTime - $onlineEventTime;
                        $totalTime += $timeDiff;

                        // Log the accumulated time difference
                        Log::info('Accumulated Time Diff:', ['total_time' => $totalTime]);
                    } else {
                        Log::warning('Offline event time is earlier than the online event time', [
                            'online_time' => $lastOnlineTime,
                            'offline_time' => $event->event_at
                        ]);
                    }

                    // Reset lastOnlineTime after calculating the duration
                    $lastOnlineTime = null;
                }
            }

            // If the user is still online and no "offline" event exists, calculate time from the last online event to the current time
            if ($lastOnlineTime) {
                $currentTime = strtotime(now()); // Current time as timestamp
                $lastOnlineTimestamp = strtotime($lastOnlineTime); // Last online event timestamp

                // Calculate the time difference between the last online event and the current time
                if ($currentTime > $lastOnlineTimestamp) {
                    $timeDiff = $currentTime - $lastOnlineTimestamp;
                    $totalTime += $timeDiff;
                    Log::info('Ongoing Online Event: Time Diff from Last Online to Current Time', ['time_diff' => $timeDiff]);
                }
            }

            // Convert total time to hours, minutes, and seconds
            $hours = floor($totalTime / 3600);
            $minutes = floor(($totalTime % 3600) / 60);
            $seconds = $totalTime % 60;

            // Format the time as HH:MM:SS
            $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            // Fetch the total earnings from startDate to endDate
            $totalEarning = DriverEarning::where("driver_id", $r->driverId)
                ->where("type", "deposit")
                ->where(function ($q) {
                    $q->whereNull('added_from')
                        ->orWhereNotIn('added_from', ['wallet']);
                })
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('amount');

            // Fetch the completed trips count from startDate to endDate
            $completedTrip = Trip::where("trip_driver_id", $r->driverId)
                ->where("trip_status", "completed")
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where("is_active", 1)
                ->where("is_delete", 0)
                ->count();
            $data = [
                "totalTime" => $formattedTime,
                "totalEarning" => $totalEarning,
                "walletBalance" => $driver->driver_wallet ?? "0",
                "completedTrip" => $completedTrip
            ];

            return response()->json([
                'status' => 200,
                'message' => "Data found",
                'result' => $data
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while calculating total time.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    public function earning_trip_list(Request $r)
    {
        try {
            $input = $r->all();

            // Validate the input
            $validator = Validator::make($input, [
                'startDate' => 'required',
                'endDate' => 'required',
                'driverId' => 'required|exists:drivers,id',
                'page' => 'nullable|integer|min:1', // Add page validation
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

            // Get the input values
            $startDate = date('Y-m-d', strtotime(str_replace("/", "-", $r->startDate)));
            $endDate = date('Y-m-d', strtotime(str_replace("/", "-", $r->endDate)));
            $driverId = $input['driverId'];
            $perPage = 10; // Set perPage to 10 for pagination
            $page = $r->input('page', 1); // Default to page 1 if no page is provided

            // Query to get trip details within the date range
            $query = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'sourceAddress', 'destinationAddress')
                ->where("trip_status", "completed")
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where("is_active", 1)
                ->where("is_delete", 0);

            // Count total number of trips for pagination
            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page;
            $offset = ($page - 1) * $perPage;

            // Fetch paginated trip results
            $tripDetails = $query->skip($offset)
                ->take($perPage)
                ->get();

            $tripArray = [];
            if ($tripDetails && count($tripDetails) > 0) {

                foreach ($tripDetails as $item) {
                    $sourceAddress = [
                        "address" => $item->sourceAddress->customeraddresses_address ?? "",
                        "mobile" => $item->sourceAddress->customeraddresses_mobile ?? "",
                        "type" => $item->sourceAddress->customeraddresses_type ?? "",
                        "name" => $item->customer->customer_first_name . ' ' . $item->customer->customer_last_name ?? "",
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


                    $data = [
                        "tripFairAmount" => $item->trip_fair_amount,
                        "tripNetfairAmount" => $item->trip_netfair_amount,
                        "tripDiscount" => $item->trip_discount,
                        "tripTotalAmount" => $item->trip_total_amount,
                        "sgstRate" => $item->trip_sgst_rate,
                        "cgstRate" => $item->trip_cgst_rate,
                        "taxAmount" => $item->trip_tax_amount,
                        "tripDate" => $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y h:i:s') : "",
                        "tripUniqueId" => $item->trip_unique_id,
                        "customerSourceAddress" => $sourceAddress,
                        "customerDestinationAddress" => $destinationAddress,
                    ];
                    array_push($tripArray, $data);
                }

                return response()->json([
                    "status" => 200,
                    "message" => "Data found",
                    "currentPage" => intval($page),
                    "totalPages" => $totalPages,
                    "totalCount" => $totalCount,
                    "result" => $tripArray,
                ], 200);
            }

            return response()->json([
                "status" => 300,
                "message" => "Data not found"
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching trip list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    public function cancel_reason(Request $r)
    {
        try {
            $reasonArray = DriverRejectionReason::where("is_delete", 0)
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
                'driverId' => 'required|exists:drivers,id',
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

            $driver = Driver::find($input['driverId']);

            if (!$driver) {
                return response()->json([
                    'status' => 300,
                    'message' => 'Partner not found'
                ], 200);
            }

            $trip = Trip::find($input['tripId']);

            if (!$trip) {
                return response()->json([
                    'status' => 300,
                    'message' => 'Trip not found'
                ], 200);
            }

            $trip = Trip::find($input['tripId']);
            $trip->trip_status = "pending";
            $trip->save();

            //save trip status in trip status				
            $tripStatus = new TripStatus;
            $tripStatus->trip_id = $trip->id;
            $tripStatus->trip_status_title = "cancelled";
            $tripStatus->trip_status_description = "Trip is cancelled by partner.";
            $tripStatus->trip_status_reason = $r->cancelReason;
            $tripStatus->trip_status_short = "cancelled";
            $tripStatus->trip_action_by = $r->driverId;
            $tripStatus->trip_action_type = "partner";
            $tripStatus->save();

            $customer = Customer::select("customer_firebase_token")
                ->where("id", $trip->trip_customer_id)
                ->first();

            if (!empty($customer)) {
                if (!empty($customer->customer_firebase_token)) {

                    $statusMessage = "Trip is cancelled by partner";
                    $title = "Trip Cancelled";

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
                    Log::info("Trip cancel status change notification result", ['result' => $result]);
                }
            }


            return response()->json([
                'status' => 200,
                'message' => 'Trip cancel successfully'
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while partner cancel trip .', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
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
                'driverId' => 'required',
                'firebaseId' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 200);
            }

            $driverdata = [
                'driver_firebase_token' => $r->firebaseId,
            ];
            $result = Driver::where('id', $r->driverId)->update($driverdata);

            if ($result == true) {

                LogHelper::logSuccess(
                    "partner firebase update successfully.",
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    __FILE__,
                    $r->driverId
                );
                return response()->json(["status" => 200, "message" => "partner firebase update Successfully."], 200);
            } else {
                return response()->json(["status" => 300, "message" => " Failed to update partner firebase."], 200);
            }
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while partner update firebase token .', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong'
            ], 400);
        }
    }

    // driver ledger balance and get list of ledger balance

    public function ledger_balance(Request $r)
    {
        try {
            $data = [];
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'page' => 'required|integer',
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


            $driver = Driver::find($r->driverId);
            // Check if the trip exists
            if (!$driver) {

                //log error
                LogHelper::logError('An error occurred while getting ledger of the partner', 'Partner not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                // Return error response
                return response()->json(["status" => 300, "message" => "partner not found"], 200);
            }


            $driverId = $r->input('driverId');
            $perPage = 10;
            $page = $r->input('page', 1);

            // Query DriverEarnings with Driver and Trip data
            $query = DriverEarning::with(['driver:id,driver_first_name,driver_last_name', 'trip:id,trip_unique_id'])
                ->where('driver_id', $driverId)
                ->where('is_delete', 0);

            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page;
            $offset = ($page - 1) * $perPage;

            $earnings = $query->orderBy('id', 'DESC')
                ->skip($offset)
                ->limit($perPage)
                ->get();

            $result = $earnings->map(function ($earning) {
                return [
                    'id' => $earning->id,
                    'driverId' => $earning->driver->id,
                    'driverName' => $earning->driver->driver_first_name . " " . $earning->driver->driver_last_name,
                    'tripUniqueId' => $earning->trip->trip_unique_id ?? "",
                    'type' => $earning->type,
                    'message' => $earning->message,
                    'amount' => $earning->amount,
                    'status' => $earning->status,
                    'paymentMode' => $earning->paymentMode ?? "",
                ];
            });

            return response()->json([
                "message" => $totalCount > 0 ? 'Data Found' : 'No Data Found',
                "currentPage" => intval($page),
                "totalPages" => $totalPages,
                "totalCount" => $totalCount,
                "balance" => $driver->driver_wallet,
                "data" => $result,

            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while fetching the ledger balance.', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong'
            ], 400);
        }
    }


    public function withdrawal_request(Request $r)
    {
        try {
            $input = $r->all();

            $validator = Validator::make($input, [
                'driverId' => 'required'
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


            // Check for existing pending withdrawal
            $existingPending = DriverEarning::where('driver_id', $r->driverId)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->first();

            if ($existingPending) {
                return response()->json([
                    'status' => 300,
                    'message' => 'A pending withdrawal request already exists.'
                ], 200);
            }

            $driver = Driver::find($input['driverId']);

            if (!$driver) {
                return response()->json([
                    'status' => 300,
                    'message' => 'Partner not found'
                ], 200);
            }

            $miniBalance = "0";
            $getMinimumBalance = Setting::where("id", 9)->first();
            if (!empty($getMinimumBalance)) {
                $miniBalance = $getMinimumBalance->setting_value;
            }

            // Assuming the driver's wallet balance is stored in $driver->driver_wallet
            /*if ($driver->driver_wallet < $miniBalance) {
				return response()->json([
					'status' => 300,
					'message' => 'Insufficient wallet balance to make this withdrawal request.'
				], 200);
			}*/

            $withdrawAmount = $driver->driver_wallet - $miniBalance;

            $driverEarning = new DriverEarning;
            $driverEarning->driver_id = $r->driverId;
            $driverEarning->type = "withdrawal";
            $driverEarning->message = "Partner requested Rs. " . $withdrawAmount . " amount for withdrawal";
            $driverEarning->amount = $withdrawAmount;
            $driverEarning->minimum_wallet_amount = $miniBalance;
            $driverEarning->status = "pending";
            $driverEarning->save();

            return response()->json(["status" => 200, "message" => "Withdrawal request is added successfully."], 200);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while request wallet.', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong'
            ], 400);
        }
    }


    public function driver_transaction_list(Request $r)
    {
        try {
            $input = $r->all();

            // Validate the input
            $validator = Validator::make($input, [
                'driverId' => 'required',
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

            $driver = Driver::find($input['driverId']);

            if (!$driver) {
                return response()->json([
                    'status' => 300,
                    'message' => 'Partner not found'
                ], 200);
            }

            $miniBalance = "0";
            $getMinimumBalance = Setting::where("id", 9)->first();
            if (!empty($getMinimumBalance)) {
                $miniBalance = $getMinimumBalance->setting_value;
            }

            // Get the input values
            $page = $r->input('page', 1); // Default to page 1 if no page is provided
            $perPage = 10; // Set perPage to 10 for pagination

            // Start building the query for transactions
            $query = DriverEarning::where('driver_id', $input['driverId'])
                ->where('is_delete', 0)
                ->whereIn('status', ['success', 'completed', 'approved', 'rejected']);
            // Count total number of transactions for pagination
            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perPage);
            $page = $page > $totalPages ? max(1, $totalPages) : $page;
            $offset = ($page - 1) * $perPage;

            // Fetch paginated transaction results
            $transactionList = $query->skip($offset)
                ->take($perPage)
                ->orderBy("id", "DESC")
                ->get();

            $transactionArray = [];
            if ($transactionList && count($transactionList) > 0) {
                foreach ($transactionList as $transaction) {
                    $data = [
                        "tripId" => strval($transaction->trip_id) ?? "",
                        "driverId" => $transaction->driver_id,
                        "type" => $transaction->type,
                        "message" => $transaction->message,
                        "amount" => $transaction->amount,
                        "status" => $transaction->status,
                        "paymentMode" => $transaction->paymentMode,
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
                    "walletBalance" => $driver->driver_wallet ?? 0,
                    "minimumBalance" => $miniBalance,
                    "result" => $transactionArray,
                ], 200);
            }

            return response()->json([
                "status" => 300,
                "message" => "Data not found",
                "minimumBalance" => $miniBalance,
            ], 200);
        } catch (\Exception $e) {
            LogHelper::logError('An error occurred while fetching transaction list.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong'
            ], 400);
        }
    }


    //seemashelar@neosao
    //verify otp with mobile number & trip while pickup

    public function verify_pickup_otp(Request $r)
    {
        try {
            // Get all input data from the request
            $input = $r->all();

            // Validate the mobile number and OTP to ensure they are both present and in the correct format
            $validator = Validator::make($input, [
                'tripId' => 'required',
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
            $otpResult = $this->checkCustomerRegisterOTP($r->otp, $r->mobileNumber, "pickup", $r->tripId);

            // If OTP has expired, return an expired message
            if ($otpResult === 'expired') {
                //log error
                LogHelper::logError('An error occurred while the partner verify otp while pickup', 'OTP has been expired.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'OTP has been expired.'
                ];
                return response()->json($response, 200);
            }
            // If OTP is invalid, return an invalid OTP message
            elseif ($otpResult === 'not-found') {

                //log error
                LogHelper::logError('An error occurred while the partner verify otp while pickup', 'Invalid OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'Invalid OTP'
                ];
                return response()->json($response, 200);
            } else {
                //success log 
                LogHelper::logSuccess('The partner verify otp successfully while pickup.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

                // Return success response with user data and token 
                return response()->json(['status' => 200, 'message' => 'OTP verified successfully'], 200);
            }
        } catch (\Exception $e) {

            //log error
            LogHelper::logError('An error occurred while the partner verify otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch any exceptions and return an error response
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    public function send_pickup_otp(Request $r)
    {
        try {
            // Get all input data
            $input = $r->all();

            // Validate mobile number
            $validator = Validator::make($input, [
                'tripId' => 'required',
                'mobileNumber' => ['required', 'digits:10', 'numeric']
            ], [
                'tripId.required' => 'Trip is required.',
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
            $otpNumber = $this->generateCustomerOTP($r->mobileNumber, 'pickup', $r->tripId);

            // Check if OTP generation is successful
            if ($otpNumber != false) {
                // Optional: Skip sending OTP for specific number
                if ($r->mobileNumber != "8482940592") {

                    $otpResult = $this->pickupotp($r->mobileNumber, $otpNumber);
                    if ($otpResult["status"] == "failed") {
                        return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
                    }
                }

                //success log
                LogHelper::logSuccess('The customer pickup otp send successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

                // Return success response
                return response()->json(['status' => 200, 'message' => 'OTP sent successfully'], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the customer pickup send otp', 'Failed to send OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return failure response if OTP could not be generated
            return response()->json(['status' => 300, 'message' => 'Failed to send OTP.'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the customer pickup send otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch and return error in case of exception
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }


    //seemashelar@neosao
    //verify otp with mobile number & trip while pickup

    public function verify_deliver_otp(Request $r)
    {
        try {
            // Get all input data from the request
            $input = $r->all();

            // Validate the mobile number and OTP to ensure they are both present and in the correct format
            $validator = Validator::make($input, [
                'tripId' => 'required',
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
            $otpResult = $this->checkCustomerRegisterOTP($r->otp, $r->mobileNumber, "deliver", $r->tripId);

            // If OTP has expired, return an expired message
            if ($otpResult === 'expired') {
                //log error
                LogHelper::logError('An error occurred while the partner verify otp while deliver', 'OTP has been expired.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'OTP has been expired.'
                ];
                return response()->json($response, 200);
            }
            // If OTP is invalid, return an invalid OTP message
            elseif ($otpResult === 'not-found') {

                //log error
                LogHelper::logError('An error occurred while the partner verify otp while deliver', 'Invalid OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                $response = [
                    'status' => 300,
                    'message' => 'Invalid OTP'
                ];
                return response()->json($response, 200);
            } else {
                //success log 
                LogHelper::logSuccess('The partner verify otp successfully while pickup.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);




                // Return success response with user data and token 
                return response()->json(['status' => 200, 'message' => 'OTP verified successfully'], 200);
            }
        } catch (\Exception $e) {

            //log error
            LogHelper::logError('An error occurred while the partner verify otp while deliver.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch any exceptions and return an error response
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }


    public function send_deliver_otp(Request $r)
    {
        try {
            // Get all input data
            $input = $r->all();

            // Validate mobile number
            $validator = Validator::make($input, [
                'tripId' => 'required',
                'mobileNumber' => ['required', 'digits:10', 'numeric']
            ], [
                'tripId.required' => 'Trip is required.',
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
            $otpNumber = $this->generateCustomerOTP($r->mobileNumber, 'deliver', $r->tripId);

            // Check if OTP generation is successful
            if ($otpNumber != false) {
                // Optional: Skip sending OTP for specific number
                if ($r->mobileNumber != "8482940592") {
                    $otpResult = $this->deliverotp($r->mobileNumber, $otpNumber);
                    if ($otpResult["status"] == "failed") {
                        return response()->json(["status" => 300, "message" => 'Failed to send OTP'], 200);
                    }
                }

                //success log
                LogHelper::logSuccess('The customer deliver otp send successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

                // Return success response
                return response()->json(['status' => 200, 'message' => 'OTP sent successfully'], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the customer deliver send otp', 'Failed to send OTP.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            // Return failure response if OTP could not be generated
            return response()->json(['status' => 300, 'message' => 'Failed to send OTP.'], 200);
        } catch (\Exception $e) {
            //log error
            LogHelper::logError('An error occurred while the customer deliver send otp.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->mobileNumber);

            // Catch and return error in case of exception
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }


    public function check_driver_balance(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            // Check if driver exists
            $driver = Driver::find($r->driverId);

            if (!$driver) {
                return response()->json([
                    "status" => 300,
                    "message" => "Partner not found."
                ], 200);
            }

            $miniBalance = "0";
            $getMinimumBalance = Setting::where("id", 9)->first();
            if (!empty($getMinimumBalance)) {
                $miniBalance = $getMinimumBalance->setting_value;
            }

            // Check partner's wallet balance
            /*if ($driver->driver_wallet < $miniBalance) {
				// Log success
				LogHelper::logSuccess('The partner wallet balance check was successful.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId);

				return response()->json([
					"status" => 300,
					"message" => "Your account has insufficient balance. Please add funds to your wallet.",
					"data"=>$miniBalance
				], 200);
			}*/

            // Wallet balance is sufficient
            return response()->json([
                "status" => 200,
                "message" => "Partner wallet balance is sufficient.",
                "data" => $miniBalance
            ], 200);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError('An error occurred while checking the partner wallet balance.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId ?? 'N/A');

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong.'
            ], 400);
        }
    }


    public function add_money_wallet(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'amount' => 'required|numeric|min:1'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
                'amount.required' => 'The amount is required.',
                'amount.numeric' => 'The amount must be a valid number.',
                'amount.min' => 'The amount must be at least ₹1.',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            // Check if driver exists
            $driver = Driver::find($r->driverId);

            if (!$driver) {
                return response()->json([
                    "status" => 300,
                    "message" => "Partner not found."
                ], 200);
            }

            //driver wallet
            $driverEarning = new DriverEarning;
            $driverEarning->driver_id = $r->driverId;
            $driverEarning->type = "deposit";
            $driverEarning->message = $r->amount . ' amount is added in wallet';
            $driverEarning->amount = $r->amount;
            $driverEarning->status = "pending";
            $driverEarning->added_from = "wallet";
            $driverEarning->paymentMode = "online";
            $driverEarning->payment_status = "pending";
            $driverEarning->payment_id = "TX" . (int)(microtime(true) * 1000);
            $driverEarning->save();

            LogHelper::logSuccess('Driver wallet updated successfully', [
                'driver_id' => $r->driverId,
                'updated_wallet_amount' => $driver->driver_wallet,
            ], __FUNCTION__, basename(__FILE__), __LINE__);

            $phonepe = new DriverPhonePe;
            $phonepeResult = $phonepe->create_phonepe_order($r->amount, $driverEarning->payment_id, 'driver-wallet-add', $r->driverId);
            if (isset($phonepeResult["success"])) {
                if ($phonepeResult["success"] == true) {
                    $paymentData = [
                        "payment_response" => $phonepeResult,
                        "payment_order_id" => $phonepeResult["orderId"],
                    ];
                    $tripUpdate = $driverEarning->update($paymentData);

                    // Prepare the result response
                    $resultData = [
                        'driverId' => $r->driverId,
                        'driverEarningId' => $driverEarning->id,
                        'phonepe' => $phonepeResult,
                        'merchantOrderId' => $driverEarning->payment_id
                    ];

                    return response()->json([
                        'status' => 200,
                        'message' => 'Money has been added to the wallet successfully.',
                        'result' => $resultData
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 300,
                        'message' => 'Adding money failed. Please try again later.'
                    ], 200);
                }
            }
            return response()->json([
                'status' => 300,
                'message' => 'Adding money failed. Please try again later.'
            ], 200);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError('An error occurred while adding money to driver wallet balance.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $r->driverId ?? 'N/A');

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong.'
            ], 400);
        }
    }

    public function check_order_status(Request $r)
    {

        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                'driverId' => 'required|integer',
                'merchantOrderId' => 'required'
            ], [
                'driverId.required' => 'The partner ID is required.',
                'driverId.integer' => 'The partner ID must be a valid integer.',
                'merchantOrderId.required' => 'The merchant order id is required.'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ], 200);
            }

            // Check if partner exists
            $driver = Driver::find($r->driverId);

            if (!$driver) {
                return response()->json([
                    "status" => 300,
                    "message" => "Partner not found."
                ], 200);
            }

            if ($validator->fails()) {
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 200);
            }

            $phonepe = new DriverPhonePe;
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

                /*$driverEarning = DriverEarning::where("payment_id",$r->merchantOrderId)->first();					
				$paymentData=["payment_status"=>strtolower($status),"status"=>strtolower($status)];
				$driverEarning->update($paymentData);
				
				if($status=="COMPLETED"){
					$driver->driver_wallet += $driverEarning->amount;
					$driver->save(); 
				}*/
                LogHelper::logSuccess('Partner wallet updated successfully', [
                    'driver_id' => $r->driverId,
                    'updated_wallet_amount' => $driver->driver_wallet,
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


    public function check_user_status(Request $r)
    {
        try {
            //return response()->json(["status" => 300, "message" => "Maintenance Notice: This app is currently under maintenance and will be unavailable for the next few days. Thank you for your patience."], 200);
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
            $driver = Driver::where("is_driver_block", 1)
                ->where("is_delete", 0)
                ->where("driver_phone", $r->mobileNumber)
                ->first();
            if (!empty($driver)) {
                return response()->json(["status" => 300, "message" => "Your account is blocked by admin.Please contact with administrator for further process."], 200);
            }
            return response()->json(["status" => 200, "message" => "No Found"], 200);
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }
}
