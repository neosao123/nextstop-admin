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
use App\Models\DriverEarning;
use App\Models\DriverWallet;
use App\Models\AdminCommission;
use App\Models\Trip;
use App\Models\TripStatus;
use App\Models\Driver;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
class WebhookController extends Controller
{
	public function verify_payment(Request $r){
		try{
			$finalResult = $r->all();
			Log::info("Payment Response".json_encode($finalResult));

			if($finalResult["type"]=="CHECKOUT_ORDER_COMPLETED"){
				$paymentOrderId = $finalResult["payload"]["merchantOrderId"];
				$state = $finalResult["payload"]["state"];

				if($finalResult["payload"]["metaInfo"]["udf1"]=="customer-trip-book") {
					$trip = Trip::where("trip_payment_id", $paymentOrderId)->first();
					if($trip){
						$paymentData = ["trip_payment_status" => strtolower($state), "webhook_response" => $finalResult];
						$trip->update($paymentData);

						LogHelper::logSuccess('Trip updated on success', $paymentData, __FUNCTION__, basename(__FILE__), __LINE__);
					}
				}

				if($finalResult["payload"]["metaInfo"]["udf1"]=="customer-wallet-add") {
					$customer = Customer::find($finalResult["payload"]["metaInfo"]["udf2"]);
					if($customer){
						$transaction = CustomerWalletTransaction::where("payment_id", $paymentOrderId)->first();
						if($transaction){
							$paymentData = ["payment_status" => strtolower($state), "status" => strtolower($state), "webhook_response" => $finalResult];
							$transaction->update($paymentData);

							if($state == "COMPLETED"){
								$customer->customer_wallet_balance += $transaction->amount;
								$customer->save();
							}
							LogHelper::logSuccess('Customer wallet updated on success', $paymentData, __FUNCTION__, basename(__FILE__), __LINE__);
						}
					}
				}

				if($finalResult["payload"]["metaInfo"]["udf1"]=="driver-wallet-add") {
					$driver = Driver::find($finalResult["payload"]["metaInfo"]["udf2"]);
					if($driver){
						$driverEarning = DriverEarning::where("payment_id", $paymentOrderId)->first();
						$paymentDataDriver = ["payment_status" => strtolower($state), "status" => strtolower($state), "webhook_response" => $finalResult];
						$driverEarning->update($paymentDataDriver);

						if($state == "COMPLETED"){
							$driver->driver_wallet += $driverEarning->amount;
							$driver->save();
						}
						LogHelper::logSuccess('Driver wallet updated on success', $paymentDataDriver, __FUNCTION__, basename(__FILE__), __LINE__);
					}
				}

				return response()->json(["status" => 200, "message" => "Trip is successfully completed."], 200);
			}

			else if($finalResult["type"]=="CHECKOUT_ORDER_FAILED") {
				$paymentOrderId = $finalResult["payload"]["merchantOrderId"];
				$state = "FAILED";
				
                $trip = Trip::where("trip_payment_id", $paymentOrderId)->first();
				if($finalResult["payload"]["metaInfo"]["udf1"]=="customer-trip-book") {
					
					if($trip){
						$paymentData = ["trip_payment_status" => strtolower($state), "webhook_response" => $finalResult];
						$trip->update($paymentData);

						LogHelper::logSuccess('Trip updated on failed', $paymentData, __FUNCTION__, basename(__FILE__), __LINE__);
					}
				}

				if($finalResult["payload"]["metaInfo"]["udf1"]=="customer-wallet-add") {
					$customer = Customer::find($finalResult["payload"]["metaInfo"]["udf2"]);
					if($customer){
						$transaction = CustomerWalletTransaction::where("payment_id", $paymentOrderId)->first();
						if($transaction){
							$paymentData = ["payment_status" => strtolower($state), "status" => strtolower($state), "webhook_response" => $finalResult];
							$transaction->update($paymentData);

							LogHelper::logSuccess('Customer wallet updated on failed', $paymentData, __FUNCTION__, basename(__FILE__), __LINE__);
						}
					}
				}

				if($finalResult["payload"]["metaInfo"]["udf1"]=="driver-wallet-add") {
					$driver = Driver::find($finalResult["payload"]["metaInfo"]["udf2"]);
					if($driver){
						$driverEarning = DriverEarning::where("payment_id", $paymentOrderId)->first();
						$paymentDataDriver = ["payment_status" => strtolower($state), "status" => strtolower($state), "webhook_response" => $finalResult];
						$driverEarning->update($paymentDataDriver);

						LogHelper::logSuccess('Driver wallet updated on failed', $paymentDataDriver, __FUNCTION__, basename(__FILE__), __LINE__);
					}
				}
				
				
				//trip status is cancelled due to payment failed by customer and add one entry in trip status table
				if($trip){
					
					//trip cancelled
					$trip->trip_status="cancelled";
					$trip->save();
					
					//add tripstatus entry
					$tripStatus = new TripStatus;
					$tripStatus->trip_id =  $trip->id;
					$tripStatus->trip_status_short = "cancelled";
					$tripStatus->trip_status_description ="Trip is cancelled due to payment failed";
					$tripStatus->trip_action_type = "customer";
					
					$tripStatus->trip_status_reason = "Trip is cancelled due to payment failed";
					$tripStatus->trip_status_title = "cancelled";					
					$tripStatus->save();
					
					// Changed API endpoint to include the specific property ID
					$apiUri = env("SOCKET_URL") . "clear/order/" . $trip->id;

					// Since we're making a GET request for a specific resource, we don't need query params
					$response = Http::get($apiUri);
					$responseData = json_decode($response->body(), true);
					Log::info("CLEAR_ORDER_API_RESPONSE => " . $response->body());
				}

				return response()->json(["status" => 200, "message" => "Trip is marked as failed."], 200); 
			}
		}
		catch(\Exception $e){
			LogHelper::logError('An error occurred while web hook create trip.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}
	
	
	public function refund_trip_payment(Request $r){
		try{
			$finalResult = $r->all();
			Log::info("Refund Payment Response".json_encode($finalResult));

		}catch(\Exception $e){
			LogHelper::logError('An error occurred while web hook refund.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}

}