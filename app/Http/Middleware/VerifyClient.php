<?php

namespace App\Http\Middleware;

use Closure;

class VerifyClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $clientId = $request->header('CLIENT_ID');
        if (!$clientId || $clientId !== config('client.CLIENT_ID')) {
            return response()->json(null, __('errors.ACCESS_DENIED'));
        }

        $clientSecret = $request->header('CLIENT_SECRET');
        if (!$clientSecret || $clientSecret != config('client.CLIENT_SECRET')) {
            return response()->json(null, __('errors.ACCESS_DENIED'));
        }

        return $next($request);
    }
}
