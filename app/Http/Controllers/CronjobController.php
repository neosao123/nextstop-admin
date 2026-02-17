<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;
use Carbon\Carbon;
use App\Helpers\LogHelper;
use App\Models\TripStatus;
use Illuminate\Support\Facades\Http;

class CronjobController extends Controller
{
    public function runClearPendingOrders()
    {
        echo $currentTime = now()->timezone('Asia/Kolkata');
        echo $oldTime = $currentTime->copy()->subHours(2);

        Log::info('Running cron job to clear pending orders.', ['current_time' => $currentTime, 'old_time' => $oldTime]);

        $alltrips = DB::table('trips')
            ->where('trip_status', 'pending')
            ->where('created_at', '<', $oldTime)
            ->get();

        if ($alltrips->count() > 0) {
            $tripsIds = $alltrips->pluck('id')->toArray();

            $trips = $alltrips->pluck('id', 'trip_netfair_amount')->toArray();

            Log::info('Found ' . count($trips) . ' pending trips older than 2 hours.', ['trips' => $trips]);

            DB::table('trips')
                ->whereIn('id', $tripsIds)
                ->update(['trip_status' => 'cancelled']);
            Log::info('Completed cron job to clear pending orders.');
        } else {
            Log::info('No pending trips found older than 2 hours.');
        }
    }
	
	
	public function cancelled_orders(Request $request){
		try{
			
			$now = Carbon::now()->timestamp * 1000;
			Trip::where('trip_payment_mode', 'online')
				->where('trip_payment_status', 'pending')
				//->where('id',"454")
				->chunk(100, function ($trips) use ($now) {
					foreach ($trips as $trip) {
						if (!empty($trip->trip_payment_response)) {
							$response = json_decode($trip->trip_payment_response, true);

							if (isset($response['expireAt']) && $response['expireAt'] < $now) {
								$trip->trip_payment_status = 'cancelled';
								$trip->trip_status = 'cancelled';
								$trip->save();
								
								//add tripstatus entry
								$tripStatus = new TripStatus;
								$tripStatus->trip_id =  $trip->id;
								$tripStatus->trip_status_short = "cancelled";
								$tripStatus->trip_status_description ="Trip is cancelled due to payment not received";
								$tripStatus->trip_action_type = "customer";
								
								$tripStatus->trip_status_reason = "Trip is cancelled due to payment not received";
								$tripStatus->trip_status_title = "cancelled";					
								$tripStatus->save();
								
								// Changed API endpoint to include the specific property ID
								$apiUri = env("SOCKET_URL") . "clear/order/" . $trip->id;

								// Since we're making a GET request for a specific resource, we don't need query params
								$response = Http::get($apiUri);
								$responseData = json_decode($response->body(), true);
								Log::info("CLEAR_ORDER_API_RESPONSE => " . $response->body());
								
							}
						}
					}
			});
			
	   }catch(\Exception $e){
			LogHelper::logError('Cron job error while changing status of trip  for cancelled trip.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
			return response()->json(['status' => 400, 'message' => 'Something went wrong.'], 400);
		}
	}
	
	
}
