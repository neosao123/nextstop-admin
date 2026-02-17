<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\GoodsType;
use App\Models\Customeraddress;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\DriverTransaction;
use App\Models\CustomerWalletTransaction;
use App\Models\TripStatus;
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

class RefundController extends Controller
{
    /*
	 *
	 * seemashelar@neosao
	 * dt: 08-Jan-2025
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
	 * dt: 8-jan-2025
	 */
	
	public function index()
	{
		try {
			return view('refund.index');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while loading the refund trip index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return redirect()->back()->with('error', 'An error occurred while loading the refund trip list.');
		}
	}
	
	
	/*
	 * list page
	 * seemashelar@neosao
	 * dt: 08-Jan-2025
	 */

	public function list(Request $request)
	{
	
		try {
			$limit = $request->length;
			$offset = $request->start;
			$search = $request->input('search.value') ?? "";

			$filters = [
				'vehicle_id' => $request->vehicle_id ?? null,
				'driver_id' => $request->driver_id ?? null,
				'customer_id' => $request->customer_id ?? null,
				'goods_type_id' => $request->goods_type_id ?? null,
				'coupon_id' => $request->coupon_id ?? null,
				'unique_id' => $request->unique_id ?? null
			];

			$filteredData = Trip::filterRefund($search, $limit, $offset, $filters);

			$total = $filteredData['totalRecords'];
			$records = $filteredData['result'];

			$data = [];
			$srno = $offset + 1;

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->transaction->first()?->created_at)->format('d-m-Y');
				$status = match ($row->trip_status) {
					'pending' => '<div><span class="badge rounded-pill badge-soft-warning">Pending</span></div>',
					'completed' => '<div><span class="badge rounded-pill badge-soft-success">Completed</span></div>',
					'cancelled' => '<div><span class="badge rounded-pill badge-soft-danger">Cancelled</span></div>',
					'accepted' => '<div><span class="badge rounded-pill badge-soft-secondary">Accepted</span></div>',
				};
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

				$data[] = [
				    $row->trip_unique_id,
					$formattedDate ,
					$row->vehicle->vehicle_name ?? '-',
					$row->customer->customer_first_name . ' ' . $row->customer->customer_last_name,
					($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : ''),
					$row->trip_total_amount,
					$row->trip_total_amount,				
					$status,
					$action
				];
				$srno++;
			}

			return response()->json([
				"draw" => intval($request->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data
			], 200);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while fetching the cancel trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the cancel trip list",
			], 500);
		}
	}
	
	/*
	 *  customer list
	 * seemashelar@neosao
	 * dt: 8-jan-2025
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
				$fullName = trim($item->customer_first_name . ' ' . $item->customer_last_name . ' ( '.$item->customer_phone.' ) ');
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
	 * dt: 8-jan-2025
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
	 * dt: 8-jan-2025
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
	 * dt: 8-jan-2025
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
	 * dt: 8-jan-2025
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
				$fullName = trim($item->driver_first_name . ' ' . $item->driver_last_name.' ( '.$item->driver_phone.' ) ');
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
				'unique_id' => $r->unique_id ?? null
			];

			$search = $r->input('search.value') ?? "";
			$limit = $r->length ?? 0;
			$offset = $r->start ?? 0;


			$filteredData = Trip::filterRefund($search, $limit, $offset, $filters);
			$records = $filteredData['result'];

			if (empty($records)) {
				return response()->json(["message" => "No data available for download."], 204);
			}

			$csvData = [];
			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->transaction->first()?->created_at)->format('d-m-Y');
				$status = match ($row->trip_status) {
					'pending' => 'Pending',
					'completed' => 'Completed',
					'cancelled' => 'Cancelled',
					'accepted' => 'Accepted',
					default => 'Unknown',
				};
               
				$csvData[] = [
					'Unique ID' => $row->trip_unique_id,
					'Date' => $formattedDate,
					'Vehicle' => $row->vehicle->vehicle_name ?? '-',
					'Customer' => $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name,
					'Driver'=>($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : ''),
					'Total Amount'=>$row->trip_total_amount,
					'Refund Amount'=>$row->trip_total_amount,
					'Status' => $status,
					
				];
			}

			$csvFileName = 'Refunds_' . date('d-m-Y') . '.csv';
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
			LogHelper::logError('An error occurred while downloading the refund trip list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
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
				'unique_id' => $r->unique_id ?? null
				
			];

			$search = $r->input('search.value') ?? "";
			$limit = $r->length ?? 0;
			$offset = $r->start ?? 0;

			$filteredData = Trip::filterRefund($search, $limit, $offset, $filters);
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
									<th>Date</th>
									<th>Vehicle</th>
									<th>Customer</th>
									<th>Driver</th>	
                                    <th>Total Amount</th>
                                    <th>Refund Amount</th>									
									<th>Status</th>
								</tr>
							</thead>';
			$htmlContent .= '<tbody>';

			foreach ($records as $row) {
				$formattedDate = Carbon::parse($row->transaction->first()?->created_at)->format('d-m-Y');
				$status = match ($row->trip_status) {
					'pending' => 'Pending',
					'completed' => 'Completed',
					'cancelled' => 'Cancelled',
					'accepted' => 'Accepted',
					default => 'Unknown',
				};
				
				$htmlContent .= '<tr>';
				$htmlContent .= '<td>' . $row->trip_unique_id . '</td>';
				$htmlContent .= '<td>' . $formattedDate . '</td>';
				$htmlContent .= '<td>' . ($row->vehicle->vehicle_name ?? '-') . '</td>';
				$htmlContent .= '<td>' . $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name . '</td>';
				$htmlContent .= '<td>' .($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : '').'</td>';
				$htmlContent .= '<td>' . $row->trip_total_amount . '</td>';
				$htmlContent .= '<td>' . $row->trip_total_amount ?? '-' . '</td>';
				$htmlContent .= '<td>' . $status . '</td>';
				
				$htmlContent .= '</tr>';
			}

			$htmlContent .= '</tbody></table>';

			$pdf = PDF::loadHTML($htmlContent);

			return $pdf->download('refunds.pdf');
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while downloading the refund list as PDF', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json(["message" => "An error occurred while generating the PDF file."], 500);
		}
	}

	
	
}
