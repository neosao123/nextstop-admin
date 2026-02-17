<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class ProfileController extends Controller
{
    
    public $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();
            return $next($request);
        });
    }
    
	/*
	 * Profile Page
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/
	
	public function index()
    {
        try{
			if (!(Auth::user())) {
				return redirect('/');
			}
			$details = Auth::user();
			return view('profile.index', compact('details'));
		}catch(\Exception $ex){
			// Log the error
			 Log::error('An error occurred while the profile', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the profile fetch.');
		}
    }
	
	 /*
	 * Profile page update
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/

    public function update(Request $r)
    {
		$id=Auth::user()->id;
        $r->validate([
            'first_name' => [
                'required', 'regex:/^[A-Za-z\s]+$/',
                'min:2', 'max:150'
            ],
			'last_name' => [
                'required', 'regex:/^[A-Za-z\s]+$/',
                'min:2', 'max:150'
            ],
            'phone_number' => [
                'required', 'min_digits:10', 'max_digits:12', 'numeric',
                 Rule::unique('users')->where(function ($query) use($id){
                    return $query->where('is_delete', '=', '0')
						->where("id", '!=', $id)
						->where('is_active', '=', 1);
                })
            ],
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'min:2',
                'max:150',
				 Rule::unique('users')->where(function ($query) use ($id) {
					return $query->where('is_delete', '=', '0')
						->where("id", '!=', $id)
						->where('is_active', '=', 1);
				})
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'The first name must only contain alphabets and spaces.',
            'first_name.min' => 'The First name must be at least 2 characters long.',
            'first_name.max' => 'The First name cannot exceed 150 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'The last name must only contain alphabets and spaces.',
            'last_name.min' => 'The Last name must be at least 2 characters long.',
            'last_name.max' => 'The Last name cannot exceed 150 characters.',
            'phone_number.required' => 'The Phone number is required.',
            'phone_number.digits_between' => 'The Phone number must be between :min and :max digits.',
            'phone_number.numeric' => 'Please enter a valid number.',
            'phone_number.unique' => 'The phone number has already been taken.',
            'email.required' => 'The Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.min' => 'The Email must be at least 2 characters long.',
            'email.max' => 'The Email cannot exceed 150 characters.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
        ]);
        try{
			$data = array(
				'first_name' => $r->first_name,
				'last_name' => $r->last_name,
				'phone_number' => $r->phone_number,
				'email' => $r->email
			);

			// Check if a file was uploaded
			if ($r->hasFile('avatar')) {
				$file = $r->file('avatar');
				$path = Storage::disk('public')->putFileAs('avatar', $file, "avatar-" . time() . "." . $file->getClientOriginalExtension());
				$data['avatar'] = $path; 
			}

			$userid = Auth::user()->id;
			//profile update 
			
			$user = User::find($userid);
			$user->update($data);
			
			//success log
			Log::info('The profile updated successfully.', [
			    'id'=>$userid,
				'user_id' => auth()->id(),
				
				'function' => __FUNCTION__,
				'file' => basename(__FILE__),
				'line' => __LINE__,
				'path' => __FILE__,
			]);
			
			//return success response		
			return redirect('/profile')->with('success', 'Profile Updated Successfully.');
		}catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while update the profile', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while update the user.');
        }
	
	}
	
	
	/*
	 * Avatar delete
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/

    public function deleteAvatar()
    {
		try{
			$user = Auth::user();
			$path = $user['avatar'];
			Storage::disk('public')->delete($path);
			$getUser = User::find($user['id']);
			$getUser->update(['avatar' => null]);
			
			//success log
			Log::info('Avatar deleted successfully.', [
				'user_id' => auth()->id(),
				'id'=>$user['id'],
				'function' => __FUNCTION__,
				'file' => basename(__FILE__),
				'line' => __LINE__,
				'path' => __FILE__,
			]);
			
			//return success response
			return redirect('profile')->with('success', 'Avatar deleted successfully');
		}catch(\Exception $ex){
			// Log the error
			 Log::error('An error occurred while the avatar delete', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the avatar delete.');
		}
    }
	
	/*
	 * change password index
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/


    public function changePassword()
    {
		 try{
             return view('change-password.index');
		 }catch(\Exception $ex){
			 
			// Log the error
			 Log::error('An error occurred while the change password', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);
			
			// Return success response
			return redirect()->back()->with('error', 'An error occurred while the change password.');
		}
    }
	
	
	/*
	 * update password activity
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/

    public function updatePassword(Request $r)
    {
        $r->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|max:20|regex:/^\S+$/',
            'password_confirmation' => 'required|same:new_password|regex:/^\S+$/',
        ], [
            'old_password.required' => 'The old password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.min' => 'The new password must be at least :min characters.',
            'new_password.max' => 'The new password must not be more than :max characters.',
            'password_confirmation.required' => 'The password confirmation is required.',
            'password_confirmation.same' => 'The password confirmation must match the new password.',
            'new_password.regex' => 'Enter valid password',
            'password_confirmation.regex' => 'Enter valid confirm password',
		]);
		try{
			//get user
			$user = User::find(Auth::user()->id);
			if ($user) {
				if (Hash::check($r->old_password, $user->password)) {				 
				
					$user->update(['password' => Hash::make($r->new_password)]);
					
					
					//success log
					Log::info('The password changed successfully.', [
						'id'=>Auth::user()->id,
						'user_id' => auth()->id(),						
						'function' => __FUNCTION__,
						'file' => basename(__FILE__),
						'line' => __LINE__,
						'path' => __FILE__,
					]);
					
					return redirect('change-password')->with('success', 'The password updated successfully.');
				} else {
					// Log the error
					 Log::error('An error occurred while the password change', [
						'user_id' => auth()->id(),
						'function' => __FUNCTION__,
						'file' => basename(__FILE__),
						'line' => __LINE__,
						'path' => __FILE__,
						'exception' => $ex->getMessage(),
					]);
				
					//return error response
					return back()->with('error', 'The old password is not valid.');
				}
			} else {
				
				// Log the error
				 Log::error('An error occurred while the password change', [
					'user_id' => auth()->id(),
					'function' => __FUNCTION__,
					'file' => basename(__FILE__),
					'line' => __LINE__,
					'path' => __FILE__,
					'exception' => $ex->getMessage(),
				]);
				
				//return error response
				return back()->with('error', 'User does not exists.');
			}
		}catch (\Exception $ex) {
            // Log the error
            Log::error('An error occurred while password change', [
                'user_id' => auth()->id(),
                'function' => __FUNCTION__,
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'path' => __FILE__,
                'exception' => $ex->getMessage(),
            ]);

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while password change.');
        }
    }
}
