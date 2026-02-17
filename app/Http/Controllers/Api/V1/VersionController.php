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
use App\Models\Setting;

class VersionController extends Controller
{
    //seemashelar@neosao
    //driver version update
    public function update_driver_version(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Setting Details
                'settingId' => 'required',
                'settingValue' => 'required',
                'isUpdateCompulsory' => 'required'
            ], [
                'settingId.required' => 'The setting ID is required.',
                'settingId.integer' => 'The driver ID must be a valid integer.',
                'settingValue.required' => 'The setting value is required',
                'isUpdateCompulsory.required' => 'The Update value is required either 0 or 1.',
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
            //get setting value
            $result = Setting::where("id", $r->settingId)->first();

            if (!empty($result)) {
                $result->setting_value = $r->settingValue;
                $result->is_update_compulsory = $r->isUpdateCompulsory;
                $result->save();

                //success log 
                LogHelper::logSuccess('The driver app version updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
                //success response
                return response()->json(["status" => 200, "message" => "App version updated successfully."], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the driver app version update.', 'Failed to update app version.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(["status" => 300, "message" => "Failed to update app version."], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while driver app version update.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    //seemashelar@neosao
    //driver get version

    public function get_driver_version(Request $r)
    {
        try {
            $data = [];
            //get ios version
            $settingIOS = Setting::select("settings.*")
                ->where("settings.id", "1")
                ->first();
            //get android version
            $settingAndroid = Setting::select("settings.*")
                ->where("settings.id", "2")
                ->first();
            if (!empty($settingAndroid)) {
                $android = [
                    "settingId" => $settingAndroid->id,
                    "settingName" => $settingAndroid->setting_name,
                    "settingValue" => $settingAndroid->setting_value,
                    "isUpdateCompulsory" => $settingAndroid->is_update_compulsory
                ];
                $data["android"] = $android;
            }
            if (!empty($settingIOS)) {
                $ios = [
                    "settingId" => $settingIOS->id,
                    "settingName" => $settingIOS->setting_name,
                    "settingValue" => $settingIOS->setting_value,
                    "isUpdateCompulsory" => $settingIOS->is_update_compulsory
                ];
                $data["ios"] = $ios;
            }
            //success log 
            LogHelper::logSuccess('the driver app version get successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            //success response

            return response()->json(["status" => 200, "message" => "Data found", "result" => $data], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while get driver app version.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }
	
	//seemashelar@neosao
    //customer version update
    public function update_customer_version(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Setting Details
                'settingId' => 'required',
                'settingValue' => 'required',
                'isUpdateCompulsory' => 'required'
            ], [
                'settingId.required' => 'The setting ID is required.',
                'settingId.integer' => 'The driver ID must be a valid integer.',
                'settingValue.required' => 'The setting value is required',
                'isUpdateCompulsory.required' => 'The Update value is required either 0 or 1.',
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
            //get setting value
            $result = Setting::where("id", $r->settingId)->first();

            if (!empty($result)) {
                $result->setting_value = $r->settingValue;
                $result->is_update_compulsory = $r->isUpdateCompulsory;
                $result->save();

                //success log 
                LogHelper::logSuccess('The customer app version updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
                //success response
                return response()->json(["status" => 200, "message" => "App version updated successfully."], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the customer app version update.', 'Failed to update app version.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(["status" => 300, "message" => "Failed to update app version."], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while customer app version update.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }

    //seemashelar@neosao
    //customer get version

    public function get_customer_version(Request $r)
    {
        try {
            $data = [];
            //get ios version
            $settingIOS = Setting::select("settings.*")
                ->where("settings.id", "7")
                ->first();
            //get android version
            $settingAndroid = Setting::select("settings.*")
                ->where("settings.id", "8")
                ->first();
            if (!empty($settingAndroid)) {
                $android = [
                    "settingId" => $settingAndroid->id,
                    "settingName" => $settingAndroid->setting_name,
                    "settingValue" => $settingAndroid->setting_value,
                    "isUpdateCompulsory" => $settingAndroid->is_update_compulsory
                ];
                $data["android"] = $android;
            }
            if (!empty($settingIOS)) {
                $ios = [
                    "settingId" => $settingIOS->id,
                    "settingName" => $settingIOS->setting_name,
                    "settingValue" => $settingIOS->setting_value,
                    "isUpdateCompulsory" => $settingIOS->is_update_compulsory
                ];
                $data["ios"] = $ios;
            }
            //success log 
            LogHelper::logSuccess('the customer app version get successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            //success response

            return response()->json(["status" => 200, "message" => "Data found", "result" => $data], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while get customer app version.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
        }
    }
	
	
}
