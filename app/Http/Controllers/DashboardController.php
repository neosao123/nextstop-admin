<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
//models
use App\Models\Driver;
use App\Models\User;
use App\Models\Customer;
use App\Models\Trip;
// Helper
use App\Helpers\LogHelper;
use App\Models\AdminCommission;
use DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


    //seemashelar@neosao
    //dashboard count
    public function index(Request $r)
    {
        $admin = Auth::guard('admin')->user();
        if ($admin->role_id != 1 && !$admin->can('Dashboard.View')) {
            return redirect('welcome');
        }

        $user = User::where("is_delete", 0)
            ->where("role_id", "!=", 1)
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->count();
        // Count of verified driver (all statuses must be 1)
        $verifiedCount = Driver::where('driver_document_verification_status', 1)
            ->where('driver_vehicle_verification_status', 1)
            ->where('driver_training_video_verification_status', 1)
            ->where('admin_verification_status', 1)
            ->where('is_delete', 0)
            ->count();

        // Count of pending driver (any status is 0)
        $pendingCount = Driver::where('is_delete', 0)
            ->where(function ($query) {
                $query->where('driver_document_verification_status', 0)
                    ->orWhere('driver_vehicle_verification_status', 0)
                    ->orWhere('admin_verification_status', 0)
                    ->orWhere('driver_training_video_verification_status', 0);
            })
            ->count();
        //total customer		
        $totalCustomer = Customer::where("is_delete", 0)->count();

        //total booking
        $trips = Trip::whereIn("trip_status", ["pending", "accepted", "completed"])
            ->where("is_delete", 0)
            ->count();

        //total cancel trips
        $totalCancelTrip = Trip::whereIn("trip_status", ["cancelled"])
            ->where("is_delete", 0)
            ->count();

        // Commissions
        $totalCommission = AdminCommission::sum('commission_amount');
        $thisMonthCommission = AdminCommission::whereMonth('created_at', Carbon::now()->month)
                                              ->whereYear('created_at', Carbon::now()->year)
                                              ->sum('commission_amount');
        $todayCommission = AdminCommission::whereDate('created_at', Carbon::now()->toDateString())
                                          ->sum('commission_amount');

        return view("dashboard", compact("user", "verifiedCount", "pendingCount", "totalCustomer", "trips", "totalCancelTrip", "totalCommission", "thisMonthCommission", "todayCommission"));
    }
    //welcome page 

    public function welcome(Request $r)
    {
        return view("welcome");
    }

    //seemashelar@neosao
    //pie chart

    public function pie_chart(Request $r)
    {
        try {
            // Count of verified driver (all statuses must be 1)
            $verifiedCount = Driver::where('driver_document_verification_status', 1)
                ->where('driver_vehicle_verification_status', 1)
                ->where('driver_training_video_verification_status', 1)
                ->where('is_delete', 0)
                ->count();

            // Count of pending driver (any status is 0)
            $pendingCount = Driver::where('is_delete', 0)
                ->where(function ($query) {
                    $query->where('driver_document_verification_status', 0)
                        ->orWhere('driver_vehicle_verification_status', 0)
                        ->orWhere('driver_training_video_verification_status', 0);
                })
                ->count();


            // Prepare data for the pie chart
            $data = [
                'label' => ['Verified', 'Pending'],
                'data'  => [$verifiedCount, $pendingCount],
                'color' => ["#" . substr(md5(rand()), 0, 6), "#" . substr(md5(rand()), 0, 6)] // Random colors for each segment
            ];

            // Wrap the result in an array and encode as JSON
            $result_array = [
                'data' => $data
            ];

            echo json_encode($result_array);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while getting driver pie chart data', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        }
    }
    //seemashelar@neosao
    //get latest verified driver.
    public function driver_verified_list(Request $r)
    {
        try {
            $search = $r->input('search.value') ?? "";
            $limit = 10;
            $offset = 0;
            $srno = $offset + 1;
            $data = array();

            // Fetch driver with filtering
            $filteredData = Driver::filterVerifiedDriver($search, "10", $offset, array());
            $total = $filteredData['totalRecords'];
            $result = $filteredData['result'];

            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $carbonDate = Carbon::parse($row->created_at);
                    $formattedDate = $carbonDate->format('d-m-Y h:i:s A');
                    $action = '';

                    // Set account status
                    $account_status = '';
                    if ($row->is_driver_delete == 1) {
                        $account_status = '<div><span class="badge rounded-pill badge-soft-dark">' . __('index.deleted') . '</span></div>';
                    } else {
                        $account_status = $row->is_active == 1
                            ? '<div><span class="badge rounded-pill badge-soft-success">' . __('index.active') . '</span></div>'
                            : '<div><span class="badge rounded-pill badge-soft-danger">' . __('index.in_active') . '</span></div>';
                    }


                    // Append data to the array
                    $data[] = array(
                        $row->driver_first_name,
                        $row->driver_last_name,
                        optional(optional($row->vehicleDetails)->vehicle)->vehicle_name ?? '-', // Handles vehicle relationship safely
                        optional($row->serviceableZones)->serviceable_zone_name ?? '-', // Handles serviceable zone safely
                        $row->driver_phone,
                        $row->driver_email,
                        $account_status,
                        $formattedDate
                    );
                    $srno++;
                }
            }

            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data,
                "result" => $result
            ], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while the verified driver list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the verified driver list",
            ], 500);
        }
    }

    //seemashelar@neosao
    //get total trips by daily,monthly & weekly.

    public function total_trips_bar_chart(Request $request)
    {
        try {
            $data = ['xValues' => [], 'yValues' => []];
            $frequency = $request->input('frequency', 'monthly');

            if ($frequency === 'daily') {
                $startDate = now()->subDays(7)->startOfDay();
                $endDate = now()->endOfDay();

                $resTrips = Trip::select(DB::raw('count(id) as countTrips'), DB::raw('DATE(created_at) as date'))
                    ->whereIn("trip_status", ["pending", "accepted", "completed"])
                    ->where("is_delete", 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderByRaw('DATE(created_at) asc')
                    ->get();

                $dates = collect();
                for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                    $dates->put($date->toDateString(), 0);
                }

                foreach ($resTrips as $trip) {
                    $dates[$trip->date] = $trip->countTrips;
                }

                $data['xValues'] = $dates->keys()->toArray();
                $data['yValues'] = $dates->values()->toArray();
            } elseif ($frequency === 'weekly') {
                $startDate = now()->subWeeks(3)->startOfWeek();
                $endDate = now()->endOfWeek();

                $resTrips = Trip::select(DB::raw('count(id) as countTrips'), DB::raw('YEARWEEK(created_at) as week'))
                    ->whereIn("trip_status", ["pending", "accepted", "completed"])
                    ->where("is_delete", 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy(DB::raw('YEARWEEK(created_at)'))
                    ->orderByRaw('YEARWEEK(created_at) asc')
                    ->get();

                $weeks = collect();
                for ($date = $startDate; $date <= $endDate; $date->addWeek()) {
                    $weekStart = $date->copy()->startOfWeek()->format('d M');
                    $weekEnd = $date->copy()->endOfWeek()->format('d M');
                    $weekKey = $weekStart . ' - ' . $weekEnd;
                    $weeks->put($weekKey, 0);
                }

                foreach ($resTrips as $trip) {
                    $date = Carbon::createFromFormat('Y-m-d', substr($trip->week, 0, 4) . '-01-01')
                        ->addWeeks(substr($trip->week, 4) - 1)
                        ->startOfWeek();
                    $weekStart = $date->copy()->startOfWeek()->format('d M');
                    $weekEnd = $date->copy()->endOfWeek()->format('d M');
                    $weekKey = $weekStart . ' - ' . $weekEnd;
                    $weeks[$weekKey] = $trip->countTrips;
                }

                $data['xValues'] = $weeks->keys()->toArray();
                $data['yValues'] = $weeks->values()->toArray();
            } else { // Monthly
                $startDate = now()->subMonths(3)->startOfMonth();
                $endDate = now()->endOfMonth();

                $resTrips = Trip::select(DB::raw('count(id) as countTrips'), DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'))
                    ->whereIn("trip_status", ["pending", "accepted", "completed"])
                    ->where("is_delete", 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy(DB::raw('MONTH(created_at), YEAR(created_at)'))
                    ->orderByRaw('YEAR(created_at) asc, MONTH(created_at) asc')
                    ->get();

                $months = collect();
                for ($date = $startDate; $date <= $endDate; $date->addMonth()) {
                    $months->put($date->format('Y-m'), 0);
                }

                foreach ($resTrips as $trip) {
                    $monthKey = $trip->year . '-' . str_pad($trip->month, 2, '0', STR_PAD_LEFT);
                    $months[$monthKey] = $trip->countTrips;
                }

                $data['xValues'] = $months->keys()->map(fn($month) => Carbon::createFromFormat('Y-m', $month)->format('F Y'))->toArray();
                $data['yValues'] = $months->values()->toArray();
            }

            return response()->json(['data' => $data]);
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while fetching trip data', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the trip data",
            ], 500);
        }
    }

    //seemashelar@neosao
    //get latest cancel trips.
    public function cancel_trips(Request $r)
    {
        try {
            $search = $r->input('search.value') ?? "";
            $limit = 5;
            $offset = 0;
            $srno = $offset + 1;
            $data = array();

            // Fetch driver with filtering
            $filteredData = Trip::filterCancelTrips($search, $limit, $offset, array());
            $total = $filteredData['totalRecords'];
            $result = $filteredData['result'];

            if ($result && $result->count() > 0) {
                foreach ($result as $row) {
                    $formattedDate = Carbon::parse($row->created_at)->format('d-m-Y');

                    $status = match ($row->trip_status) {
                        'pending' => '<div><span class="badge rounded-pill badge-soft-warning">Pending</span></div>',
                        'completed' => '<div><span class="badge rounded-pill badge-soft-success">Completed</span></div>',
                        'cancelled' => '<div><span class="badge rounded-pill badge-soft-danger">Cancelled</span></div>',
                        'accepted' => '<div><span class="badge rounded-pill badge-soft-secondary">Accepted</span></div>',
                    };

                    $trip_no = '<div class="d-flex flex-column"><span><b>' . $row->id . '</b></span><small>' . $row->trip_unique_id . '</small> </div>';

                    $data[] = [
                        $trip_no,
                        $formattedDate,
                        $row->vehicle->vehicle_name ?? '-',
                        $row->customer->customer_first_name . ' ' . $row->customer->customer_last_name,
                        $row->driver ? $row->driver->driver_first_name . ' ' . $row->driver->driver_last_name : '-NA-',
                        $row->tripstatus->first()?->trip_action_type ?? '-',
                        $row->tripstatus->first()?->trip_status_reason ?? '-',
                        $status
                    ];
                    $srno++;
                }
            }

            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data,
                "result" => $result
            ], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while the cancel trips list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the cancel trips list",
            ], 500);
        }
    }

    public function delete_customer_process(Request $r)
    {
        // Render the Blade view as HTML
        $html = view('customerdelete')->render();

        // Return the HTML content with proper header
        return response($html)->header('Content-Type', 'text/html');
    }

    public function delete_driver_process(Request $r)
    {
        // Render the Blade view as HTML
        $html = view('driverdelete')->render();

        // Return the HTML content with proper header
        return response($html)->header('Content-Type', 'text/html');
    }
}
