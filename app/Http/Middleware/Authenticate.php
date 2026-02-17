<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
	
	 protected function unauthenticated($request, array $guards)
    {
        // Check if the request expects a JSON response (API request)
        if ($request->expectsJson() || $request->is('api/*')) {
            throw new HttpResponseException(            
                response()->json(['status' => 400, 'message' => 'Unauthenticated or token expired.'], 400)
            );
        }

        // For web requests, redirect to the login page
        return redirect()->guest(route('login'));
    }
}
