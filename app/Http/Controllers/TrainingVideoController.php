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
use App\Models\TrainingVideo;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class TrainingVideoController extends Controller
{
    /**`
     * Display a index page of the resource.
     * @author seemashelar@neosao
     */
    public function index()
    {
        try {
            return view('training-video.index');
        } catch (\Exception $ex) {
            LogHelper::logError('An error occurred while the training video index page', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the training video list.');
        }
    }
	
	
	 /**
     * Display a listing of the resource.
	 * @author seemashelar@neosao
     */
   public function list(Request $request)
	{
		try {
			$search = $request->input('search.value') ?? "";
			$limit = $request->length;
			$offset = $request->start;
			$data = array();
			
			$filteredData = TrainingVideo::filterTrainingVideo($search, $limit, $offset);
			$total = $filteredData['totalRecords'];
			$result = $filteredData['result'];
			
			// Check permissions once for all rows
			$canEdit = Auth::guard('admin')->user()->can('Training-Video.Edit');
			$canView = Auth::guard('admin')->user()->can('Training-Video.View');
			$canDelete = Auth::guard('admin')->user()->can('Training-Video.Delete');
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
								<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="trainingvideo-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
									aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
								</button>
								<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="trainingvideo-dropdown-' . $row->id . '">
									<div class="bg-white py-2">';
						
						if ($canEdit) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('training-video/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> ' . __('index.edit') . ' </a>';
						}
						if ($canView) {
							$action .= '<a class="dropdown-item btn-edit" href="' . url('training-video/' . $row->id) . '"> <i class="far fa-folder-open"></i> ' . __('index.view') . '</a>';
						}
						if ($canDelete) {
							$action .= '<a class="dropdown-item btn-delete" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> ' . __('index.delete') . '</a>';
						}
						
						$action .= '</div></div></div></span>';
						$dataRow[] = $action;
					}

					// Add remaining columns
					$dataRow[] = $row->video_title;
					$dataRow[] = $row->video_path 
						? '<a href="' . asset('storage/videos/' . $row->video_path) . '" target="_blank" class="btn btn-sm btn-primary">View Video</a>'
						: '';
					$dataRow[] = $formattedDate;
					$dataRow[] = $row->is_active == 1 
						? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
						: '<div><span class="badge rounded-pill badge-soft-danger">Inactive</span></div>';
					
					$data[] = $dataRow;
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
			LogHelper::logError('An error occurred while the training video list', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return response()->json([
				"message" => "An error occurred while fetching the training video list",
			], 500);
		}
	}
	/**
     * @author seemashelar@neosao
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('training-video.add');
        } catch (\Exception $ex) {
             // Log the error
            LogHelper::logError('An error occurred while create the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while create the training video.');
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
				'video_title' => [
					'required',
					Rule::unique('training_videos')->where(function ($query) {
						return $query->where('is_delete', '=', '0');
					})
				],
				'video' => [
					'required',
					'mimes:mp4,avi,mov,wmv,flv,mkv,webm'
				],
				'thumbnail' => [
					'required',
					'image',
					'mimes:jpg,png,jpeg'
				],
				'is_active' => 'nullable'
			], [
				'video_title.required' => 'The video title field is required.',
				'video_title.unique' => 'The video title has already been taken.',
				'video.required' => 'The video is required',
				'video.mimes' => 'The video must be a file of type: mp4, avi, mov, wmv, flv, mkv, or webm.',
				'thumbnail.required' => 'The thumbnail image is required.',
				'thumbnail.image' => 'The thumbnail must be an image.',
				'thumbnail.mimes' => 'The thumbnail must be a file of type: jpg, png, or jpeg.',
			
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			// Handle the video upload
			$videoName = null;
			if ($request->hasFile('video')) {
				$file = $request->file('video');
				$videoName = 'video_' . time() . '.' . $file->getClientOriginalExtension();
				Storage::disk('public')->putFileAs('videos', $file, $videoName);
			}

			// Handle the thumbnail upload
			$thumbnailName = null;
			if ($request->hasFile('thumbnail')) {
				$thumbnail = $request->file('thumbnail');
				$thumbnailName = 'thumbnail_' . time() . '.' . $thumbnail->getClientOriginalExtension();
				Storage::disk('public')->putFileAs('thumbnails', $thumbnail, $thumbnailName);
			}

			// Create and save the training video
			$training_video = new TrainingVideo;
			$training_video->video_title = $request->video_title;
			$training_video->total_video_time_length = $request->total_video_time_length;
			$training_video->video_path = $videoName;
			$training_video->thumbnail = $thumbnailName;
			$training_video->is_active = $request->is_active ? 1 : 0;
			$training_video->is_delete = 0;
			$training_video->save();

			LogHelper::logSuccess('The training video added successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $training_video->id);
			return redirect('training-video')->with('success', 'Training video added successfully.');
			
		} catch (\Exception $ex) {
			LogHelper::logError('An error occurred while saving the training video', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			return redirect()->back()->with('error', 'An error occurred while saving the training video.');
		}
	}
	/**
     * @author seemashelar@neosao
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
            if (!$training_video) {                
				// Log the error
                LogHelper::logError('An error occurred while view the training video', 'The invalid training video',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid training video.');
            }
            return view('training-video.show', compact('training_video'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while view the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the training video.');
        }
    }
	
	
	/**
     * Remove the specified resource from storage.
     * @author seemashelar@neosao
     */
    public function destroy(string $id)
    {
        try {
            // Find the training video by ID
              $training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
            // Check if the training video exists
            if (! $training_video) {
                LogHelper::logError('An error occurred while deleting the training video', 'Training video not found',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json(['success' => false, 'error' => 'Training video not found.']);
            }

            // Soft delete the training video by setting is_delete flag
            $training_video->is_delete = 1;
            $training_video->save();

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log the error and return error response
            LogHelper::logError('An error occurred while deleting the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(['success' => false, 'error' => 'An error occurred while deleting the training video .']);
        }
    }
	
	/**
     * @author seemashelar@neosao
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
            if (!$training_video) {                
				// Log the error
                LogHelper::logError('An error occurred while edit the training video', 'The invalid training video',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid training video.');
            }
            return view('training-video.edit', compact('training_video'));
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError('An error occurred while edit the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while edit the training video.');
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
                'video_title' => [
                    'required',
					Rule::unique('training_videos')->where(function ($query) use ($id) {
						return $query->where('is_delete', '=', '0')
							->where('id', '!=', $id)
							->where('is_active', '=', 1);
					}),
                ],
				'video' => [
                    'nullable',
					'mimes:mp4,avi,mov,wmv,flv,mkv,webm'

                ],
				'thumbnail' => [ // Added thumbnail validation
					'nullable',
					'image',
					'mimes:jpg,png,jpeg'
				],
                'is_active' => 'nullable'
            ], [
                'video_title.required' => 'The video title field is required.',
				'video_title.unique' => 'The video title has already been taken.',
				'video.required'=>'The video is required',
				'video.mimes' => 'The video must be a file of type: mp4, avi, mov, wmv, flv, mkv, or webm.',
				'thumbnail.image' => 'The thumbnail must be an image.', // Added error messages
                'thumbnail.mimes' => 'The thumbnail must be a file of type: jpg, png, or jpeg.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Create and save the training video
           $training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
            if (!$training_video) {
               // Log the error
                LogHelper::logError('An error occurred while update the training video', 'The invalid training video',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid training video.');
            }
            $training_video->video_title = $request->video_title;
			$training_video->total_video_time_length = $request->total_video_time_length;
            $training_video->is_active = $request->is_active ? 1 : 0;
			$training_video->is_delete=0;
			if ($request->hasFile('video')) {
                $file = $request->file('video');
                $videoName = 'video' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('videos', $file, $videoName);
                $training_video->video_path= $videoName; // Save the video name in the database
            }
			   // Handle thumbnail upload if provided
			if ($request->hasFile('thumbnail')) {
				// Delete old thumbnail if exists
				if ($training_video->thumbnail) {
					Storage::disk('public')->delete('thumbnails/' . $training_video->thumbnail);
				}
				
				$thumbnail = $request->file('thumbnail');
				$thumbnailName = 'thumbnail_' . time() . '.' . $thumbnail->getClientOriginalExtension();
				Storage::disk('public')->putFileAs('thumbnails', $thumbnail, $thumbnailName);
				$training_video->thumbnail = $thumbnailName;
			}
            $training_video->update();

            LogHelper::logSuccess('The training video updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $training_video->id);
            // Return success response
            return redirect('training-video')->with('success', 'Training video updated successfully.');
			
        } catch (\Exception $ex) {
           // Log the error
            LogHelper::logError('An error occurred while updating the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while updating the training video.');
        }
    }
	
	 /**
     * @author seemashelar@neosao
     * delete training video.
     */
	public function delete_video(Request $r,String $id)
    {
        try{
			$training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
			if (!$training_video ) {                
				 // Log the error
				 LogHelper::logError('An error occurred while delete the training video', 'The invalid training video',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
				 // Return error response to the user
					return redirect()->back()->with('error', 'The invalid training video.');
            }
			
			
			Storage::disk('public')->delete($training_video->video_path);
			$training_video->update(['video_path' => null]);
            //success log			
			LogHelper::logSuccess('The video deleted successfully',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, $id);
			//return response
			return redirect('training-video/'.$r->id.'/edit')->with('success', 'The video deleted successfully');
		}catch (\Exception $ex) {
            // Log the error
             LogHelper::logError('An error occurred while deleting the training video', $ex->getMessage(),  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while training video');
        }
    }
	

	
	/**
	 * @author seemashelar@neosao
	 * Delete training video thumbnail.
	 */
	public function delete_thumbnail(Request $r, String $id)
	{
		try {
			$training_video = TrainingVideo::where('id', $id)->where('is_delete', 0)->first();
			
			if (!$training_video) {                
				// Log the error
				LogHelper::logError(
					'An error occurred while deleting the thumbnail', 
					'Invalid training video',  
					__FUNCTION__, 
					basename(__FILE__), 
					__LINE__, 
					__FILE__, 
					$id
				);
				// Return error response
				return redirect()->back()->with('error', 'Invalid training video.');
			}

			// Check if thumbnail exists before trying to delete
			if ($training_video->thumbnail) {
				Storage::disk('public')->delete('thumbnails/' . $training_video->thumbnail);
				$training_video->update(['thumbnail' => null]);
			}

			// Success log            
			LogHelper::logSuccess(
				'Thumbnail deleted successfully',  
				__FUNCTION__, 
				basename(__FILE__), 
				__LINE__, 
				__FILE__, 
				$id
			);
			
			// Return response
			return redirect('training-video/'.$r->id.'/edit')->with('success', 'Thumbnail deleted successfully');
			
		} catch (\Exception $ex) {
			// Log the error
			LogHelper::logError(
				'An error occurred while deleting thumbnail', 
				$ex->getMessage(),  
				__FUNCTION__, 
				basename(__FILE__), 
				__LINE__, 
				__FILE__, 
				''
			);
			// Return error response
			return redirect()->back()->with('error', 'An error occurred while deleting thumbnail');
		}
	}
	
}
