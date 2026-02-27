<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminCommission;
use App\Models\Driver;
use App\Helpers\LogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminCommissionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $totalCommission = AdminCommission::sum('commission_amount');
            $thisMonthCommission = AdminCommission::whereMonth('created_at', Carbon::now()->month)
                                                  ->whereYear('created_at', Carbon::now()->year)
                                                  ->sum('commission_amount');
            $todayCommission = AdminCommission::whereDate('created_at', Carbon::now()->toDateString())
                                              ->sum('commission_amount');

            return view('reports.commission.index', compact('totalCommission', 'thisMonthCommission', 'todayCommission'));
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while loading the commission index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return redirect()->back()->with('error', 'An error occurred while loading the commission list.');
        }
    }

    public function list(Request $request)
    {
        try {
            $limit = $request->length;
            $offset = $request->start;
            $search = $request->input('search.value') ?? "";
            $driver = $request->driver ?? "";
            $from_date = $request->from_date ?? ""; // Expecting d-m-Y
            $to_date = $request->to_date ?? "";     // Expecting d-m-Y

            $filteredData = AdminCommission::filterCommissions($search, $limit, $offset, $driver, $from_date, $to_date);

            $total = $filteredData['totalRecords'];
            $records = $filteredData['result'];

            $data = [];
            $srno = $offset + 1;
            
            $totalCommissionLimit = 0;

            foreach ($records as $row) {
                $createdDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');
                
                $dataRow = [];
                // No actions needed for read-only report, but we can add view if needed.
                // Assuming "report" implies read-only.
                
                $dataRow[] = $row->trip ? $row->trip->trip_unique_id : '-'; // Trip ID
                $dataRow[] = ($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : '');
                $dataRow[] = $row->type ?? '-';
                $dataRow[] = $row->commission_percentage ?? '0';
                $dataRow[] = $row->commission_amount ?? '0';
                $dataRow[] = $row->grand_total ?? '0';
                $dataRow[] = $createdDate;

                $data[] = $dataRow;
                $srno++;
                $totalCommissionLimit += (float)($row->commission_amount ?? 0);
            }

            return response()->json([
                "draw" => intval($request->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data,
                "totalCommissionLimit" => number_format($totalCommissionLimit, 2)
            ], 200);

        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while fetching the commission list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while fetching the commission list",
            ], 500);
        }
    }

    public function get_drivers(Request $request)
    {
        try {
            $html = [];
            $search = $request->input('search');

            $result = Driver::where(function ($query) use ($search) {
                    $query->where('driver_first_name', 'like', '%' . $search . '%')
                          ->orWhere('driver_last_name', 'like', '%' . $search . '%')
                          ->orWhereRaw("CONCAT(driver_first_name, ' ', driver_last_name) LIKE ?", ["%{$search}%"])
                          ->orWhere('driver_phone', 'like', '%' . $search . '%');
                })
                ->where("is_active", 1)
                ->where("is_delete", 0)
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
            $limit = $r->length ?? 0;
            $offset = $r->start ?? 0;
            $search = $r->input('search.value') ?? "";
            $driver = $r->driver ?? "";
            $from_date = $r->from_date ?? "";
            $to_date = $r->to_date ?? "";

            $filteredData = AdminCommission::filterCommissions($search, $limit, $offset, $driver, $from_date, $to_date);
            $records = $filteredData['result'];

            if ($records->isEmpty()) {
                return response()->json(["message" => "No data available for download."], 204);
            }

            $csvData = [];
            foreach ($records as $row) {
                $createdDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');

                $csvData[] = [
                    'Trip ID' => $row->trip ? $row->trip->trip_unique_id : '-',
                    'Driver Name' => ($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : ''),
                    'Type' => $row->type ?? '-',
                    'Commission Percentage' => $row->commission_percentage ?? '0',
                    'Commission Amount' => $row->commission_amount ?? '0',
                    'Order Amount' => $row->grand_total ?? '0',
                    'Date' => $createdDate,
                ];
            }

            $csvFileName = 'Commission_Report_' . date('d-m-Y') . '.csv';
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
            LogHelper::logError('An error occurred while downloading the commission report', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                "message" => "An error occurred while generating the CSV file.",
            ], 500);
        }
    }

    public function pdf_download(Request $r)
    {
        try {
            $limit = $r->length ?? 0;
            $offset = $r->start ?? 0;
            $search = $r->input('search.value') ?? "";
            $driver = $r->driver ?? "";
            $from_date = $r->from_date ?? "";
            $to_date = $r->to_date ?? "";

            $filteredData = AdminCommission::filterCommissions($search, $limit, $offset, $driver, $from_date, $to_date);
            $records = $filteredData['result'];

            if ($records->isEmpty()) {
                return response()->json(["message" => "No data available for download."], 204);
            }

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
                        </style>';
                        
            $htmlContent .= '<h3>Commission Report</h3>';
            $htmlContent .= '<table>';
            $htmlContent .= '<thead>
                                <tr>
                                    <th>Trip ID</th>
                                    <th>Driver Name</th>
                                    <th>Type</th>
                                    <th>Commission %</th>
                                    <th>Commission Amount</th>
                                    <th>Order Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>';
            $htmlContent .= '<tbody>';

            foreach ($records as $row) {
                $createdDate = Carbon::parse($row->created_at)->format('d-m-Y h:i A');

                $htmlContent .= '<tr>';
                $htmlContent .= '<td>' . ($row->trip ? $row->trip->trip_unique_id : '-') . '</td>';
                $htmlContent .= '<td>' . (($row->driver ? $row->driver->driver_first_name : '') . ' ' . ($row->driver ? $row->driver->driver_last_name : '')) . '</td>';
                $htmlContent .= '<td>' . ($row->type ?? '-') . '</td>';
                $htmlContent .= '<td>' . ($row->commission_percentage ?? '0') . '</td>';
                $htmlContent .= '<td>' . ($row->commission_amount ?? '0') . '</td>';
                $htmlContent .= '<td>' . ($row->grand_total ?? '0') . '</td>';
                $htmlContent .= '<td>' . $createdDate . '</td>';
                $htmlContent .= '</tr>';
            }

            $htmlContent .= '</tbody></table>';

            $pdf = PDF::loadHTML($htmlContent);
            return $pdf->download('Commission_Report.pdf');

        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while downloading the commission report', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(["message" => "An error occurred while generating the PDF file."], 500);
        }
    }
}
