<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Facades\Log;
// Helper
use App\Helpers\LogHelper;

class AuthController extends Controller
{

    /*
	 * Login Index Page
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	*/
	
	public function index()
    {
        try {
            if (Auth::guard('admin')->check()) {
                if (Auth::guard('admin')->user()->id != "") {
                    return redirect('/dashboard');
                }
            }
            return view('auth.login');
        } catch (\Exception $ex) {
			//error response
			LogHelper::logError('An error occurred while the login', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while the login.');
            //return view('auth.login');
        }
    }

     /*
	 * Login operation
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */
	
	
	public function login(Request $r)
    {
        
	   try {
		if ($r->isMethod('post')) {
            $r->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            $email = $r->input('email');
            $password = $r->input('password');
            $remember = $r->input('rememberme') == true ? '1' : '0';
            $result = User::where(['email' => $email])
			         ->where("is_delete",0)
					 ->first();
            if (empty($result)) {
                $r->session()->flash('fail', 'Please Enter Valid Email & Password');
                return redirect('login');
            } else {
                if($result->is_block==1){
					$r->session()->flash('fail', 'Your account is blocked by admin, please contact the administrator to unblock it.');
                    return redirect('login');
				}
				if($result->is_active==0){
					$r->session()->flash('fail', 'Your account is inactive, please contact the administrator to activate it.');
                    return redirect('login');
				}
				if (Auth::guard('admin')->attempt(['email' => $email, 'password' => $password])) {
                    Auth::login($result);
                    $email = Auth::guard('admin')->user()->email;
                    $r->session()->put('SUPERUSER_LOGIN', true);
                    if ($remember == '1') {
                        Cookie::queue('email', $email, time() + (10 * 365 * 24 * 60 * 60));
                        Cookie::queue('password', $password, time() + (10 * 365 * 24 * 60 * 60));
                    } else {
                        if (Cookie::get('email')) Cookie::queue('email', '');
                        if (Cookie::get('password')) Cookie::queue('password', '');
                    }
                    //return redirect('/dashboard');
                }
				$role = Auth::guard( 'admin' )->user()->role_id;
				
				if($role==1){
					 return redirect( 'dashboard' );
				}else{
					  if(Auth::guard('admin')->user()->can('Dashboard.View')){
						   return redirect('dashboard');
					  }else{
							return redirect('welcome');
					  }
				}
                $r->session()->flash('error', 'Invalid Email or password.');
                return redirect('login');
            }
         }
	   }catch (\Exception $ex) {
	        //error log
			LogHelper::logError('An error occurred while admin login', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
       }
    }
	
	/*
	 * Loggout session
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */

    public function logout(Request $r)
    {
		try{
			Auth::logout();
			Auth::guard("admin")->logout(); 
			session()->forget('admin_LOGIN');
			$r->session()->flash('success', 'Successfully Logout');
			return redirect('login');
		}catch (\Exception $ex) {
			//error response
			LogHelper::logError('An error occurred while the admin logged out', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
        }
    }
	
	/*
	 * Reset password index page
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */
	
	public function reset(Request $r){
		return view('auth.reset');
	}
	
	
	 /*
	 * Sending email with token for forgot password operations
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */

    public function reset_password(Request $r){
		try{
			$email = $r->input('email');        
			$result=User::where("email",$email)->first();
			if ($result) {
				$token = $this->random_characters(5);
				$token .= date('Hdm');
				$sendLink = url('verify-token/' . $token);
				$details = [
					'title' => 'Mail from Carrier',
					'link' => $sendLink,
				];
				Mail::to($email)->send(new \App\Mail\ForgotAdminEmail($details));
				$data = array('reset_token' => $token);
				$resultAfterMail=$result->update($data);    
				if ($resultAfterMail) {
					$r->session()->flash('success', 'Reset Link was sent to your email...');
					return redirect('/forgot-password');
				} else {
					$r->session()->flash('error', 'Some Error is Occur');
					return redirect('/forgot-password');
				}
				
			}else {
				$r->session()->flash('error', 'No users were found with the email address provided! Sorry cannot reset the password');
				return redirect('/forgot-password');
			}
		}catch (\Exception $ex) {
			//error response
			LogHelper::logError('An error occurred while the reset password', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
			
        }
	}
	
	/*
	 * Check whether verify link is valid or not
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */

    public function verify_token_link(Request $r)
    {
		try{
			$token = $r->token;
			$result=User::where("reset_token",$token)->first();
			if ($result) {
				return view('auth.verify', compact('result'));
			} else {
				$r->session()->flash('message', 'Password Reset Link is Expired. Please Forgot Password Again to Continue.');
				return redirect('/login');
			}
		}catch (\Exception $ex) {
			//error response
			LogHelper::logError('An error occurred while the verify token', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

        }
    }
	
	/*
	 * update password operations
	 * seemashelar@neosao
	 * dt: 19-oct-2024
	 */

    public function update_password(Request $r)
    {
	 try{
        $token = $r->input('token');
        $code = $r->input('code');
        $getResult=User::where("reset_token",$token)->first();
        if ($getResult) {
            $rules = [
                'password'  =>  'min:6|confirmed|required',
                'password_confirmation' => 'min:6|required',
            ];

            $messages = [
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password is not matched'
            ];
            $this->validate($r, $rules, $messages);

            $data = array(
                'password' => Hash::make($r->input('password')),
                'reset_token' => null,
            );
			$result=$getResult->update($data);            
            if ($result) {
                $r->session()->flash('message', 'Password Reset Successfully.. Please Login to Continue');
                return redirect('/login');
            } else {
                $r->session()->flash('message', 'Problem During Reset Password.. Please Try Again');
                return redirect('/login');
            }
        } else {
            $r->session()->flash('message', 'Reset Link is broken! Please try again...');
            return redirect('/login');
        }
	  }catch (\Exception $ex) {
		    //error response
			LogHelper::logError('An error occurred while the update password', $ex->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

       }
    } 	
	
	 public function random_characters($n){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
	}

}
