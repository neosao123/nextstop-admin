<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Contactus;
use App\Http\Resources\BlogResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\Enquiry;
class ContactController extends Controller
{
	
	 //contact send
    public function index(Request $request)
	{
	  try {
			$input = $request->all();

			$validator = Validator::make($input, [
				'name' => 'required|string|max:255',
				'email' => 'required|email|max:255',
				'contactno' => 'required|string|max:15',
				'subject' => 'required|string|max:255',
				'message' => 'nullable|string'
			]);

			if ($validator->fails()) {
				return response()->json([
				    "status"=>false,
					"message" => $validator->errors()->first(),
				], 300);
			}
			
			
			$enquiry = new Enquiry; 
			$enquiry->name = $request->name;
			$enquiry->email=$request->email;
			$enquiry->subject=$request->subject;
			$enquiry->contactno=$request->contactno;
			$enquiry->message=$request->message;
			$enquiry->save(); 
			
			
			$contactus = [
			       'name' => $request->name,
			       'email' => $request->email, 
                   'contactno' => $request->contactno,  				   
			       'subject' => $request->subject,
			       'message' => $request->message
			 ];
			 
			    // Email to the user
			Mail::send([], [], function ($message) use ($contactus) {
				$message->to($contactus["email"])
						->subject('Thank You for Contacting Us')
						->html(
							"<p>Dear <b>{$contactus["name"]}<b>,</p>
							 <p>Thank you for reaching out. We have received your message and will get back to you shortly:</p>
							 <p><strong>Contact Number:</strong> {$contactus["contactno"]}</p>
							 <p><strong>Subject:</strong> {$contactus["subject"]}</p>
							 <p><strong>Message:</strong><br>{$contactus["message"]}</p>
							"
						);
			});

            
			// Email to the admin
			$adminEmail = 'nextstopkolhapur@gmail.com';
			Mail::send([], [], function ($message) use ($contactus, $adminEmail) {
				$message->to($adminEmail)
						->subject('New Contact Form')
						->html(
							"<p>A new contact form enquiry has been received:</p>
							 <p><strong>Name:</strong> {$contactus["name"]}</p>
							 <p><strong>Contact Number:</strong> {$contactus["contactno"]}</p>
							 <p><strong>Subject:</strong> {$contactus["subject"]}</p>
							 <p><strong>Message:</strong><br>{$contactus["message"]}</p>"
						);
			});
			
			return response()->json(["status"=>true,'message' => 'Thank you for contacting us!'], 200);
			
       } catch (\Exception $e) {
			Log::error('An error occurred while fetching the contact send.', [
				'error' => $e->getMessage(),
				'request' => $request->all(),
			]);
			return response()->json(["status"=>false,'message' => 'Something went wrong'], 500);
	   }
	}

	
}