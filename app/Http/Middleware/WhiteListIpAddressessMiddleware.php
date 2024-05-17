<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ReportConfig;

class WhiteListIpAddressessMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $config = ReportConfig::query()->first();
        $whitelistIps = [];
        if($config && !empty($config->white_list_ips)) {
            $white_list_ips_str = preg_replace('/\s+/','', $config->white_list_ips);
            $whitelistIps = explode(',', trim($white_list_ips_str));
        }
        
        if (!empty($whitelistIps) && !in_array($request->getClientIp(), $whitelistIps)) {
            $code = 403;
            $response = [
                'code' => $code,
                'message' => __('messages.permission_access_denied')
            ];
            
            return response()->json($response, $code);
        }

        return $next($request);
    }
}
