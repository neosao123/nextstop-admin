<?php

// 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
// Controllers
use App\Http\Controllers\PendingDriver;
use App\Http\Controllers\VerifiedDriver;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GoodsTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\DriverDocumentDetails;
use App\Http\Controllers\TrainingVideoController;
use App\Http\Controllers\UserPermissionsController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\ServiceableZoneController;
use App\Http\Controllers\DriverRejectionReasonController;
use App\Http\Controllers\CustomerRejectionReasonController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\CancelTripController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\IncentivesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DriverWithdrawalController;
use App\Http\Controllers\DriverPaymentHistoryController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\EnquiryController;
use App\Classes\FCMNotify;
use App\Http\Controllers\CronjobController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/user/deleteuserprocess', [WelcomeController::class, 'delete_user_process']);
Route::get('/send-notification', function () {
    $fcm = new FCMNotify();
    $response = $fcm->send();
    return $response;
});

Route::get('/customer/deleteuserprocess', [DashboardController::class, 'delete_customer_process']);
Route::get('/driver/deleteuserprocess', [DashboardController::class, 'delete_driver_process']);

//clear commands
Route::get('clear', function () {
    Artisan::call('optimize:clear');
});

Route::get('clear-pending-orders', [CronjobController::class, 'runClearPendingOrders']);

Route::get('cancelled-pending-orders', [CronjobController::class, 'cancelled_orders']);

/** 
 * Access Storage Files
 * @author shreyasm@neosao 
 */
Route::get("storage-bucket", function (Request $request) {
    return response()->file(storage_path('app/public/' . $request->path));
});


/** --------------------------------------------------------------------------------------------------
 * Admin Routes
 * seemashelar@neosao
 * --------------------------------------------------------------------------------------------------- */
Route::get('/updatepassword', [AuthController::class, 'updatePassword']);

//forgot-password
Route::get('/forgot-password', [AuthController::class, 'reset']);
Route::post('/reset-password', [AuthController::class, 'reset_password']);
Route::get('/verify-token/{token}', [AuthController::class, 'verify_token_link']);
Route::post('/recovers-password', [AuthController::class, 'update_password']);

Route::group(['middleware' => ['PreventBack']], function () {
    /** --------------------------------------------------------------------------------------------------
     * login + logged out
     * seemashelar@neosao
     * --------------------------------------------------------------------------------------------------- */

    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::get('/login', [AuthController::class, 'index'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::group(['middleware' => ['admin']], function () {
        /** --------------------------------------------------------------------------------------------------
         * dashboard + profile
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::get('/welcome', [DashboardController::class, 'welcome'])->middleware(['permission:Welcome.View,admin']);
        Route::get("/dashboard", [DashboardController::class, 'index'])->middleware(['permission:Dashboard.View,admin']);
        Route::get("/dashboard/driver/piechart", [DashboardController::class, 'pie_chart']);
        Route::get("/dashboard/totaltrips", [DashboardController::class, 'total_trips_bar_chart']);
		Route::get("/dashboard/canceltrips/list", [DashboardController::class, 'cancel_trips']);
		Route::get("/dashboard/driver/list", [DashboardController::class, 'driver_verified_list']);

        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProfileController::class, 'index']);
            Route::post('/update', [ProfileController::class, 'update']);
            Route::get('/delete/avatar', [ProfileController::class, 'deleteAvatar']);
        });

        /** --------------------------------------------------------------------------------------------------
         * change password
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'change-password'], function () {
            Route::get('/', [ProfileController::class, 'changePassword']);
            Route::post('/update', [ProfileController::class, 'updatePassword']);
        });

        Route::group(['prefix' => 'configuration'], function () {

            /** --------------------------------------------------------------------------------------------------
             * Role
             * seemashelar@neosao
             * --------------------------------------------------------------------------------------------------- */
            Route::group(['prefix' => 'role'], function () {
                Route::get('/', [RoleController::class, 'index'])->middleware(['permission:Role.List,admin']);
                Route::get('/list', [RoleController::class, 'list'])->middleware(['permission:Role.List,admin']);
                Route::get('/add', [RoleController::class, 'add']);
                Route::post('/store', [RoleController::class, 'store']);
                Route::get('/edit', [RoleController::class, 'edit'])->middleware(['permission:Role.Edit,admin']);
                Route::post('/update', [RoleController::class, 'update'])->middleware(['permission:Role.Create-Update,admin']);
                Route::get('/delete/{id}', [RoleController::class, 'destroy'])->middleware(['permission:Role.Delete,admin']);
            });
            /*Route::group(['prefix' => 'role'], function () {
				Route::get('/list', [RoleController::class, 'list']);  
			});
			Route::resource('role', RoleController::class);*/

            /** --------------------------------------------------------------------------------------------------
             * permission groups
             * seemashelar@neosao
             * --------------------------------------------------------------------------------------------------- */
            Route::group(['prefix' => '/permission-groups'], function () {
                Route::get('/', [PermissionGroupController::class, 'index'])->middleware(['permission:PermissionGroup.List,admin']);
                Route::get('/create', [PermissionGroupController::class, 'create'])->middleware(['permission:PermissionGroup.Create,admin']);
                Route::post('/store', [PermissionGroupController::class, 'store']);
            });

            /** --------------------------------------------------------------------------------------------------
             * permissions
             * seemashelar@neosao
             * --------------------------------------------------------------------------------------------------- */

            Route::group(['prefix' => '/permissions'], function () {
                Route::get('/', [PermissionController::class, 'index'])->middleware(['permission:Permissions.List,admin']);
                Route::post('/store', [PermissionController::class, 'store'])->middleware(['permission:Permissions.Create,admin']);
            });
        });

        /** --------------------------------------------------------------------------------------------------
         * user master
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'users'], function () {
            Route::get('/exceldownload', [UserController::class, 'excel_download'])->middleware(['permission:User.Export,admin']);
            Route::get('/pdfdownload', [UserController::class, 'pdf_download'])->middleware(['permission:User.Export,admin']);
            Route::get('/', [UserController::class, 'index'])->middleware(['permission:User.List,admin']);
            Route::get('/list', [UserController::class, 'list']);
            Route::get('/create', [UserController::class, 'create'])->middleware(['permission:User.Create,admin']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show'])->middleware(['permission:User.View,admin']);
            Route::get('/{id}/edit', [UserController::class, 'edit'])->middleware(['permission:User.Edit,admin']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy'])->middleware(['permission:User.Delete,admin']);
            Route::get('/fetch/role', [UserController::class, 'get_role']);
            Route::get('/fetch/users', [UserController::class, 'get_users']);

            Route::get('/delete/avatar/{id}', [UserController::class, 'delete_avatar']);
            Route::get('/block/{id}', [UserController::class, 'block_unblock_user'])->middleware(['permission:User.Block,admin']);
        });

        /** --------------------------------------------------------------------------------------------------
         * access rights for user
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => '/user/{id}'], function () {
            Route::get('/permissions', [UserPermissionsController::class, 'index'])->middleware(['permission:User.Permissions,admin']);
            Route::get('/set-permission', [UserPermissionsController::class, 'setPermission']);
        });


        /** --------------------------------------------------------------------------------------------------
         * Vehicle Type - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('vehicle-type', [VehicleTypeController::class, 'index'])->middleware(['permission:Vehicle-Type.List,admin']);
        Route::get('vehicle-type/list', [VehicleTypeController::class, 'list'])->middleware(['permission:Vehicle-Type.List,admin']);
        Route::get('vehicle-type/create', [VehicleTypeController::class, 'create'])->middleware(['permission:Vehicle-Type.Create,admin']);
        Route::post('vehicle-type', [VehicleTypeController::class, 'store'])->middleware(['permission:Vehicle-Type.Create,admin']);
        Route::get('vehicle-type/{id}', [VehicleTypeController::class, 'show'])->middleware(['permission:Vehicle-Type.View,admin']);
        Route::get('vehicle-type/{id}/edit', [VehicleTypeController::class, 'edit'])->middleware(['permission:Vehicle-Type.Edit,admin']);
        Route::put('vehicle-type/{id}', [VehicleTypeController::class, 'update'])->middleware(['permission:Vehicle-Type.Edit,admin']);
        Route::delete('vehicle-type/{id}', [VehicleTypeController::class, 'destroy'])->middleware(['permission:Vehicle-Type.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * Vehicle - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('vehicle', [VehicleController::class, 'index'])->middleware(['permission:Vehicle.List,admin']);
        Route::get('vehicle/excel-export', [VehicleController::class, 'excelDownloadVehicle'])->middleware(['permission:Vehicle.Export,admin']);
        Route::get('vehicle/pdf-export', [VehicleController::class, 'pdfDownloadVehicle'])->middleware(['permission:Vehicle.Export,admin']);
        Route::get('vehicle/import/excel', [VehicleController::class, 'importexcel'])->middleware(['permission:Vehicle.Import,admin']);
        Route::post('vehicle/validate/excel', [VehicleController::class, 'validateexcel'])->middleware(['permission:Vehicle.Import,admin']);
        Route::post('vehicle/upload/excel', [VehicleController::class, 'uploadexcel'])->middleware(['permission:Vehicle.Import,admin']);
        Route::get('vehicle/list', [VehicleController::class, 'list'])->middleware(['permission:Vehicle.List,admin']);
        Route::get('vehicle/create', [VehicleController::class, 'create'])->middleware(['permission:Vehicle.Create,admin']);
        Route::post('vehicle', [VehicleController::class, 'store'])->middleware(['permission:Vehicle.Create,admin']);
        Route::get('vehicle/{id}', [VehicleController::class, 'show'])->middleware(['permission:Vehicle.View,admin']);
        Route::get('vehicle/{id}/edit', [VehicleController::class, 'edit'])->middleware(['permission:Vehicle.Edit,admin']);
        Route::put('vehicle/{id}', [VehicleController::class, 'update'])->middleware(['permission:Vehicle.Edit,admin']);
        Route::delete('vehicle/{id}', [VehicleController::class, 'destroy'])->middleware(['permission:Vehicle.Delete,admin']);
        Route::get('vehicle/delete/icon/{id}', [VehicleController::class, 'deletevehicleicon'])->middleware(['permission:Vehicle.Edit,admin']);
        // Fetch
        Route::get('vehicle/fetch/vehicle-type', [VehicleController::class, 'vehicletype'])->middleware(['permission:Vehicle.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * Coupon - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('coupon', [CouponsController::class, 'index'])->middleware(['permission:Coupon.List,admin']);
        Route::get('coupon/list', [CouponsController::class, 'list'])->middleware(['permission:Coupon.List,admin']);
        Route::get('coupon/create', [CouponsController::class, 'create'])->middleware(['permission:Coupon.Create,admin']);
        Route::post('coupon', [CouponsController::class, 'store'])->middleware(['permission:Coupon.Create,admin']);
        Route::get('coupon/{id}', [CouponsController::class, 'show'])->middleware(['permission:Coupon.View,admin']);
        Route::get('coupon/{id}/edit', [CouponsController::class, 'edit'])->middleware(['permission:Coupon.Edit,admin']);
        Route::put('coupon/{id}', [CouponsController::class, 'update'])->middleware(['permission:Coupon.Edit,admin']);
        Route::delete('coupon/{id}', [CouponsController::class, 'destroy'])->middleware(['permission:Coupon.Delete,admin']);
        Route::get('coupon/delete/image/{id}', [CouponsController::class, 'deletecouponimage'])->middleware(['permission:Coupon.Edit,admin']);



        /** --------------------------------------------------------------------------------------------------
         * Serviceable Zone - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('serviceable-zone', [ServiceableZoneController::class, 'index'])->middleware(['permission:ServiceableZone.List,admin']);
        Route::get('serviceable-zone/list', [ServiceableZoneController::class, 'list'])->middleware(['permission:ServiceableZone.List,admin']);
        Route::get('serviceable-zone/create', [ServiceableZoneController::class, 'create'])->middleware(['permission:ServiceableZone.Create,admin']);
        Route::post('serviceable-zone', [ServiceableZoneController::class, 'store'])->middleware(['permission:ServiceableZone.Create,admin']);
        Route::get('serviceable-zone/{id}', [ServiceableZoneController::class, 'show'])->middleware(['permission:ServiceableZone.View,admin']);
        Route::get('serviceable-zone/{id}/edit', [ServiceableZoneController::class, 'edit'])->middleware(['permission:ServiceableZone.Edit,admin']);
        Route::put('serviceable-zone/{id}', [ServiceableZoneController::class, 'update'])->middleware(['permission:ServiceableZone.Edit,admin']);
        Route::delete('serviceable-zone/{id}', [ServiceableZoneController::class, 'destroy'])->middleware(['permission:ServiceableZone.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * Goods Type - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('goods-type', [GoodsTypeController::class, 'index'])->middleware(['permission:Goods-Type.List,admin']);
        Route::get('goods-type/import/excel', [GoodsTypeController::class, 'importexcel'])->middleware(['permission:Goods-Type.Import,admin']);
        Route::post('goods-type/validate/excel', [GoodsTypeController::class, 'validateexcel'])->middleware(['permission:Goods-Type.Import,admin']);
        Route::post('goods-type/upload/excel', [GoodsTypeController::class, 'uploadexcel'])->middleware(['permission:Goods-Type.Import,admin']);
        Route::get('goods-type/list', [GoodsTypeController::class, 'list'])->middleware(['permission:Goods-Type.List,admin']);
        Route::get('goods-type/create', [GoodsTypeController::class, 'create'])->middleware(['permission:Goods-Type.Create,admin']);
        Route::post('goods-type', [GoodsTypeController::class, 'store'])->middleware(['permission:Goods-Type.Create,admin']);
        Route::get('goods-type/{id}', [GoodsTypeController::class, 'show'])->middleware(['permission:Goods-Type.View,admin']);
        Route::get('goods-type/{id}/edit', [GoodsTypeController::class, 'edit'])->middleware(['permission:Goods-Type.Edit,admin']);
        Route::put('goods-type/{id}', [GoodsTypeController::class, 'update'])->middleware(['permission:Goods-Type.Edit,admin']);
        Route::delete('goods-type/{id}', [GoodsTypeController::class, 'destroy'])->middleware(['permission:Goods-Type.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * Pending Driver - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::group(['prefix' => 'driver/pending'], function () {
            
			
			Route::get('/', [PendingDriver::class, 'index'])->middleware(['permission:Pending-Driver.List,admin']);
            Route::get('/excel-export', [PendingDriver::class, 'excelDownloadPendingDrivers']);
            Route::get('/pdf-export', [PendingDriver::class, 'pdfDownloadPendingDriver']);
            Route::get('/list', [PendingDriver::class, 'list'])->middleware(['permission:Pending-Driver.List,admin']);
            Route::get('/{id}', [PendingDriver::class, 'viewOrEdit'])->middleware(['permission:Pending-Driver.View-Edit,admin']);
            Route::post('/porter-photo/delete/{id}', [PendingDriver::class, 'delete_porter_photo']);
            Route::post('/personal-document/delete/{id}', [PendingDriver::class, 'personal_document_delete']);
            Route::post('/vehicle-document/delete/{id}', [PendingDriver::class, 'vehicle_document_delete']);
            Route::put('/verify/{id}', [PendingDriver::class, 'verifyPersonalDetails']);
            Route::put('/admin-verify/{id}', [PendingDriver::class, 'admin_verify']);
			
			
			Route::put('/{id}', [PendingDriver::class, 'update']);
            Route::post('/vehicle-information/{id}', [PendingDriver::class, 'vehicle_information_update']);
            Route::post('/vehicle-information/verify/{id}', [PendingDriver::class, 'vehicle_information_verify']);
            Route::post('/training-video-verify/{id}', [PendingDriver::class, 'training_video_verify']);
            Route::post('/vehicle-photo/delete/{id}', [PendingDriver::class, 'delete_vehicle_photo']);
            Route::delete('/{id}', [PendingDriver::class, 'destroy'])->middleware(['permission:Pending-Driver.Delete,admin']);
            Route::delete('/{id}/{type}', [PendingDriver::class, 'blockOrUnblock'])->middleware(['permission:Pending-Driver.Block,admin']);
            
			// Fetch
            Route::get('/fetch/vehicle', [PendingDriver::class, 'vehicle']);
            Route::get('/fetch/servicable-zone', [PendingDriver::class, 'servicablelocation']);
			
        });  


        /** --------------------------------------------------------------------------------------------------
         * Verified driver - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::group(['prefix' => 'driver/verified'], function () {
            
			Route::get('/rating/list', [VerifiedDriver::class, 'rating_list']);
			
			Route::get('/rating/{id}', [VerifiedDriver::class, 'rating_index'])->middleware(['permission:Verified-Driver.Rating,admin']);
     
			Route::get('/', [VerifiedDriver::class, 'index'])->middleware(['permission:Verified-Driver.List,admin']);
            Route::get('/excel-export', [VerifiedDriver::class, 'excelDownloadVerifiedDrivers']);
            Route::get('/pdf-export', [VerifiedDriver::class, 'pdfDownloadVerifiedDriver']);
            Route::get('/list', [VerifiedDriver::class, 'list'])->middleware(['permission:Verified-Driver.List,admin']);
            Route::get('/{id}', [VerifiedDriver::class, 'viewOrEdit'])->middleware(['permission:Verified-Driver.View-Edit,admin']);
            Route::post('/porter-photo/delete/{id}', [VerifiedDriver::class, 'delete_porter_photo']);
            Route::post('/personal-document/delete/{id}', [VerifiedDriver::class, 'personal_document_delete']);
            Route::post('/vehicle-document/delete/{id}', [VerifiedDriver::class, 'vehicle_document_delete']);
            Route::put('/verify/{id}', [VerifiedDriver::class, 'verifyPersonalDetails']);
			Route::put('/admin-verify/{id}', [VerifiedDriver::class, 'admin_verify']);
            Route::put('/{id}', [VerifiedDriver::class, 'update']);
            Route::post('/vehicle-information/{id}', [VerifiedDriver::class, 'vehicle_information_update']);
            Route::post('/vehicle-information/verify/{id}', [VerifiedDriver::class, 'vehicle_information_verify']);
            Route::post('/training-video-verify/{id}', [VerifiedDriver::class, 'training_video_verify']);
            Route::post('/vehicle-photo/delete/{id}', [VerifiedDriver::class, 'delete_vehicle_photo']);
            Route::delete('/{id}', [VerifiedDriver::class, 'destroy'])->middleware(['permission:Verified-Driver.Delete,admin']);
            Route::delete('/{id}/{type}', [VerifiedDriver::class, 'blockOrUnblock'])->middleware(['permission:Verified-Driver.Block,admin']);

            // Fetch
            Route::get('/fetch/vehicle', [VerifiedDriver::class, 'vehicle']);
            Route::get('/fetch/servicable-zone', [VerifiedDriver::class, 'servicablelocation']);
        
		    Route::get('/fetch/customer', [VerifiedDriver::class, 'get_customers']);
            Route::get('/fetch/trip', [VerifiedDriver::class, 'get_trips']);
            Route::get('/fetch/driver', [VerifiedDriver::class, 'get_drivers']);
		
		});



        /** --------------------------------------------------------------------------------------------------
         * driver  - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::get('driver-document-details/change-status/{id}', [DriverDocumentDetails::class, 'change_status']);
        Route::get('driver-document-details', [DriverDocumentDetails::class, 'index']);
        Route::get('driver-document-details/list', [DriverDocumentDetails::class, 'list']);
        Route::get('driver-document-details/{id}', [DriverDocumentDetails::class, 'show']);

        // Route::get('driver/{id}/edit', [Driver::class, 'edit'])->middleware(['permission:driver.Edit,admin']);
        // Route::put('driver/{id}', [Driver::class, 'update'])->middleware(['permission:driver.Edit,admin']);
        // Route::delete('driver/{id}', [Driver::class, 'destroy'])->middleware(['permission:driver.Delete,admin']);
        // Route::get('driver/{id}/{type}', [Driver::class, 'activateOrSuspend']);


        /** --------------------------------------------------------------------------------------------------
         * Training Video - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('training-video', [TrainingVideoController::class, 'index'])->middleware(['permission:Training-Video.List,admin']);
        Route::get('training-video/list', [TrainingVideoController::class, 'list'])->middleware(['permission:Training-Video.List,admin']);
        Route::get('training-video/create', [TrainingVideoController::class, 'create'])->middleware(['permission:Training-Video.Create,admin']);
        Route::post('training-video', [TrainingVideoController::class, 'store'])->middleware(['permission:Training-Video.Create,admin']);
        Route::get('training-video/{id}', [TrainingVideoController::class, 'show'])->middleware(['permission:Training-Video.View,admin']);
        Route::get('training-video/{id}/edit', [TrainingVideoController::class, 'edit'])->middleware(['permission:Training-Video.Edit,admin']);
        Route::put('training-video/{id}', [TrainingVideoController::class, 'update'])->middleware(['permission:Training-Video.Edit,admin']);
        Route::delete('training-video/{id}', [TrainingVideoController::class, 'destroy'])->middleware(['permission:Training-Video.Delete,admin']);
        Route::get('training-video/delete/video/{id}', [TrainingVideoController::class, 'delete_video']);
		Route::get('training-video/delete/thumbnail/{id}', [TrainingVideoController::class, 'delete_thumbnail']);

        /** --------------------------------------------------------------------------------------------------
         * Setting
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('setting', [SettingController::class, 'index'])->middleware(['permission:Setting.List,admin']);
        Route::get('setting/list', [SettingController::class, 'list'])->middleware(['permission:Setting.List,admin']);
        Route::get('setting/create', [SettingController::class, 'create'])->middleware(['permission:Setting.Create,admin']);
        Route::post('setting', [SettingController::class, 'store'])->middleware(['permission:Setting.Create,admin']);
        Route::get('setting/{id}', [SettingController::class, 'show'])->middleware(['permission:Setting.View,admin']);
        Route::get('setting/{id}/edit', [SettingController::class, 'edit'])->middleware(['permission:Setting.Edit,admin']);
        Route::put('setting/{id}', [SettingController::class, 'update'])->middleware(['permission:Setting.Edit,admin']);
        Route::delete('setting/{id}', [SettingController::class, 'destroy'])->middleware(['permission:Setting.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * Customer Rejection Reason - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('customer-rejection-reason/import/excel', [CustomerRejectionReasonController::class, 'import_excel'])->middleware(['permission:Customer-Reason.Import,admin']);
        Route::post('customer-rejection-reason/validate/excel', [CustomerRejectionReasonController::class, 'validate_excel']);
        Route::post('customer-rejection-reason/upload/excel', [CustomerRejectionReasonController::class, 'upload_excel']);

        Route::get('customer-rejection-reason', [CustomerRejectionReasonController::class, 'index'])->middleware(['permission:Customer-Reason.List,admin']);
        Route::get('customer-rejection-reason/list', [CustomerRejectionReasonController::class, 'list'])->middleware(['permission:Customer-Reason.List,admin']);
        Route::get('customer-rejection-reason/create', [CustomerRejectionReasonController::class, 'create'])->middleware(['permission:Customer-Reason.Create,admin']);
        Route::post('customer-rejection-reason', [CustomerRejectionReasonController::class, 'store'])->middleware(['permission:Customer-Reason.Create,admin']);
        Route::get('customer-rejection-reason/{id}', [CustomerRejectionReasonController::class, 'show'])->middleware(['permission:Customer-Reason.View,admin']);
        Route::get('customer-rejection-reason/{id}/edit', [CustomerRejectionReasonController::class, 'edit'])->middleware(['permission:Customer-Reason.Edit,admin']);
        Route::put('customer-rejection-reason/{id}', [CustomerRejectionReasonController::class, 'update'])->middleware(['permission:Customer-Reason.Edit,admin']);
        Route::delete('customer-rejection-reason/{id}', [CustomerRejectionReasonController::class, 'destroy'])->middleware(['permission:Customer-Reason.Delete,admin']);

        /** --------------------------------------------------------------------------------------------------
         * driver Rejection Reason - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('driver-rejection-reason/import/excel', [DriverRejectionReasonController::class, 'import_excel'])->middleware(['permission:Driver-Reason.Import,admin']);
        Route::post('driver-rejection-reason/validate/excel', [DriverRejectionReasonController::class, 'validate_excel']);
        Route::post('driver-rejection-reason/upload/excel', [DriverRejectionReasonController::class, 'upload_excel']);


        Route::get('driver-rejection-reason', [DriverRejectionReasonController::class, 'index'])->middleware(['permission:Driver-Reason.List,admin']);
        Route::get('driver-rejection-reason/list', [DriverRejectionReasonController::class, 'list'])->middleware(['permission:Driver-Reason.List,admin']);
        Route::get('driver-rejection-reason/create', [DriverRejectionReasonController::class, 'create'])->middleware(['permission:Driver-Reason.Create,admin']);
        Route::post('driver-rejection-reason', [DriverRejectionReasonController::class, 'store'])->middleware(['permission:Driver-Reason.Create,admin']);
        Route::get('driver-rejection-reason/{id}', [DriverRejectionReasonController::class, 'show'])->middleware(['permission:Driver-Reason.View,admin']);
        Route::get('driver-rejection-reason/{id}/edit', [DriverRejectionReasonController::class, 'edit'])->middleware(['permission:Driver-Reason.Edit,admin']);
        Route::put('driver-rejection-reason/{id}', [DriverRejectionReasonController::class, 'update'])->middleware(['permission:Driver-Reason.Edit,admin']);
        Route::delete('driver-rejection-reason/{id}', [DriverRejectionReasonController::class, 'destroy'])->middleware(['permission:Driver-Reason.Delete,admin']);


        /** --------------------------------------------------------------------------------------------------
         * customer master
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'customers'], function () {
            Route::get('/exceldownload', [CustomerController::class, 'excel_download'])->middleware(['permission:Customer.Export,admin']);
            Route::get('/pdfdownload', [CustomerController::class, 'pdf_download'])->middleware(['permission:Customer.Export,admin']);
            
			Route::get('/rating/list', [CustomerController::class, 'rating_list']);
			Route::get('/rating/{id}', [CustomerController::class, 'rating_index'])->middleware(['permission:Customer.Rating,admin']);
         
			Route::get('/wallet-transaction/list', [CustomerController::class, 'transaction_list']);
			Route::get('/wallet-transaction/{id}', [CustomerController::class, 'transaction_index'])->middleware(['permission:Customer.Wallet-Transaction,admin']);
		    Route::get('/wallet-transaction/show/{id}', [CustomerController::class, 'show_wallet_transaction']);
			
			Route::post('/wallet', [CustomerController::class, 'wallet_operation']);
			
			Route::get('/', [CustomerController::class, 'index'])->middleware(['permission:Customer.List,admin']);
            Route::get('/list', [CustomerController::class, 'list']);
            Route::get('/{id}', [CustomerController::class, 'show'])->middleware(['permission:Customer.View,admin']);
            Route::get('/{id}/edit', [CustomerController::class, 'edit'])->middleware(['permission:Customer.Edit,admin']);
            Route::put('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->middleware(['permission:Customer.Delete,admin']);
            Route::get('/fetch/email', [CustomerController::class, 'get_email']);
            Route::get('/fetch/mobile', [CustomerController::class, 'get_mobile']);
            Route::get('/fetch/customer', [CustomerController::class, 'get_customers']);
            Route::get('/fetch/trip', [CustomerController::class, 'get_trips']);
            Route::get('/fetch/driver', [CustomerController::class, 'get_drivers']);
            Route::get('/delete/avatar/{id}', [CustomerController::class, 'delete_avatar']);
            Route::get('/block/{id}', [CustomerController::class, 'block_unblock_customer'])->middleware(['permission:Customer.Block,admin']);
        
		
		
		});
		
		
		/** --------------------------------------------------------------------------------------------------
         * Trip
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'trips'], function () {
            Route::get('/change-status/{id}', [TripController::class, 'change_status'])->middleware(['permission:Trip.Change-Status,admin']);
			Route::get('/exceldownload', [TripController::class, 'excel_download'])->middleware(['permission:Trip.Export,admin']);
            Route::get('/pdfdownload', [TripController::class, 'pdf_download'])->middleware(['permission:Trip.Export,admin']);
            Route::get('/refund/{id}', [TripController::class, 'refund_trip_amount']);
			
			Route::get('/', [TripController::class, 'index'])->middleware(['permission:Trip.List,admin']);
            Route::get('/list', [TripController::class, 'list']);
            Route::get('/{id}', [TripController::class, 'show'])->middleware(['permission:Trip.View,admin']);
            
            Route::get('/fetch/coupon', [TripController::class, 'get_coupons']);
            Route::get('/fetch/goods', [TripController::class, 'get_goods']);
            Route::get('/fetch/customer', [TripController::class, 'get_customers']);
            Route::get('/fetch/vehicle', [TripController::class, 'get_vehicles']);
			Route::get('/fetch/trip', [TripController::class, 'get_trips']);
            Route::get('/fetch/driver', [TripController::class, 'get_drivers']);
        });
		
		
		/** --------------------------------------------------------------------------------------------------
         * Service
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('service', [ServiceController::class, 'index'])->middleware(['permission:Service.List,admin']);
        Route::get('service/list', [ServiceController::class, 'list'])->middleware(['permission:Service.List,admin']);
        Route::get('service/{id}', [ServiceController::class, 'show'])->middleware(['permission:Service.View,admin']);
        Route::get('service/{id}/edit', [ServiceController::class, 'edit'])->middleware(['permission:Service.Edit,admin']);
        Route::put('service/{id}', [ServiceController::class, 'update'])->middleware(['permission:Service.Edit,admin']);
        //Route::delete('service/{id}', [SettingController::class, 'destroy'])->middleware(['permission:Setting.Delete,admin']);


		/** --------------------------------------------------------------------------------------------------
         * Trip
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'cancel-trips'], function () {
			Route::get('/exceldownload', [CancelTripController::class, 'excel_download'])->middleware(['permission:Cancel Trip.Export,admin']);
            Route::get('/pdfdownload', [CancelTripController::class, 'pdf_download'])->middleware(['permission:Cancel Trip.Export,admin']);
            Route::get('/', [CancelTripController::class, 'index'])->middleware(['permission:Cancel Trip.List,admin']);
            Route::get('/list', [CancelTripController::class, 'list']);
            Route::get('/{id}', [CancelTripController::class, 'show'])->middleware(['permission:Cancel Trip.View,admin']);
            Route::get('/{id}/edit', [CancelTripController::class, 'edit'])->middleware(['permission:Cancel Trip.Edit,admin']);
            
		    
			Route::get('/fetch/coupon', [CancelTripController::class, 'get_coupons']);
            Route::get('/fetch/goods', [CancelTripController::class, 'get_goods']);
            Route::get('/fetch/customer', [CancelTripController::class, 'get_customers']);
            Route::get('/fetch/vehicle', [CancelTripController::class, 'get_vehicles']);
			Route::get('/fetch/trip', [CancelTripController::class, 'get_trips']);
            Route::get('/fetch/driver', [CancelTripController::class, 'get_drivers']);
			
			Route::post('/driver/penalty', [CancelTripController::class, 'driver_penalty']);
			Route::post('/customer/refund', [CancelTripController::class, 'customer_refund']);
            
			
		});
		
		
		/** --------------------------------------------------------------------------------------------------
         * Trip
         * seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'refund-trips'], function () {
			Route::get('/exceldownload', [RefundController::class, 'excel_download'])->middleware(['permission:Refund.Export,admin']);
            Route::get('/pdfdownload', [RefundController::class, 'pdf_download'])->middleware(['permission:Refund.Export,admin']);
            Route::get('/', [RefundController::class, 'index'])->middleware(['permission:Refund.List,admin']);
            Route::get('/list', [RefundController::class, 'list']);
            
			Route::get('/fetch/coupon', [RefundController::class, 'get_coupons']);
            Route::get('/fetch/goods', [RefundController::class, 'get_goods']);
            Route::get('/fetch/customer', [RefundController::class, 'get_customers']);
            Route::get('/fetch/vehicle', [RefundController::class, 'get_vehicles']);
			Route::get('/fetch/trip', [RefundController::class, 'get_trips']);
            Route::get('/fetch/driver', [RefundController::class, 'get_drivers']);
        });
		
		
		  /** --------------------------------------------------------------------------------------------------
         * Incentives - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
         Route::group(['prefix' => 'incentives'], function () {
			Route::get('/fetch/driver', [IncentivesController::class, 'get_drivers']);
			
			Route::get('/exceldownload', [IncentivesController::class, 'excel_download'])->middleware(['permission:Incentives.Export,admin']);
			Route::get('/pdfdownload', [IncentivesController::class, 'pdf_download'])->middleware(['permission:Incentives.Export,admin']);
				
			Route::get('/', [IncentivesController::class, 'index'])->middleware(['permission:Incentives.List,admin']);
			Route::get('list', [IncentivesController::class, 'list'])->middleware(['permission:Incentives.List,admin']);
			Route::get('create', [IncentivesController::class, 'create'])->middleware(['permission:Incentives.Create,admin']);
			Route::post('/', [IncentivesController::class, 'store'])->middleware(['permission:Incentives.Create,admin']);
			Route::get('/{id}', [IncentivesController::class, 'show'])->middleware(['permission:Incentives.View,admin']);
			Route::get('/{id}/edit', [IncentivesController::class, 'edit'])->middleware(['permission:Incentives.Edit,admin']);
			Route::put('/{id}', [IncentivesController::class, 'update'])->middleware(['permission:Incentives.Edit,admin']);
			Route::delete('/{id}', [IncentivesController::class, 'destroy'])->middleware(['permission:Incentives.Delete,admin']);
           
		 });
		 
		 
		 
		 /** --------------------------------------------------------------------------------------------------
         * Driver Withdrawal - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
         Route::group(['prefix' => 'driver-earning'], function () {
			Route::post('/process/withdrawal-request', [DriverWithdrawalController::class, 'driver_withdrawal_operation']);
			Route::get('/fetch/driver', [DriverWithdrawalController::class, 'get_drivers']);
			Route::get('/exceldownload', [DriverWithdrawalController::class, 'excel_download'])->middleware(['permission:Driver Earning.Export,admin']);
			Route::get('/pdfdownload', [DriverWithdrawalController::class, 'pdf_download'])->middleware(['permission:Driver Earning.Export,admin']);
				
			Route::get('/', [DriverWithdrawalController::class, 'index'])->middleware(['permission:Driver Earning.List,admin']);
			Route::get('list', [DriverWithdrawalController::class, 'list'])->middleware(['permission:Driver Earning.List,admin']);
			Route::get('create', [DriverWithdrawalController::class, 'create'])->middleware(['permission:Driver Earning.Create,admin']);
			Route::post('/', [DriverWithdrawalController::class, 'store'])->middleware(['permission:Driver Earning.Create,admin']);
			Route::get('/{id}', [DriverWithdrawalController::class, 'show'])->middleware(['permission:Driver Earning.View,admin']);
			Route::get('/{id}/edit', [DriverWithdrawalController::class, 'edit'])->middleware(['permission:Driver Earning.Edit,admin']);
			Route::put('/{id}', [DriverWithdrawalController::class, 'update'])->middleware(['permission:Driver Earning.Edit,admin']);
			Route::delete('/{id}', [DriverWithdrawalController::class, 'destroy'])->middleware(['permission:Driver Earning.Delete,admin']);
           
		 });
		 
		 
		 /** 	 
		 /** --------------------------------------------------------------------------------------------------
         * Driver Payment History - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
         Route::group(['prefix' => 'driver-payment-history'], function () {
			Route::post('/process/withdrawal-request', [DriverPaymentHistoryController::class, 'driver_withdrawal_operation']);
			Route::get('/fetch/driver', [DriverPaymentHistoryController::class, 'get_drivers']);
			Route::get('/exceldownload', [DriverPaymentHistoryController::class, 'excel_download'])->middleware(['permission:Driver Payment History.Export,admin']);
			Route::get('/pdfdownload', [DriverPaymentHistoryController::class, 'pdf_download'])->middleware(['permission:Driver Payment History.Export,admin']);
			
			
			Route::get('/payout/exceldownload', [DriverPaymentHistoryController::class, 'payout_excel_download'])->middleware(['permission:Driver Earning.Export,admin']);
			Route::get('/payout/pdfdownload', [DriverPaymentHistoryController::class, 'payout_pdf_download'])->middleware(['permission:Driver Earning.Export,admin']);
				
			Route::get('/', [DriverPaymentHistoryController::class, 'index'])->middleware(['permission:Driver Payment History.List,admin']);
			Route::get('list', [DriverPaymentHistoryController::class, 'list'])->middleware(['permission:Driver Payment History.List,admin']);
			Route::get('create', [DriverPaymentHistoryController::class, 'create'])->middleware(['permission:Driver Payment History.Create,admin']);
			Route::post('/', [DriverPaymentHistoryController::class, 'store'])->middleware(['permission:Driver Payment History.Create,admin']);
			Route::get('/{id}', [DriverPaymentHistoryController::class, 'show'])->middleware(['permission:Driver Payment History.View,admin']);
			Route::get('/{id}/edit', [DriverPaymentHistoryController::class, 'edit'])->middleware(['permission:Driver Payment History.Edit,admin']);
			Route::put('/{id}', [DriverPaymentHistoryController::class, 'update'])->middleware(['permission:Driver Payment History.Edit,admin']);
			Route::delete('/{id}', [DriverPaymentHistoryController::class, 'destroy'])->middleware(['permission:Driver Payment History.Delete,admin']);
           
		 });
		 
		 
		 /** --------------------------------------------------------------------------------------------------
         * Notification - Master
         * @author seemashelar@neosao
         * --------------------------------------------------------------------------------------------------- */
         Route::group(['prefix' => 'notification'], function () {
		     Route::get('/fetch/customer', [NotificationController::class, 'get_customers']);
			 Route::get('/fetch/driver', [NotificationController::class, 'get_drivers']);
			
			 
			 Route::get('/', [NotificationController::class, 'index'])->middleware(['permission:Notification.List,admin']);
		     Route::post('/', [NotificationController::class, 'store']);
		 });
		 
		 
		 /** --------------------------------------------------------------------------------------------------
         * Enquiry - Master
         * @author shreyasm@neosao
         * --------------------------------------------------------------------------------------------------- */
        Route::get('enquiry', [EnquiryController::class, 'index'])->middleware(['permission:Enquiry.List,admin']);
        Route::get('enquiry/list', [EnquiryController::class, 'list'])->middleware(['permission:Enquiry.List,admin']);
		
		
    });
});
