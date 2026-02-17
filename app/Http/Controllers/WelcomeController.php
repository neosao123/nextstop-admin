<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WelcomeController extends Controller
{
    public function welcome()
    { 
        return view('index');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function terms_conditions()
    {
        return view('terms-conditinos');
    }

    public function privacy_policy()
    {
        return view('privacy-policy');
    }

    public function refund_policy()
    {
        return view('refund-policy');
    }

    public function faq()
    {
        return view('faq');
    }
	
	public function delete_user_process(Request $r){
		 // Render the Blade view as HTML
        $html = view('sample')->render(); 

        // Return the HTML content with proper header
        return response($html)->header('Content-Type', 'text/html');
	}
}
