<?php 
namespace App\Classes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
class Phonepe
{
	
	
	protected $accessToken;
    protected $tokenExpiry;
    
    public function __construct()
    {
        $this->initializeToken();
    }
    
    protected function initializeToken()
    {
        // Try to get token from cache
        $this->accessToken = Cache::get('phonepe_access_token');
        $this->tokenExpiry = Cache::get('phonepe_token_expiry');
        
        // If token is expired or about to expire (within 1 minute), refresh it
        if (!$this->accessToken || time() >= ($this->tokenExpiry - 60)) {
            $this->refreshToken();
        }
    }
	
	
	protected function refreshToken()
    {
		
		$isTest = env("PAY_MODE") === "TEST";
        $url = $isTest
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
            : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $formParams = [
            'client_id' => $isTest ? env("TEST_CLIENT_ID") : env("LIVE_CLIENT_ID"),
            'client_version' => '1',
            'client_secret' => $isTest ? env("TEST_CLIENT_SECRET") : env("LIVE_CLIENT_SECRET"),
            'grant_type' => 'client_credentials',
        ];
        
        $response = Http::asForm()->post($url, $formParams);
        
        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['access_token']) && isset($data['expires_at'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiry = $data['expires_at'];
                
                // Cache the token until it expires (with 1 minute buffer)
                $cacheTime = $data['expires_at'] - time() - 60;
                if ($cacheTime > 0) {
                    Cache::put('phonepe_access_token', $this->accessToken, now()->addSeconds($cacheTime));
                    Cache::put('phonepe_token_expiry', $this->tokenExpiry, now()->addSeconds($cacheTime));
                }
                
                return true;
            }
        }        
        return false;
    }
    
    public function create_authorization_token()
    {
        $this->initializeToken();
        return $this->accessToken;
    }
    
	
	public function create_authorization_token_old()
	{
		$isTest = env("PAY_MODE") === "TEST";
		$url = $isTest
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
			: 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

		$formParams = [
			'client_id' => $isTest ? env("TEST_CLIENT_ID") : env("LIVE_CLIENT_ID"),
			'client_version' => '1',
			'client_secret' => $isTest ? env("TEST_CLIENT_SECRET") : env("LIVE_CLIENT_SECRET"),
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
	
	
	 /**
	 * Initiate a refund
	 * 
	 * @param string $merchantRefundId    Your unique refund ID
	 * @param string $originalOrderId     Original PhonePe order ID
	 * @param float $amountInRupees       Refund amount in rupees
	 * @return array
	 */
	public function initiate_refund(
		string $merchantRefundId, 
		string $originalOrderId, 
		float $amountInRupees
	) {
		$isSandbox = env('PAY_MODE') === 'TEST';
		$url = $isSandbox
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox/payments/v2/refund'
			: 'https://api.phonepe.com/apis/pg/payments/v2/refund';

		Log::info('Initiating PhonePe Refund', [
			'environment' => $isSandbox ? 'Sandbox' : 'Production',
			'merchantRefundId' => $merchantRefundId,
			'originalOrderId' => $originalOrderId,
			'amountInRupees' => $amountInRupees,
			'amountInPaisa' => (int)($amountInRupees * 100)
		]);

		$accessToken = $this->create_authorization_token();
		if (!$accessToken) {
			Log::error('PhonePe Refund Failed - No Access Token');
			return [
				'success' => false,
				'message' => 'Access token not available',
			];
		}

		$payload = [
			'merchantRefundId' => $merchantRefundId,
			'originalMerchantOrderId' => $originalOrderId,
			'amount' => (int)($amountInRupees * 100) // Convert to paisa
		];

		$headers = [
			'Authorization' => 'O-Bearer ' . $accessToken,
			'Content-Type' => 'application/json',
		];

		Log::debug('PhonePe Refund Request', [
			'url' => $url,
			'headers' => $headers,
			'payload' => $payload
		]);

		$response = Http::withHeaders($headers)
			
			->post($url, $payload);

		$data = $response->json();
		
		Log::info('PhonePe Refund Response', [
			'status_code' => $response->status(),
			'response' => $data,
			'success' => $response->successful()
		]);

		if ($response->successful()) {
			Log::info('PhonePe Refund Initiated Successfully', [
				'refundId' => $data['refundId'] ?? null,
				'status' => $data['state'] ?? null
			]);
			return [
				'success' => true,
				'refundId' => $data['refundId'],
				'amount' => $data['amount'],
				'status' => $data['state']
			];
		}

		Log::error('PhonePe Refund Failed', [
			'error' => $data['message'] ?? 'Unknown error',
			'response' => $data
		]);
		
		return [
			'success' => false,
			'error' => $data['message'] ?? 'Refund failed',
			'response' => $data
		];
	}

	/**
	 * Check refund status
	 * 
	 * @param string $merchantRefundId The refund ID you initiated
	 * @return array
	 */
	public function check_refund_status(string $merchantRefundId)
	{
		$isTest = env("PAY_MODE") === "TEST";
		
		$baseUrl = $isTest
			? 'https://api-preprod.phonepe.com/apis/pg-sandbox'
			: 'https://api.phonepe.com/apis/pg';
		
		$endpoint = "/payments/v2/refund/{$merchantRefundId}/status";
		$url = $baseUrl . $endpoint;

		Log::info('Checking PhonePe Refund Status', [
			'environment' => $isTest ? 'Sandbox' : 'Production',
			'merchantRefundId' => $merchantRefundId,
			'full_url' => $url
		]);

		$accessToken = $this->create_authorization_token();
		if (!$accessToken) {
			Log::error('PhonePe Refund Status Check Failed - No Access Token');
			return [
				'success' => false,
				'message' => 'Access token not available',
			];
		}

		$headers = [
			'Content-Type' => 'application/json',
			'Authorization' => 'O-Bearer ' . $accessToken
		];

		Log::debug('PhonePe Refund Status Request', [
			'headers' => $headers,
			'method' => 'GET'
		]);

		$response = Http::withHeaders($headers)
			
			->get($url);

		$data = $response->json();

		Log::info('PhonePe Refund Status Response', [
			'status_code' => $response->status(),
			'response' => $data,
			'success' => $response->successful()
		]);

		if ($response->successful()) {
			$status = strtoupper($data['state'] ?? 'UNKNOWN');
			Log::info('PhonePe Refund Status Retrieved', [
				'status' => $status,
				'refundId' => $data['refundId'] ?? null
			]);
			return [
				'success' => true,
				'status' => $status,
				'data' => $data
			];
		}

		Log::error('PhonePe Refund Status Check Failed', [
			'error' => $data['message'] ?? 'Unknown error',
			'response' => $data
		]);
		
		return [
			'success' => false,
			'error' => $data['message'] ?? 'Failed to fetch refund status',
			'data' => $data
		];
	}

}