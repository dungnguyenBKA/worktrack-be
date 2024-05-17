<?php

namespace App\Http\Middleware;

use Closure;

class VerifyNotAuthen
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
        $deviceToken = $request->header('DEVICE_TOKEN');
        if (!$deviceToken) {
            return response()->json(null, __('errors.ACCESS_DENIED'));
        }

        return $next($request);
    }
}
