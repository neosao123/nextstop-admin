<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Coupons;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\Customeraddress;

use App\Models\Driver;
use App\Models\TripStatus;
use App\Models\DriverEarning;
use App\Models\CustomerWalletTransction;
use App\Models\Rating;
use App\Models\AdminCommission;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;

// Helper
use App\Helpers\LogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use App\Classes\Phonepe;

class TripController extends Controller
{
	/*
	 *
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

	protected $user;
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->user = Auth::guard('admin')->user();
			return $next($request);
		});
	}

	/*
	 *  index page
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

	public function index()
	{
		try {
			return view('live-trip.index');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while loading the trip index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return redirect()->back()->with('error', 'An error occurred while loading the trip list.');
		}
	}

	/*
	 * list page
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

	public function list(Request $request)
	{

		try {
			$limit = $request->length;
			$offset = $request->start;
			$search = $request->input('search.value') ?? "";

			$filters = [
				'vehicle_id' => $request->vehicle_id ?? "",
				'driver_id' => $request->driver_id ?? "",
				'customer_id' => $request->customer_id ?? "",
				'goods_type_id' => $request->goods_type_id ?? "",
				'coupon_id' => $request->coupon_id ?? "",
				'unique_id' => $request->unique_id ?? "",
				'from_date' => $request->from_date ?? "",
				'to_date' => $request->to_date ?? "",
				'is_active' => $request->is_active ?? ""
			];

			$filteredData = Trip::filterTrips($search, $limit, $offset, $filters);

			$total = $filteredData['totalRecords'];
			$records = $filteredData['result'];

			$data = [];
			$srno = $offset + 1;

			// Check permissions once for all rows
			$canView = Auth::guard('admin')->user()->can('Trip.View');
			$canChangeStatus = Auth::guard('admin')->user()->can('Trip.Change-Status');
			$canDelete = Auth::guard('admin')->user()->can('Trip.Refund-Amount');
			$showActions = $canChangeStatus || $canView || $canDelete;


			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');

				$pickupaddress = $row->sourceAddress ? $row->sourceAddress->customeraddresses_name . ' ' . $row->sourceAddress->customeraddresses_address . ', ' . $row->sourceAddress->customeraddresses_mobile : '-';
				$drop_offaddress = $row->destinationAddress ? $row->destinationAddress->customeraddresses_name . ' ' . $row->destinationAddress->customeraddresses_address . ', ' . $row->destinationAddress->customeraddresses_mobile : '-';
				$dataRow = [];
				$status = match ($row->trip_status) {
					'pending' => '<div><span class="badge rounded-pill badge-soft-warning">Pending</span></div>',
					'completed' => '<div><span class="badge rounded-pill badge-soft-success">Completed</span></div>',
					'cancelled' => '<div><span class="badge rounded-pill badge-soft-danger">Cancelled</span></div>',
					'accepted' => '<div><span class="badge rounded-pill badge-soft-info">Accepted</span></div>',
				};
				if ($showActions) {
					$action = '<span class="text-start">'
						. '<div class="dropdown font-sans-serif position-static">'
						. '<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="trip-dropdown-' . $srno . '" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">'
						. '<span class="fas fa-ellipsis-h fs--1"></span>'
						. '</button>'
						. '<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="trip-dropdown-' . $srno . '">'
						. '<div class="bg-white py-2">';

					if ($this->user->can('Trip.View')) {
						$action .= '<a class="dropdown-item" href="' . url('trips/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
					}
					if ($this->user->can('Trip.Change-Status')) {
						$action .= '<a class="dropdown-item btn-changestatus" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="fas fa-angle-up"></i> Change Status</a>';
					}
					if ($row->trip_payment_mode == "online" && $row->trip_payment_status!="refund") {
						if ($this->user->can('Trip.Refund-Amount')) {
							$action .= '<a class="dropdown-item btn-refund-amount" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="fas fa-money"></i> Refund Amount</a>';
						}
					}
					$action .= '</div></div></div></span>';
					$dataRow[] = $action;
				}
				$trip_no = '<div class="d-flex flex-column"><span><b>' . $row->id . '</b></span><small>' . $row->trip_unique_id . '</small> </div>';
				$dataRow[] = $trip_no;
				//$dataRow[] = $row->trip_unique_id;
				$dataRow[] = $formattedDate;
				$dataRow[] = $row->vehicle->vehicle_name ?? '-';
				$dataRow[] = $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name;
				$dataRow[] = ($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : '');
				$dataRow[] = $row->coupon->coupon_code ?? '-';
				$dataRow[] = $pickupaddress;
				$dataRow[] = $drop_offaddress;
				$dataRow[] = $status;
				$data[] = $dataRow;
				$srno++;
			}

			return response()->json([
				"draw" => intval($request->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data
			], 200);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the trip list",
			], 500);
		}
	}

	/*
	 *  customer list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

	public function get_customers(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Customer::where(function ($query) use ($search) {
				$query->where('customer_first_name', 'like', '%' . $search . '%')
					->orWhere('customer_last_name', 'like', '%' . $search . '%')
					->orWhere('customer_phone', 'like', '%' . $search . '%');
			})
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$fullName = trim($item->customer_first_name . ' ' . $item->customer_last_name . ' ( ' . $item->customer_phone . ' ) ');
				$html[] = ['id' => $item->id, 'text' => $fullName];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the customers list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}

	/*
	 * goods list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	public function get_goods(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Goods::where('goods_name', 'like', '%' . $search . '%')
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$html[] = ['id' => $item->id, 'text' => $item->goods_name];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the goods list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}

	/*
	 * vehicle list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */

	public function get_vehicles(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Vehicle::where('vehicle_name', 'like', '%' . $search . '%')
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$html[] = ['id' => $item->id, 'text' => $item->vehicle_name];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the vehicles list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}

	/*
	 * unique id list
	 * seemashelar@neosao
	 * dt: 18-nov-2024
	 */
	public function get_trips(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Trip::where('trip_unique_id', 'like', '%' . $search . '%')
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$html[] = ['id' => $item->id, 'text' => $item->trip_unique_id];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the unique list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}

	public function get_coupons(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Coupons::where('coupon_code', 'like', '%' . $search . '%')
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$html[] = ['id' => $item->id, 'text' => $item->coupon_code];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the unique list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}


	/*
	 * vehicle list
	 * seemashelar@neosao
	 * dt: 1-jan-2025
	 */

	public function get_drivers(Request $request)
	{
		try {
			$html = [];
			$search = $request->input('search');

			$result = Driver::where(function ($query) use ($search) {
				$query->where('driver_first_name', 'like', '%' . $search . '%')
					->orWhere('driver_last_name', 'like', '%' . $search . '%')
					->orWhere('driver_phone', 'like', '%' . $search . '%');
			})
				->orderBy('id', 'DESC')
				->limit(20)
				->get();

			foreach ($result as $item) {
				$fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name . ' ( ' . $item->driver_phone . ' ) ');
				$html[] = ['id' => $item->id, 'text' => $fullName];
			}

			return response()->json($html);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the driver list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([]);
		}
	}

	public function excel_download(Request $r)
	{
		try {
			$filters = [
				'vehicle_id' => $r->vehicle_id ?? null,
				'driver_id' => $r->driver_id ?? null,
				'customer_id' => $r->customer_id ?? null,
				'goods_type_id' => $r->goods_type_id ?? null,
				'coupon_id' => $r->coupon_id ?? null,
				'unique_id' => $r->unique_id ?? null,
				'from_date' => $r->from_date ?? null,
				'to_date' => $r->to_date ?? null,
				'is_active' => $r->is_active ?? null
			];

			$search = $r->input('search.value') ?? "";
			$limit = $r->length ?? 0;
			$offset = $r->start ?? 0;


			$filteredData = Trip::filterTrips($search, $limit, $offset, $filters);
			$records = $filteredData['result'];

			if (empty($records)) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');
				$status = match ($row->trip_status) {
					'pending' => 'Pending',
					'completed' => 'Completed',
					'cancelled' => 'Cancelled',
					'accepted' => 'Accepted',
					default => 'Unknown',
				};

				$pickupaddress = $row->sourceAddress ? $row->sourceAddress->customeraddresses_name . ', ' . $row->sourceAddress->customeraddresses_address . ', ' . $row->sourceAddress->customeraddresses_mobile : '-';
				$drop_offaddress = $row->destinationAddress ? $row->destinationAddress->customeraddresses_name . ', ' . $row->destinationAddress->customeraddresses_address . ', ' . $row->destinationAddress->customeraddresses_mobile : '-';

				$csvData[] = [
					'Unique ID' => $row->trip_unique_id,
					'Vehicle' => $row->vehicle->vehicle_name ?? '-',
					'Customer' => $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name,
					'Driver' => ($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : ''),
					'Goods Type' => $row->goodtype->goods_name ?? '-',
					'Coupon' => $row->coupon->coupon_code ?? '-',
					'Pick Up Address' => $pickupaddress,
					'Drop-off Address' => $drop_offaddress,
					'Status' => $status,
					'Date' => $formattedDate
				];
			}

			$csvFileName = 'Trips_' . date('d-m-Y') . '.csv';
			$csvFile = fopen('php://temp', 'w+');
			fputcsv($csvFile, array_keys($csvData[0]));

			foreach ($csvData as $row) {
				fputcsv($csvFile, $row);
			}

			rewind($csvFile);
			$csvContent = stream_get_contents($csvFile);
			fclose($csvFile);

			$headers = [
				"Content-Type" => "text/csv",
				"Content-Disposition" => "attachment; filename=$csvFileName",
				"Pragma" => "no-cache",
				"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
				"Expires" => "0",
			];

			return response()->make($csvContent, 200, $headers);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while downloading the trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "An error occurred while generating the CSV file."], 500);
		}
	}


	public function pdf_download(Request $r)
	{
		try {
			$filters = [
				'vehicle_id' => $r->vehicle_id ?? null,
				'driver_id' => $r->driver_id ?? null,
				'customer_id' => $r->customer_id ?? null,
				'goods_type_id' => $r->goods_type_id ?? null,
				'coupon_id' => $r->coupon_id ?? null,
				'unique_id' => $r->unique_id ?? null,
				'from_date' => $r->from_date ?? null,
				'to_date' => $r->to_date ?? null,
				'is_active' => $r->is_active ?? null
			];

			$search = $r->input('search.value') ?? "";
			$limit = $r->length ?? 0;
			$offset = $r->start ?? 0;

			$filteredData = Trip::filterTrips($search, $limit, $offset, $filters);
			$records = $filteredData['result'];

			$htmlContent = '<style>
						table {
							width: 100%;
							border-collapse: collapse;
						}
						table, th, td {
							border: 1px solid black;
							padding: 4px;
							font-size: 12px;
							text-align: left;
						}
						.badge-soft-success {
							color: #28a745;
						}
						.badge-soft-danger {
							color: #dc3545;
						}
					</style>';

			$htmlContent .= '<table>';
			$htmlContent .= '<thead>
								<tr>
									<th>Unique ID</th>
									<th>Vehicle</th>
									<th>Customer</th>
									<th>Driver</th>
									<th>Goods Type</th>
									<th>Coupon</th>
									<th>Pick Up Address</th>
									<th>Drop-off Address</th>
									<th>Status</th>
									<th>Date</th>
								</tr>
							</thead>';
			$htmlContent .= '<tbody>';

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');
				$status = match ($row->trip_status) {
					'pending' => 'Pending',
					'completed' => 'Completed',
					'cancelled' => 'Cancelled',
					'accepted' => 'Accepted',
					default => 'Unknown',
				};
				$pickupaddress = $row->sourceAddress ? $row->sourceAddress->customeraddresses_name . ', ' . $row->sourceAddress->customeraddresses_address . ', ' . $row->sourceAddress->customeraddresses_mobile : '-';
				$drop_offaddress = $row->destinationAddress ? $row->destinationAddress->customeraddresses_name . ', ' . $row->destinationAddress->customeraddresses_address . ', ' . $row->destinationAddress->customeraddresses_mobile : '-';

				$htmlContent .= '<tr>';
				$htmlContent .= '<td>' . $row->trip_unique_id . '</td>';
				$htmlContent .= '<td>' . ($row->vehicle->vehicle_name ?? '-') . '</td>';
				$htmlContent .= '<td>' . $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name . '</td>';
				$htmlContent .= '<td>' . ($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : '') . '</td>';
				$htmlContent .= '<td>' . ($row->goodtype->goods_name ?? '-') . '</td>';
				$htmlContent .= '<td>' . ($row->coupon->coupon_code ?? '-') . '</td>';
				$htmlContent .= '<td>' . ($pickupaddress ?? '-') . '</td>';
				$htmlContent .= '<td>' . ($drop_offaddress ?? '-') . '</td>';
				$htmlContent .= '<td>' . $status . '</td>';
				$htmlContent .= '<td>' . $formattedDate . '</td>';
				$htmlContent .= '</tr>';
			}

			$htmlContent .= '</tbody></table>';

			$pdf = PDF::loadHTML($htmlContent);

			return $pdf->download('Trips.pdf');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while downloading the trip list as PDF', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "An error occurred while generating the PDF file."], 500);
		}
	}

	/*
	 * show trip
	 * seemashelar@neosao
	 * dt: 16-dec-2024
	 */
	public function show(string $id)
	{
		try {
			$trip = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'rating', 'sourceAddress', 'destinationAddress')
				->where('id', $id)
				->where('is_delete', 0)
				->first();

			$customerStopAddress = Customeraddress::with('trip')
				->where("customeraddresses_trip_id", $id)
				->where("customeraddresses_type", "stop")
				->where("is_delete", 0)
				->get();

			$vehicleDetails = Driver::with('vehicleDetails.vehicle')
				->where("id", $trip->trip_driver_id)
				->first();

			$DriverEarning = DriverEarning::where("driver_id", $trip->trip_driver_id)
				->where("trip_id", $id)
				->get();


			$adminCommission = AdminCommission::where("driver_id", $trip->trip_driver_id)
				->where("trip_id", $id)
				->first();

		$tripStatus = TripStatus::leftJoin("drivers", "drivers.id", "=", "trip_statuses.trip_action_by")
				->where("trip_id", $id)
				->orderBy("trip_statuses.id", "DESC")
				->select(
					"trip_statuses.id",
					"trip_statuses.trip_status_short",
					"trip_statuses.trip_status_reason",
					"trip_statuses.trip_action_type",
					"trip_statuses.created_at",
					"drivers.driver_first_name",
					"drivers.driver_last_name"
				)
				->get();

			if (!$trip) {
				// Log the error
				LogHelper::logError('An error occurred while view the trip', 'The invalid trip',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				// Return error response to the user
				return redirect()->back()->with('error', 'The invalid trip.');
			}
			return view('live-trip.show', compact('trip', 'customerStopAddress', 'vehicleDetails', 'DriverEarning', 'adminCommission','tripStatus'));
		} catch (\Exception $ex) {

			// Log the error
			LogHelper::logError('An error occurred while view the trip', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while view the trip.');
		}
	}



	/*
	 * change trip status
	 * seemashelar@neosao
	 * dt: 1-jan-2024
	 */
	public function change_status(Request $r, string $id)
	{
		try {
			// Find the trip
			$trip = Trip::find($id);

			// Check if the trip exists
			if (!$trip) {

				//log error
				LogHelper::logError('An error occurred while change status of trip', 'Trip not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
				// Return error response
				return response()->json([
					'success' => false,
					'error' => 'Trip not found.'
				]);
			}

			$trip->trip_status = $r->status;
			$trip->save();

			$tripStatus = new TripStatus;
			$tripStatus->trip_id = $id;
			$tripStatus->trip_status_short = $r->status;
			$tripStatus->trip_status_description = $r->reason;
			$tripStatus->trip_action_type = "admin";
			if ($r->status == "completed") {
				$tripStatus->trip_status_reason = "Trip is completed by admin";
				$tripStatus->trip_status_title = "completed";
			}
			if ($r->status == "cancelled") {
				$tripStatus->trip_status_reason = "Trip is cancelled by admin";
				$tripStatus->trip_status_title = "cancelled";
				
				
				// Changed API endpoint to include the specific property ID
				$apiUri = env("SOCKET_URL") . "clear/order/" . $id;

				// Since we're making a GET request for a specific resource, we don't need query params
				$response = Http::get($apiUri);
				$responseData = json_decode($response->body(), true);
				Log::info("CLEAR_ORDER_API_RESPONSE => " . $response->body());
					
			}
			$tripStatus->save();

			// Return success response
			return response()->json(['success' => true]);
		} catch (\Exception $ex) {
			// Log the error and return error response
			LogHelper::logError('An error occurred while change status of trip', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
				"message" => "An error occurred while deleting the user",
			], 500);
		}
	}


	public function refund_trip_amount(Request $r)
	{
		try {
			// Find the trip id
			$trip = Trip::find($r->id);

			// Check if the trip exists
			if (!$trip) {

				//log error
				LogHelper::logError('An error occurred while refund amount', 'Trip is not found.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
				// Return error response
				return response()->json([
					'success' => false,
					'error' => 'Trip Not found.'
				]);
			}

			$prefix = (env('PAY_MODE') === 'TEST') ? 'T' : 'P';
			$merchantRefundId = $prefix . uniqid() . random_int(1000, 9999);


			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->initiate_refund($merchantRefundId, $trip->trip_payment_id, $trip->trip_total_amount);

			if ($phonepeResult["success"] == true) {

				$refundResult = $phonepe->check_refund_status($phonepeResult["merchantRefundId"]);

				if ($refundResult["success"] == true) {

					
					$trip->update(["refund_webhook_response" => $refundResult,"trip_status"=>"cancelled","trip_payment_status"=>"refund"]);
					
					//trip status
					
					$tripStatus = new TripStatus;
					$tripStatus->trip_id = $trip->id;
					$tripStatus->trip_status_short = "cancelled";
					$tripStatus->trip_status_description = "Trip is cancelled by customer due to payment refund.";
					$tripStatus->trip_action_type = "customer";					
					$tripStatus->trip_status_reason = "Trip is cancelled by customer due to payment refund.";
					$tripStatus->trip_status_title = "cancelled";					
					$tripStatus->save();
					
					
					return response()->json(['success' => true]);
				}
				return response()->json(['success' => false]);
			}
			return response()->json(['success' => false]);
		} catch (\Exception $ex) {
			// Log the error and return error response
			LogHelper::logError('An error occurred while refund amount', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
				"message" => "An error occurred while while refund amount",
			], 500);
		}
	}
}
