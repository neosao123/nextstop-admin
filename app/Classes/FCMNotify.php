<?php 

namespace App\Classes;

use App\Models\Notification; 
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Google\Auth\Credentials\ServiceAccountCredentials; 

class FCMNotify
{
	//update notification for version v1
	public function send(){
	    try {
	     $notifications = Notification::orderBy('id','ASC')->limit(500)->get();
	     if($notifications->count()>0) {
    		$url = 'https://fcm.googleapis.com/v1/projects/nextstop-23e9c/messages:send';
    		$serviceAccountPath = public_path('nextstop-23e9c-firebase-adminsdk-fbsvc-825e1f5bef.json');
    		if (!file_exists($serviceAccountPath)) {  
    			throw new \Exception('Service account key file not found: ' . $serviceAccountPath);
    		}
    		$credentials = new ServiceAccountCredentials(
    			'https://www.googleapis.com/auth/firebase.messaging',
    			$serviceAccountPath
    		);
    		$accessToken = $credentials->fetchAuthToken()['access_token'];
    		
    		 // Create a pool of HTTP requests
            $responses = Http::pool(function (Pool $pool) use ($notifications,$accessToken,$url) {
    		
    		foreach ($notifications as $notification) {
    		     $image = ($notification->image != "" || $notification->image != null) ? $notification->image : url('notify.png');
                 $body = [
        				  'message' => [
        					"notification" => [
        					  "title" => $notification->title,
        					  "body" => $notification->message,
							  "image"=>$image
        					],
        					"android"=>[
        					   "priority"=>"high",
        					   "ttl"=>"1000s",  
        					   "notification" => [
        						  "image"=>$image
        					   ],
        					],
        					"token" => $notification->firebase_id
        				  ]
        			];
        		    $response = Http::withHeaders([
    				  'Authorization' => 'Bearer  '. $accessToken,
    				  'Content-Type' => 'application/json'
    				])->post($url, $body);
    				if ($response->successful()) {
    					Log::debug(" Notification send => ".json_encode($response->json()));
    				} else {
    				    Log::debug(" Notification error => ".json_encode($response->body()));
    				}
    		  }
          });
          
          foreach($notifications as $n) {
                DB::table('notifications')->where('id',$n->id)->delete();
          }
	    }
	    
	    } catch (\Exception $e) {
            Log::error("General Exception: " . $e->getMessage());
        }
	    
	}
}