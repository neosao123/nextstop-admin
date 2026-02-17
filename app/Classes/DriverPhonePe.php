<?php 
namespace App\Classes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class DriverPhonePe
{
	
	public function create_authorization_token()
	{
		$isTest = env("PAY_MODE") === "TEST";
		$url = $isTest
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
			: 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

		$formParams = [
			'client_id' => $isTest ? env("TEST_DRIVER_CLIENT_ID") : env("LIVE_DRIVER_CLIENT_ID"),
			'client_version' => '1',
			'client_secret' => $isTest ? env("TEST_DRIVER_CLIENT_SECRET") : env("LIVE_DRIVER_CLIENT_SECRET"),
			'grant_type' => 'client_credentials',
		];
		$response = Http::asForm()->post($url, $formParams);
		if ($response->successful() && isset($response['access_token'])) {
			return $response['access_token'];
		}
		return null;
	}
	
    public function create_phonepe_order(string $amount, string $merchantOrderId,string $udf1,string $udf2)
	{
		$isTest = env("PAY_MODE") === "TEST";

		$accessToken = $this->create_authorization_token();

		if (!$accessToken) {
			return [
				'success' => false,
				'message' => 'Access token not available',
			];
		}

		$baseUrl = $isTest
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox'
			: 'https://api.phonepe.com/apis/pg';

		$endpoint = '/checkout/v2/sdk/order';
		$url = $baseUrl . $endpoint;

		$headers = [
			'Authorization' => 'O-Bearer ' . $accessToken,  // Important: "O-Bearer", not "Bearer"
			'Content-Type' => 'application/json',
		];

		$body = [
			'merchantOrderId' => $merchantOrderId,
			'amount' => (int) ((float) $amount * 100), // convert to paisa
			'expireAfter' => 1200, // 20 mins
			'metaInfo' => [
				'udf1' => $udf1,
				'udf2' => $udf2,
				'udf3' => 'user-defined-3',
				'udf4' => 'user-defined-4',
				'udf5' => 'user-defined-5',
			],
			'paymentFlow' => [
				'type' => 'PG_CHECKOUT'
			],
		];

		// Debug log
		Log::info('PhonePe Order - Request URL:', ['url' => $url]);
		Log::info('PhonePe Order - Request Body:', $body);
		Log::info('PhonePe Order - Headers:', $headers);

		$response = Http::withHeaders($headers)->post($url, $body);
		$json = $response->json();

		Log::info('PhonePe Order - Response:', $json);

		if ($response->successful() && isset($json['token'])) {
			return [
				'success' => true,
				'orderId' => $json['orderId'],
				'token' => $json['token'],
				'expireAt' => $json['expireAt'],
			];
		}

		return [
			'success' => false,
			'message' => $json['message'] ?? 'Order creation failed',
			'error' => $json,
		];
	}
	
   public function check_phonepe_order_status(string $merchantOrderId)
	{
		$isTest = env("PAY_MODE") === "TEST";
		$accessToken = $this->create_authorization_token();

		if (!$accessToken) {
			return [
				'success' => false,
				'message' => 'Access token not available',
			];
		}

		$baseUrl = $isTest
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox'
			: 'https://api.phonepe.com/apis/pg';

		$url = $baseUrl . "/checkout/v2/order/$merchantOrderId/status?details=false";

		$headers = [
			'Authorization' => 'O-Bearer ' . $accessToken,
			'Content-Type' => 'application/json',
		];

		$response =Http::withHeaders($headers)->get($url);
		$json = $response->json();

		if ($response->successful() && isset($json['state'])) {
			return [
				'success' => true,
				'status' => strtoupper($json['state']),  // Map state to status uppercase
				'data' => $json,
			];
		}

		return [
			'success' => false,
			'message' => $json['message'] ?? 'Failed to fetch order status',
			'error' => $json,
		];
	}

}