<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
// 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\GoodsType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class GoodsTypeController extends Controller
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

    /**
     * Display a index page of the resource.
     * @author shreyasm@neosao
     */
    public function index()
    {
        try {
            return view('goods-type.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the goods type index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the goods type list.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Display a listing of the resource.
     */

	 public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$srno = $offset + 1;
			$data = array();
			$filteredData = GoodsType::filterGoodsType($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('Goods-Type.Edit');
			$canView = Auth::guard('admin')->user()->can('Goods-Type.View');
			$canDelete = Auth::guard('admin')->user()->can('Goods-Type.Delete');
			$showActions = $canEdit || $canView || $canDelete;

			if ($result && $result->count() > 0) {
				foreach ($result as $row) {
					$carbonDate = Carbon::parse($row->created_at);
					$formattedDate = $carbonDate->format('d-m-Y h:i:s A');
					
					$dataRow = [];
					
					// Add action column only if user has any permissions
					if ($showActions) {
						$action = '<span class="text-start">
							<div class="dropdown font-sans-serif position-static">
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit text-warning" href="' . url('goods-type/' . $row['id'] . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit')  . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('goods-type/' . $row['id']) . '"> <i class="far fa-folder-open"></i> ' . __('index.view')  . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete text-danger" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete')  . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->goods_name;
					$dataRow[] = $formattedDate;
					$dataRow[] = $row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
					
					$data[] = $dataRow;
					$srno++;
				}
			}
			
			return response()->json([
				"draw" => intval($request->draw),
				"recordsTotal" => $total,
				"recordsFiltered" => $total,
				"data" => $data,
				"result" => $result
			], 200);
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while the goods type list', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the goods type list",
			], 500);
		}
	}

    /** 
     * @author shreyasm@neosao
     */
    public function importexcel()
    {
        try {
            return view('goods-type.import');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the goods type import page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the goods type import.');
        }
    }

    /** 
     * @author shreyasm@neosao
     */
    public function validateexcel(Request $request)
    {
        $rowArray = $request->convertedIntoArray;
        $goodTypeNameError = '';
        $duplicateGoodType = '';
        $statusError = '';
        $returnRowArray = [];
        $goodTypeCount = [];
        $statusArrData = ['active', 'inactive'];

        if (!empty($rowArray)) {
            for ($i = 0; $i < count($rowArray); $i++) {
                $goodsTypeName = trim($rowArray[$i][1]);
                $status = trim($rowArray[$i][2]); // Check the right index for asset status

                // Goods type name validation
                if (empty($goodsTypeName)) {
                    $goodTypeNameError .= "Row " . ($i + 1) . " - Goods type name is required.<br>";
                    $returnRowArray[] = $i; // Add to return rows
                } else {
                    $goodsTypeExists = GoodsType::where('goods_name', $goodsTypeName)
                        ->where("is_delete", 0)
                        ->exists();

                    if ($goodsTypeExists) {
                        $duplicateGoodType .= "Row " . ($i + 1) . " - Goods type name '$goodsTypeName' already exists in the database.<br>";
                        $returnRowArray[] = $i;
                    } else {
                        // Detect duplicates in the current upload
                        if (!isset($goodTypeCount[$goodsTypeName])) {
                            $goodTypeCount[$goodsTypeName] = [$i + 1]; // Store row numbers
                        } else {
                            $goodTypeCount[$goodsTypeName][] = $i + 1; // Add row number
                        }
                    }
                }

                // Status validation
                if (empty($status)) {
                    $statusError .= "Row " . ($i + 1) . " - Status is required.<br>";
                    $returnRowArray[] = $i; // Add to return rows
                } elseif (!in_array($status, $statusArrData)) {
                    $statusError .= "Row " . ($i + 1) . " - Status is not valid. Please select from active/inactive instead  " . $status . "<br>";
                    $returnRowArray[] = $i; // Add to return rows
                }
            }
        }

        // Prepare duplicate goods type error message
        foreach ($goodTypeCount as $goodsTypeName => $rows) {
            if (count($rows) > 1) {
                $duplicateGoodType .= "Goods type name '$goodsTypeName' is duplicated in rows: " . implode(', ', $rows) . ".<br>";
            }
        }

        // Prepare the response message with colors
        $msg = '';
        if (!empty($duplicateGoodType)) {
            $msg .= '<li><p class="mb-1"><span class="text-warning">' . rtrim($duplicateGoodType, '<br>') . '</span></p></li>';
        }
        if ($goodTypeNameError != '') {
            $msg .= '<li><p class="mb-1"><span class="text-warning">' . rtrim($goodTypeNameError, '<br>') . '</span></p></li>';
        }
        if ($statusError != '') {
            $msg .= '<li><p class="mb-1"><span class="text-warning">' . rtrim($statusError, '<br>') . '</span></p></li>';
        }
        if ($msg != '') {
            $msg = '<ul>' . $msg . '</ul>';
        }

        $arr = [
            "msg" => $msg,
            "rowArr" => json_encode($returnRowArray)
        ];
        return response()->json($arr);
    }

    /** 
     * @author shreyasm@neosao
     */
    public function uploadexcel(Request $r)
    {
        $path = 'excel/';
        if (file_exists($path . $r->file('uploadFile'))) {
            unlink($path . $r->file('uploadFile'));
        }
        $fileItem =  $r->file('uploadFile');
        $rowExcepts = $r->rowExcepts;
        $filenameItemImage = $fileItem->getClientOriginalName();
        $fileItem->move($path,  $filenameItemImage);
        $inputFileName = $path . $filenameItemImage;
        $itemArr = array();
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($inputFileName);
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $allData = $objPHPExcel->getActiveSheet();
            $total_line = 0;
            $i = 0;
            $cntr = 0;
            unset($allDataInSheet[1]);
            $rowExcepts = json_decode($rowExcepts, true);
            if (!empty($rowExcepts)) {
                if (count($rowExcepts) > 0) {
                    $total_line = $total_line + count($rowExcepts);
                    for ($j = 0; $j < count($rowExcepts); $j++) {
                        unset($allDataInSheet[$rowExcepts[$j] + 2]);
                    }
                }
            }
            foreach ($allDataInSheet as $value) {
                if (count(array_filter($value)) == 0) {
                } else {
                    $total_line++;
                    $inserdata[$i]['goods_name'] = $value['A'];
                    $inserdata[$i]['is_active'] = $value['B'] === 'active' ? 1 : 0;
                    $result = GoodsType::create($inserdata[$i]);

                    if ($result == false) {
                        $result = 0;
                    } else {
                        $cntr++;
                    }
                    array_push($itemArr, array("id" => $result));
                    $i++;
                }
            }
            if ($total_line == $cntr) {
                $response['status'] = true;
                if ($total_line == 1) {
                    $response['text'] = 'Total : ' . $total_line . ' record is saved';
                } else {
                    $response['text'] = 'Total : ' . $total_line . ' records. All records are saved';
                }
            } else {
                $response['status'] = false;
                $response['text'] = 'Total Records: ' . $total_line . ' Successful: ' . $cntr . ' Unsuccessful: ' . ($total_line - $cntr);
            }

            echo json_encode($response);
        } catch (Exception $e) {
            $response['status'] = false;
            $response['text'] = 'Something went wrong';
        }
    }

    /**
     * @author seemashelar@neosao
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('goods-type.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while create the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the goods.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'goods_name' => [
                    'required',
                    'regex:/^[A-Za-z0-9\s\/\\|&\-_\,]+$/',
                    'min:2',
                    'max:150',
                    Rule::unique('goods_types', 'goods_name')->where(function ($query) {
                        return $query->where('is_delete', 0);
                    }),
                ],
                'is_active' => 'nullable'
            ], [
                'goods_name.required' => 'The goods type name field is required.',
                'goods_name.min' => 'The goods type name must be at least 2 characters.',
                'goods_name.max' => 'The goods type name may not be greater than 150 characters.',
                'goods_name.regex' => 'The goods type name may only contain letters, numbers, spaces, and the following characters: / \\ | & - _ ,',
                'goods_name.unique' => 'The goods type name must be unique.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the goods type
            $goods_type = new GoodsType;
            $goods_type->goods_name = $request->goods_name;
            $goods_type->is_active = $request->is_active ? 1 : 0;
            $goods_type->save();
            //success log
            LogHelper::logSuccess('The goods type added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $goods_type->id);
            // Return success response
            return redirect('goods-type')->with('success', 'Goods type added successfully.');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while saving the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while saving the goods type.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $goods_type = GoodsType::where('id', $id)->where('is_delete', 0)->first();
            if (!$goods_type) {

                // Log the error
                LogHelper::logError('An error occurred while view the goods type', 'The invalid goods type',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid goods type.');
            }
            return view('goods-type.show', compact('goods_type'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the goods type.');
        }
    }


    /**
     * @author seemashelar@neosao
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $goods_type = GoodsType::where('id', $id)->where('is_delete', 0)->first();
            if (!$goods_type) {
                // Log the error
                LogHelper::logError('An error occurred while edit the goods type', 'The invalid goods type',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid goods type.');
            }
            return view('goods-type.edit', compact('goods_type'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the goods type.');
        }
    }

    /**
     * @author seemashelar@neosao
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'goods_name' => [
                    'required',
                    'regex:/^[A-Za-z0-9\s\/\\|&\-_\,]+$/',
                    'min:2',
                    'max:150',
                    Rule::unique('goods_types', 'goods_name')
                        ->ignore($id)
                        ->where(function ($query) {
                            return $query->where('is_delete', 0);
                        }),
                ],
                'is_active' => 'nullable'
            ], [
                'goods_name.required' => 'The goods type name field is required.',
                'goods_name.min' => 'The goods type name must be at least 2 characters.',
                'goods_name.max' => 'The goods type name may not be greater than 150 characters.',
                'goods_name.regex' => 'The goods type name may only contain letters, numbers, spaces, and the following characters: / \\ | & - _ ,',
                'goods_name.unique' => 'The goods type name must be unique.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the goods types
            $goods_type = GoodsType::find($id);
            if (!$goods_type) {
                // Log the error
                LogHelper::logError('An error occurred while update the goods type', 'The invalid goods type',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid goods type.');
            }
            $goods_type->goods_name = $request->goods_name;
            $goods_type->is_active = $request->is_active ? 1 : 0;
            $goods_type->update();

            LogHelper::logSuccess('The goods type updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $goods_type->id);
            // Return success response
            return redirect('goods-type')->with('success', 'Goods type updated successfully.');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while updating the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the goods type.');
        }
    }


    /**
     * Remove the specified resource from storage.
     * @author seemashelar@neosao
     */
    public function destroy(string $id)
    {
        try {
            // Find the goods type by ID
            $goods_type = GoodsType::find($id);

            // Check if the goods type exists
            if (! $goods_type) {
                LogHelper::logError('An error occurred while deleting the goods type', 'Goods Type not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'Goods Type not found.']);
            }

            // Soft delete the good type by setting is_delete flag
            $goods_type->is_delete = 1;
            $goods_type->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the goods type', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the goods type .']);
        }
    }
}
