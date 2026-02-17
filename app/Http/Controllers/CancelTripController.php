<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\Customeraddress;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\DriverEarning;
use App\Models\CustomerWalletTransaction;
use App\Models\TripStatus;
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

class CancelTripController extends Controller
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
	 * dt: 1-jan-2025
	 */

	public function index()
	{
		try {
			return view('cancel-trip.index');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while loading the cancel trip index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return redirect()->back()->with('error', 'An error occurred while loading the cancel trip list.');
		}
	}

	/*
	 * list page
	 * seemashelar@neosao
	 * dt: 1-jan-2025
	 */

	public function list(Request $request)
	{

		//try {
		$limit = $request->length;
		$offset = $request->start;
		$search = $request->input('search.value') ?? "";

		$filters = [
			'vehicle_id' => $request->vehicle_id ?? null,
			'driver_id' => $request->driver_id ?? null,
			'customer_id' => $request->customer_id ?? null,
			'goods_type_id' => $request->goods_type_id ?? null,
			'coupon_id' => $request->coupon_id ?? null,
			'unique_id' => $request->unique_id ?? null,
			'from_date' => $request->from_date ?? null,
			'to_date' => $request->to_date ?? null
		];

		$filteredData = Trip::filterCancelTrips($search, $limit, $offset, $filters);

		$total = $filteredData['totalRecords'];
		$records = $filteredData['result'];

		$data = [];
		$srno = $offset + 1;

		// Check permissions once for all rows
		$canView = Auth::guard('admin')->user()->can('Cancel Trip.View');
		$canEdit = Auth::guard('admin')->user()->can('Cancel Trip.Edit');
		$showActions = $canEdit || $canView;



		foreach ($records as $row) {
			$dataRow = [];

			$cancelledStatus = $row->tripstatus->where('trip_status_title', 'cancelled')->last();


			$formattedDate = Carbon::parse($row->created_date)->format('d-m-Y h:i A');
			$pickupaddress = $row->sourceAddress ? $row->sourceAddress->customeraddresses_name . ', ' . $row->sourceAddress->customeraddresses_address . ', ' . $row->sourceAddress->customeraddresses_mobile : '-';
			$drop_offaddress = $row->destinationAddress ? $row->destinationAddress->customeraddresses_name . ', ' . $row->destinationAddress->customeraddresses_address . ', ' . $row->destinationAddress->customeraddresses_mobile : '-';
			$status = match ($row->trip_status) {
				'pending' => '<div><span class="badge rounded-pill badge-soft-warning">Pending</span></div>',
				'completed' => '<div><span class="badge rounded-pill badge-soft-success">Completed</span></div>',
				'cancelled' => '<div><span class="badge rounded-pill badge-soft-danger">Cancelled</span></div>',
				'accepted' => '<div><span class="badge rounded-pill badge-soft-secondary">Accepted</span></div>',
			};
			if ($showActions) {
				$action = '<span class="text-end">'
					. '<div class="dropdown font-sans-serif position-static">'
					. '<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="trip-dropdown-' . $srno . '" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">'
					. '<span class="fas fa-ellipsis-h fs--1"></span>'
					. '</button>'
					. '<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="trip-dropdown-' . $srno . '">'
					. '<div class="bg-white py-2">';

				if ($this->user->can('Cancel Trip.View')) {
					$action .= '<a class="dropdown-item" href="' . url('cancel-trips/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
				}
				if ($this->user->can('Cancel Trip.Edit')) {
					$action .= '<a class="dropdown-item btn-edit" href="' . url('cancel-trips/' . $row['id'] . '/edit') . '"> <i class="fas fa-edit"></i> Edit</a>';;
				}
				$action .= '</div></div></div></span>';
				$dataRow[] = $action;
			}
			$trip_no = '<div class="d-flex flex-column"><span><b>' . $row->id . '</b></span><small>' . $row->trip_unique_id . '</small> </div>';
			$dataRow[] = $trip_no;
			//$dataRow[] = $row->trip_unique_id;
			$dataRow[] =  $formattedDate;
			$dataRow[] =  $row->vehicle->vehicle_name ?? '-';
			$dataRow[] =  $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name;
			$dataRow[] =  $cancelledStatus->trip_action_type ?? '-';
			$dataRow[] =  $cancelledStatus->trip_status_reason ?? '-';
			$dataRow[] =  $status;
			$data[] = $dataRow;
			$srno++;
		}

		return response()->json([
			"draw" => intval($request->draw),
			"recordsTotal" => $total,
			"recordsFiltered" => $total,
			"data" => $data
		], 200);
		/*} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the cancel trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the cancel trip list",
			], 500);
		}*/
	}

	/*
	 *  customer list
	 * seemashelar@neosao
	 * dt: 1-jan-2025
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
	 * dt: 1-jan-2025
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
	 * dt: 1-jan-2025
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
	 * dt: 1-jan-2025
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
				->where('is_delete', 0)
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
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');
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
					'Cancel By' => $row->trip_action_type,
					'Reason' => $row->trip_status_reason,
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
			LogHelper::logError('An error occurred while downloading the cancel trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
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
                                    <th>Cancel By</th>
                                    <th>Reason</th>									
									<th>Status</th>
									<th>Date</th>
								</tr>
							</thead>';
			$htmlContent .= '<tbody>';

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');
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
				$htmlContent .= '<td>' . $row->trip_action_type . '</td>';
				$htmlContent .= '<td>' . $row->trip_status_reason . '</td>';
				$htmlContent .= '<td>' . $status . '</td>';
				$htmlContent .= '<td>' . $formattedDate . '</td>';
				$htmlContent .= '</tr>';
			}

			$htmlContent .= '</tbody></table>';

			$pdf = PDF::loadHTML($htmlContent);

			return $pdf->download('Trips.pdf');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while downloading the cancel trip list as PDF', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "An error occurred while generating the PDF file."], 500);
		}
	}

	/*
	 * show trip
	 * seemashelar@neosao
	 * dt: 1-jan-2025
	 */
	public function show(string $id)
	{

		try {
			$trip = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'rating', 'sourceAddress', 'destinationAddress')
				->select("trips.*", "trip_statuses.trip_action_type", "trip_statuses.trip_status_short", "trip_statuses.trip_status_reason", "trip_statuses.created_at as cancel_time")
				->join("trip_statuses", "trip_statuses.trip_id", "=", "trips.id")
				->where('trips.id', $id)
				->where('trips.is_delete', 0)
				->where('trip_status_title', 'cancelled')
				//->whereIn('trip_status', ['cancelled'])
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



			$customerStopAddress = Customeraddress::with('trip')
				->where("customeraddresses_trip_id", $id)
				->where("customeraddresses_type", "stop")
				->where("is_delete", 0)
				->get();

			$vehicleDetails = Driver::with('vehicleDetails.vehicle')
				->where("id", $trip->trip_driver_id ?? "")
				->first();
			$penalty = DriverEarning::where("trip_id", $id)
				->where("driver_id", $trip->trip_driver_id ?? "")
				->where("type", "penalty")
				->first();
			$customerPenalty = CustomerWalletTransaction::where("trip_id", $id)
				->where("customer_id", $trip->trip_customer_id ?? "")
				->where("type", "deduction")
				->first();

			$refundAmount = CustomerWalletTransaction::where("trip_id", $id)
				->where("customer_id", $trip->trip_customer_id ?? "")
				->where("type", "refund")
				->first();
			if (!$trip) {
				// Log the error
				LogHelper::logError('An error occurred while show the trip', 'The invalid trip',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				// Return error response to the user
				return redirect()->back()->with('error', 'The invalid trip.');
			}
			return view('cancel-trip.show', compact('trip', 'customerStopAddress', 'vehicleDetails', 'penalty', 'customerPenalty', 'refundAmount', 'tripStatus'));
		} catch (\Exception $ex) {

			// Log the error
			LogHelper::logError('An error occurred while view the trip', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response to the user
			return redirect()->back()->with('error', 'An error occurred while view the trip.');
		}
	}


	/*
	 * edit trip
	 * seemashelar@neosao
	 * dt: 1-jan-2025
	 */
	public function edit(string $id)
	{

		//try {
		$trip = Trip::with('vehicle', 'customer', 'goodtype', 'coupon', 'driver', 'rating', 'sourceAddress', 'destinationAddress')
			->select("trips.*", "trip_statuses.trip_action_type", "trip_statuses.trip_status_short", "trip_statuses.trip_status_reason", "trip_statuses.created_at as cancel_time")
			->join("trip_statuses", "trip_statuses.trip_id", "=", "trips.id")
			->where('trips.id', $id)
			->where('trips.is_delete', 0)
			->where('trip_status_title', 'cancelled')

			->first();


		$tripStatus = TripStatus::leftJoin("drivers", "drivers.id", "=",  "trip_statuses.trip_action_by")
			->where("trip_id", $id)->get();


		$customerStopAddress = Customeraddress::with('trip')
			->where("customeraddresses_trip_id", $id)
			->where("customeraddresses_type", "stop")
			->where("is_delete", 0)
			->get();

		$vehicleDetails = Driver::with('vehicleDetails.vehicle')
			->where("id", $trip->trip_driver_id ?? "")
			->first();
		//driver penalty

		$driverpenalty = DriverEarning::where("trip_id", $id)
			->where("driver_id", $trip->trip_driver_id ?? "")
			->where("type", "penalty")
			->first();

		$getDriverPenalty = Setting::where("id", 3)->first();
		$driverPenaltyPer = 0;
		if (!empty($getDriverPenalty)) {
			$driverPenaltyPer = $getDriverPenalty->setting_value;
		}
		$driverPenaltyAmount = ($trip->trip_total_amount ?? 0 * $driverPenaltyPer) / 100;

		//customer refund by admin

		$getCustomerPenalty = Setting::where("id", 5)->first();
		$customerPenaltyPer = 0;
		if (!empty($getCustomerPenalty)) {
			$customerPenaltyPer = $getCustomerPenalty->setting_value;
		}

		$customerpenalty = CustomerWalletTransaction::where("trip_id", $id)
			->where("customer_id", $trip->trip_customer_id ?? "")
			->where("type", "deduction")
			->first();

		$refundAmount = CustomerWalletTransaction::where("trip_id", $id)
			->where("customer_id", $trip->trip_customer_id ?? "")
			->where("type", "refund")
			->first();
		if (!$trip) {
			// Log the error
			LogHelper::logError('An error occurred while edit the trip', 'The invalid trip',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
			// Return error response to the user
			return redirect()->back()->with('error', 'The invalid trip.');
		}
		return view('cancel-trip.edit', compact('tripStatus', 'trip', 'customerStopAddress', 'vehicleDetails', 'driverpenalty', 'driverPenaltyAmount', 'driverPenaltyPer', 'customerPenaltyPer', 'customerpenalty', 'refundAmount'));
		/*} catch (\Exception $ex) {
           
            // Log the error
            LogHelper::logError('An error occurred while edit the trip', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
		    // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the trip.');
        }*/
	}

	public function driver_penalty(Request $r)
	{
		try {

			// Find the trip
			$trip = Trip::find($r->tripId);

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

			$driverEarning = new DriverEarning;
			$driverEarning->trip_id = $r->tripId;
			$driverEarning->driver_id = $r->driverId;
			$driverEarning->type = "penalty";
			$driverEarning->message = $r->penaltyAmount . ' penalty is applied against trip - ' . $trip->trip_unique_id . ' at ' . $r->driverPenaltyPer . '%.';
			$driverEarning->amount = $r->penaltyAmount;
			$driverEarning->status = "success";
			$driverEarning->save();

			LogHelper::logSuccess('Driver penalty recorded successfully', [
				'trip_id' => $r->tripId,
				'driver_id' => $r->driverId,
				'amount' => $r->penaltyAmount,
			], __FUNCTION__, basename(__FILE__), __LINE__);

			// Update driver's wallet
			$driver = Driver::find($r->driverId);
			if ($driver) {
				$driver->driver_wallet -= $r->penaltyAmount; // Remove penalty from driver wallet
				$driver->save();

				LogHelper::logSuccess('Driver wallet updated successfully', [
					'driver_id' => $r->driverId,
					'updated_wallet_amount' => $driver->driver_wallet,
				], __FUNCTION__, basename(__FILE__), __LINE__);
			}

			return response()->json(['success' => true]);
		} catch (\Exception $ex) {

			// Log the error and return error response
			LogHelper::logError('An error occurred while giving penalty to the driver', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
				"message" => "An error occurred while giving penalty to the driver",
			], 500);
		}
	}

	public function customer_refund(Request $r)
	{
		try {
			// Find the trip
			$trip = Trip::find($r->tripId);

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

			$finalAmount = $trip->trip_total_amount - $r->penaltyAmount;
			
			$prefix = (env('PAY_MODE') === 'TEST') ? 'T' : 'P';
			$merchantRefundId = $prefix . uniqid() . random_int(1000, 9999);

			$phonepe = new Phonepe;
			$phonepeResult = $phonepe->initiate_refund($merchantRefundId, $trip->trip_payment_id, $trip->trip_total_amount);

			/*$customerWalletTransaction = new CustomerWalletTransaction;
			$customerWalletTransaction->trip_id = $r->tripId;
			$customerWalletTransaction->customer_id = $r->customerId;
			$customerWalletTransaction->type = "refund";
			$customerWalletTransaction->message = $finalAmount . ' refund is applied against trip - ' . $trip->trip_unique_id . ' at ' . $r->customerPenaltyPer . '%.';
			$customerWalletTransaction->amount = $finalAmount;
			$customerWalletTransaction->status = "success";
			$customerWalletTransaction->save();*/
			
		    LogHelper::logSuccess('Customer Refund recorded successfully', [
				'trip_id' => $r->tripId,
				'customer_id' => $r->customerId,
				'amount' => $finalAmount,
			], __FUNCTION__, basename(__FILE__), __LINE__);

			// Update customer's wallet
			$customer = Customer::find($r->customerId);
			if ($customer) {
				$customer->customer_wallet_balance += $r->penaltyAmount; // Remove penalty from driver wallet
				$customer->save();

				LogHelper::logSuccess('Customer wallet updated successfully', [
					'customer_id' => $r->customerId,
					'updated_wallet_amount' => $customer->customer_wallet_balance,
				], __FUNCTION__, basename(__FILE__), __LINE__);
			}

			
			if ($phonepeResult["success"] == true) {

				$refundResult = $phonepe->check_refund_status($phonepeResult["merchantRefundId"]);

				if ($refundResult["success"] == true) {

					$getTransaction = CustomerWalletTransction::where("payment_id", $trip->trip_payment_id)
						->first();
					$trip->update(["refund_webhook_response" => $refundResult]);
					if (!empty($getTransaction)) {
						$getTransaction->update(["payment_status" => "refund"]);
					}
					return response()->json(['success' => true]);
				}
				return response()->json(['success' => false]);
			}
			return response()->json(['success' => false]);
			
		} catch (\Exception $ex) {

			// Log the error and return error response
			LogHelper::logError('An error occurred while giving refund to the customer', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			// Return error response
			return response()->json([
				"message" => "An error occurred while giving refund to the customer",
			], 500);
		}
	}
}
