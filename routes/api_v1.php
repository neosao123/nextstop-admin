<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\VersionController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\ContactController;


Route::post('/contact', [ContactController::class, 'index']);

//webhook url for testing logic of wallet

Route::any('/verify/payment', [WebhookController::class, 'verify_payment']);
Route::any('/refund/payment', [WebhookController::class, 'refund_trip_payment']);

Route::post('/checkorderstatus', [CustomerController::class, 'check_phonepe_order_status']);
Route::post('/driver/check-add-money-status', [DriverController::class, 'check_order_status']);
Route::post('/customer/check-order-status', [CustomerController::class, 'check_order_status']);

Route::get('/driver/version', [VersionController::class, 'get_driver_version']);
Route::post('/driver/version/update', [VersionController::class, 'update_driver_version']);


Route::get('/customer/version', [VersionController::class, 'get_customer_version']);
Route::post('/customer/version/update', [VersionController::class, 'update_customer_version']);

Route::post('/check-customer-status', [CustomerController::class, 'check_user_status']);
Route::post('/check-driver-status', [DriverController::class, 'check_user_status']);

/** --------------------------------------------------------------------------------------------------
 * Driver
 * seemashelar@neosao
 * --------------------------------------------------------------------------------------------------- */
Route::group(['prefix' => 'driver'], function () {

    /** --------------------------------------------------------------------------------------------------
     * Login,logout,resendotp,verifyotp,register
     * seemashelar@neosao
     * --------------------------------------------------------------------------------------------------- */
    Route::post('/login', [DriverController::class, 'login']);
    Route::post('/logout', [DriverController::class, 'logout']);
    Route::post('/resendotp', [DriverController::class, 'resend_otp']);
    Route::post('/verifyotp', [DriverController::class, 'verify_otp']);
    Route::post('/register', [DriverController::class, 'driver_register']);
    
	//get serviceable location
    Route::post('/serviceable/location', [DriverController::class, 'serviceable_location']);

    
    Route::middleware(['auth:sanctum'])->group(function () {
        
		
		//change online/ofline status
		Route::post('/change/status', [DriverController::class, 'driver_change_status']);
		
		//basic info of driver
        
		Route::post('/basicinfo', [DriverController::class, 'basic_profile_info']);
        Route::post('/update/profile', [DriverController::class, 'basic_profile_update']);


        //document register first step for driver
        Route::post('/document/register', [DriverController::class, 'document_register']);

        //document update first step for driver
        Route::post('/document/update', [DriverController::class, 'document_update']);

        //vehicle register second step for driver
        Route::post('/vehicles', [DriverController::class, 'vehicles']);
        Route::post('/vehicle/register', [DriverController::class, 'vehicle_register']);

        //vehicle update second step for driver
        Route::post('/vehicle/update', [DriverController::class, 'vehicle_update']);

        //training video seen third step for driver
        Route::get('/trainingvideo', [DriverController::class, 'training_video_list']);
        Route::post('/trainingvideo/register', [DriverController::class, 'training_video_details']);

        //get all three steps info with verification status and driver register status
        Route::post('/registerinfo', [DriverController::class, 'driver_register_info']);

        //delete driver
        Route::post('/delete', [DriverController::class, 'driver_delete']);

        //logout driver
        Route::post('/logout', [DriverController::class, 'driver_logout']);

        //driver status checking
        Route::post('/checkstatus', [DriverController::class, 'check_driver_status']);
    
	    //driver trip list
	    Route::post('/trip/list', [DriverController::class, 'trip_list']);
        Route::post('/trip/accept', [DriverController::class, 'trip_status_change']);
	    Route::post('/trip/details', [DriverController::class, 'trip_details']);
		Route::post('/trip/end', [DriverController::class, 'trip_end']);
		Route::get('/home/data', [DriverController::class, 'driver_home_screen']);
	    Route::post('/rating', [DriverController::class, 'customer_rating']);
		
		Route::post('/earning/list', [DriverController::class, 'earning_list']);
		Route::post('/earning/trip/list', [DriverController::class, 'earning_trip_list']);
	    
		Route::post('/cancel-trip', [DriverController::class, 'cancel_trip']);
	    Route::get('/cancel-reason', [DriverController::class, 'cancel_reason']);
	 	
		Route::post('/delete', [DriverController::class, 'driver_delete']);	
        Route::post('/firebase/update', [DriverController::class, 'update_firebase_token']);		
	
	    Route::post('/ledger/balance', [DriverController::class, 'ledger_balance']);
		
		//withdrawal Request
		
		Route::post('/withdrawal-request', [DriverController::class, 'withdrawal_request']);
	    Route::post('/transaction/list', [DriverController::class, 'driver_transaction_list']);
        
	
	    //verify pickup otp
		
		Route::post('/verify/pickup-otp', [DriverController::class, 'verify_pickup_otp']);
        Route::post('/resend/pickup-otp', [DriverController::class, 'send_pickup_otp']);
	
	  //verify and resend otp while deliver trip
	    Route::post('/verify/deliver-otp', [DriverController::class, 'verify_deliver_otp']);
        Route::post('/resend/deliver-otp', [DriverController::class, 'send_deliver_otp']);
	
	   //add money to wallet
	   
	     Route::post('/check-wallet-status', [DriverController::class, 'check_driver_balance']);
		 Route::post('/add-money-wallet', [DriverController::class, 'add_money_wallet']);
	
	});
});

/** --------------------------------------------------------------------------------------------------
 *Customer
 * seemashelar@neosao
 * --------------------------------------------------------------------------------------------------- */
Route::group(['prefix' => 'customer'], function () {
    /** --------------------------------------------------------------------------------------------------
     * Login,logout,resendotp,verifyotp,register
     * seemashelar@neosao
     * --------------------------------------------------------------------------------------------------- */
    Route::post('/login', [CustomerController::class, 'login']);
    Route::post('/logout', [CustomerController::class, 'customer_logout']);
    Route::post('/resendotp', [CustomerController::class, 'resend_otp']);
    Route::post('/verifyotp', [CustomerController::class, 'verify_otp']);
    Route::post('/register', [CustomerController::class, 'customer_register']);
	
	Route::middleware(['auth:sanctum'])->group(function () {
		
		//invoice 
		Route::get('invoice/{id}', [CustomerController::class, 'invoice']);
		
		
		//basic info of driver
        Route::post('/basicinfo', [CustomerController::class, 'basic_profile_info']);
        Route::post('/update/profile', [CustomerController::class, 'basic_profile_update']);
        Route::post('/homevehicle/list', [CustomerController::class, 'home_vehicle_list']);
		Route::post('/vehicle/list', [CustomerController::class, 'vehicle_list']);
        Route::post('/goodtype/list', [CustomerController::class, 'good_types_list']);
		Route::post('/coupon/list', [CustomerController::class, 'coupon_list']);
		
		Route::post('/calculate/tripamount', [CustomerController::class, 'calculate_tripamount']);
		Route::post('/booktrip', [CustomerController::class, 'book_trip']);
		Route::post('/source/address', [CustomerController::class, 'source_address']);
		Route::post('/destination/address', [CustomerController::class, 'destination_address']);
		Route::get('/services', [CustomerController::class, 'get_all_services']); 
	    Route::post('/triplist', [CustomerController::class, 'get_trip_history']); 
	    Route::get('/tripdetails', [CustomerController::class, 'trip_details']);
        Route::post('/trip/rating', [CustomerController::class, 'trip_driver_rating']); 		
	    Route::post('/trip/cancel', [CustomerController::class, 'cancel_trip']);
 		Route::get('/cancel-reason', [CustomerController::class, 'cancel_reason']);
	 		 	
		Route::post('/add/wallet', [CustomerController::class, 'customer_wallet_add']);
        Route::post('/wallet', [CustomerController::class, 'customer_wallet']); 		 		
	    Route::get('/transaction/list', [CustomerController::class, 'customer_transaction_list']);
        	
        Route::post('/delete', [CustomerController::class, 'customer_delete']);	
        Route::post('/firebase/update', [CustomerController::class, 'update_firebase_token']);		
	    Route::post('/check/serviceable/area', [CustomerController::class, 'serviceable_area']);
	
	    Route::post('/update/address', [CustomerController::class, 'update_customer_address']);
        Route::post('/delete/address', [CustomerController::class, 'delete_customer_address']);	
        Route::post('/edit/address', [CustomerController::class, 'edit_customer_address']);					

	    Route::post('/create/order', [CustomerController::class, 'create_order']);

	});
	
});


