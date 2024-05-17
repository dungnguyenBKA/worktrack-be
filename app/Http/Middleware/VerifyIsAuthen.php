<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\UserApp;

class VerifyIsAuthen
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
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'code' => __('errors.E0101.code'),
                    'message' => __('errors.E0101.message')
                ], __('errors.E0101.statusCode'));
            }
        } catch (JWTException $e) {
            // Token expired
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'code' => __('errors.E0101.code'),
                    'message' => __('errors.E0101.message'),
                    'data' => [
                        'access_token_expired' => true
                    ]
                ], __('errors.E0101.statusCode'));
            }

            return response()->json([
                'code' => __('errors.E0101.code'),
                'message' => __('errors.E0101.message'),
            ], __('errors.E0101.statusCode'));
        }

        return $next($request);

    }
}
