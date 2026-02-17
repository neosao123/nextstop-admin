<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\FCMNotify;
use App\Models\Notification;

class CronController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('id','ASC')->count();
	    if($notifications>0) {
	        $fcm = new FCMNotify();
	        $fcm->send();
	    }
    }
}
