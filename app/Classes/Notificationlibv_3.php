<?php

namespace App\Classes;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials; 
use Illuminate\Support\Facades\Http;

class Notificationlibv_3
{
	var $CI = null;
	public function __construct()
	{
		
	}

	public function sendNotification($data, $notification)
	{
		if (isset($data['image'])) {
			$image = $data['image'] ?: "";
		} else {
			$image = "";
		}		
		
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

		$responses = [];

		foreach ($notification['device_id'] as $token) {
			$body = [
				'message' => [
					"data" => [
						"title" => $data['title'],
						"body" => $data['message'],
						"type" => "ringing"
					],
					"notification" => [
						"title" => $notification['title'],
						"body" => $notification['message'],
                        "image"=>$image						
					],
					"android" => [
						"priority" => "high",
						"ttl" => "1000s",  
						"notification" => [
							"image" => $image,
							"sound" => "ringing.mp3" 
						],
					],
					"token" => $token
				]
			];

			try {
				$response = Http::withHeaders([
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json'
				])->post($url, $body);

				if ($response->successful()) {
					$responses[$token] = $response->json();
				} else {
					$responses[$token] = 'Error: ' . $response->body();
				}
			} catch (\Exception $e) {
				$responses[$token] = 'Error: ' . $e->getMessage();
			}
		}

		return $responses; 
	}

}
